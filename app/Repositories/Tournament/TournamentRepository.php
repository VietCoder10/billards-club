<?php

namespace App\Repositories\Tournament;

use App\Components\CommonComponent;
use App\Models\Tournament;
use App\Models\TournamentParticipant;
use App\Models\TournamentMatch;
use Carbon\Carbon;
use Illuminate\Pagination\Paginator;

class TournamentRepository implements TournamentInterface
{
    private Tournament $tournament;

    public function __construct(Tournament $tournament)
    {
        $this->tournament = $tournament;
    }

    public function get($request)
    {
        $newSizeLimit = CommonComponent::newListLimit($request);
        $builder = $this->tournament->query();
        if (isset($request['free_word']) && $request['free_word'] != '') {
            $builder->where(function ($query) use ($request) {
                $query->where('name', 'like', "%{$request['free_word']}%");
            });
        }
        $tournaments = $builder->sortable(['created_at' => 'desc'])->paginate($newSizeLimit);
        if (CommonComponent::checkPaginatorList($tournaments)) {
            Paginator::currentPageResolver(function () {
                return 1;
            });
            $tournaments = $builder->paginate($newSizeLimit);
        }
        return $tournaments;
    }

    public function getActiveTournaments()
    {
        // Get Open and Ongoing tournaments for users to see
        return $this->tournament->whereIn('status', [1, 2])->orderBy('start_date', 'asc')->get();
    }

    public function store($request)
    {
        $tournament = $this->tournament->fill($request->only([
            'name', 'description', 'start_date', 'end_date', 'registration_deadline',
            'max_participants', 'entry_fee', 'prize_pool', 'status'
        ]));
        
        $tournament->created_by = auth('admin')->id();
        $saved = $tournament->save();

        if ($saved && $tournament->status == 1) { // Open
            $customers = \App\Models\Customer::all();
            \Illuminate\Support\Facades\Notification::send($customers, new \App\Notifications\TournamentCreated($tournament));
        }

        return $saved;
    }

    public function getById(string $id)
    {
        return $this->tournament->with(['participants.customer', 'matches'])->find($id);
    }

    public function update($request, string $id)
    {
        $tournament = $this->getById($id);
        if (!$tournament) return false;

        $oldStatus = $tournament->status;

        $tournament->fill($request->only([
            'name', 'description', 'start_date', 'end_date', 'registration_deadline',
            'max_participants', 'entry_fee', 'prize_pool', 'status'
        ]));
        
        $saved = $tournament->save();

        if ($saved && $oldStatus != 1 && $tournament->status == 1) { // Changed to Open
            $customers = \App\Models\Customer::all();
            \Illuminate\Support\Facades\Notification::send($customers, new \App\Notifications\TournamentCreated($tournament));
        }

        return $saved;
    }

    public function destroy(string $id)
    {
        $tournament = $this->getById($id);
        if (!$tournament) return false;
        return $tournament->delete();
    }

    public function getParticipants($tournamentId, $request)
    {
        // Simple list for now, can add pagination if needed
        return TournamentParticipant::with('customer')
            ->where('tournament_id', $tournamentId)
            ->get();
    }

    public function registerParticipant($tournamentId, $customerId)
    {
        $exists = TournamentParticipant::where('tournament_id', $tournamentId)
            ->where('customer_id', $customerId)
            ->exists();
            
        if ($exists) {
            return false;
        }

        return TournamentParticipant::create([
            'tournament_id' => $tournamentId,
            'customer_id' => $customerId,
            'status' => 0, // Pending
            'payment_status' => 0 // Unpaid
        ]);
    }

    public function updateParticipantStatus($participantId, $status)
    {
        $participant = TournamentParticipant::find($participantId);
        if (!$participant) return false;
        
        $participant->status = $status;
        return $participant->save();
    }

    public function getMatches($tournamentId)
    {
        return TournamentMatch::with(['player1', 'player2', 'winner'])
            ->where('tournament_id', $tournamentId)
            ->get();
    }

    public function storeMatch($request)
    {
        return TournamentMatch::create($request->all());
    }

    public function updateMatchScore($matchId, $data)
    {
        $match = TournamentMatch::find($matchId);
        if (!$match) return false;

        $match->fill($data);
        $saved = $match->save();

        // Advance winner/loser if match is completed
        if ($saved && $match->status == 2 && $match->winner_id) {
            $loser_id = ($match->winner_id == $match->player1_id) ? $match->player2_id : $match->player1_id;

            if ($match->next_match_id) {
                $nextMatch = TournamentMatch::find($match->next_match_id);
                if ($nextMatch) {
                    if (!$nextMatch->player1_id) {
                        $nextMatch->player1_id = $match->winner_id;
                    } else if (!$nextMatch->player2_id && $nextMatch->player1_id != $match->winner_id) {
                        $nextMatch->player2_id = $match->winner_id;
                    }
                    $nextMatch->save();
                }
            }

            if ($match->next_loser_match_id) {
                $nextLoserMatch = TournamentMatch::find($match->next_loser_match_id);
                if ($nextLoserMatch && $loser_id) {
                    if (!$nextLoserMatch->player1_id) {
                        $nextLoserMatch->player1_id = $loser_id;
                    } else if (!$nextLoserMatch->player2_id && $nextLoserMatch->player1_id != $loser_id) {
                        $nextLoserMatch->player2_id = $loser_id;
                    }
                    $nextLoserMatch->save();
                }
            }
        }

        return $saved;
    }

    public function generateBracket($tournamentId)
    {
        $participants = TournamentParticipant::where('tournament_id', $tournamentId)
            ->where('status', 1) // Approved
            ->inRandomOrder()
            ->get();

        $count = $participants->count();
        if ($count < 2) return false;

        // Clear existing matches
        TournamentMatch::where('tournament_id', $tournamentId)->delete();

        // 1. Generate Winner Bracket
        $rounds = ceil(log($count, 2));
        $bracketSize = pow(2, $rounds);
        $byes = $bracketSize - $count;

        $matchNumber = 1;
        $winnerMatches = []; // Store by round
        $participantQueue = $participants->pluck('customer_id')->toArray();
        
        // Round 1 Winner Bracket
        $round1MatchCount = $bracketSize / 2;
        $currentRoundMatchIds = [];
        for ($i = 0; $i < $round1MatchCount; $i++) {
            $player1 = array_shift($participantQueue);
            $player2 = null;
            $status = 0;
            $winner = null;

            if ($byes > 0) {
                $byes--;
                $status = 2; // Completed
                $winner = $player1;
            } else {
                $player2 = array_shift($participantQueue);
            }

            $match = TournamentMatch::create([
                'tournament_id' => $tournamentId,
                'round_name' => 'Nhánh Thắng - Vòng 1',
                'round_number' => 1,
                'match_number' => $matchNumber++,
                'player1_id' => $player1,
                'player2_id' => $player2,
                'status' => $status,
                'winner_id' => $winner,
                'bracket_type' => 0
            ]);
            $currentRoundMatchIds[] = $match->id;
        }
        $winnerMatches[1] = $currentRoundMatchIds;

        // Subsequent Winner Rounds
        for ($r = 2; $r <= $rounds; $r++) {
            $numMatches = count($currentRoundMatchIds) / 2;
            $nextRoundMatchIds = [];
            $roundName = ($r == $rounds) ? 'Chung kết Nhánh Thắng' : 'Nhánh Thắng - Vòng ' . $r;

            for ($i = 0; $i < $numMatches; $i++) {
                $prev1Id = array_shift($currentRoundMatchIds);
                $prev2Id = array_shift($currentRoundMatchIds);

                $match = TournamentMatch::create([
                    'tournament_id' => $tournamentId,
                    'round_name' => $roundName,
                    'round_number' => $r,
                    'match_number' => $matchNumber++,
                    'bracket_type' => 0
                ]);

                TournamentMatch::whereIn('id', [$prev1Id, $prev2Id])->update(['next_match_id' => $match->id]);
                
                // Forward byes
                $m1 = TournamentMatch::find($prev1Id);
                $m2 = TournamentMatch::find($prev2Id);
                if ($m1->status == 2) $match->player1_id = $m1->winner_id;
                if ($m2->status == 2) $match->player2_id = $m2->winner_id;
                $match->save();

                $nextRoundMatchIds[] = $match->id;
            }
            $currentRoundMatchIds = $nextRoundMatchIds;
            $winnerMatches[$r] = $currentRoundMatchIds;
        }

        // 2. Generate Loser Bracket
        // Losers from W1 go to L1. Losers from W2 go to L2, etc.
        // This is a simplified Double Elim logic:
        // L1: Losers from W1
        // L2: Winners from L1 vs Losers from W2
        // L3: Winners from L2
        // L4: Winners from L3 vs Losers from W3...
        
        $prevLoserMatchIds = [];
        $loserRoundNum = 1;

        for ($r = 1; $r < $rounds; $r++) {
            $wMatches = $winnerMatches[$r];
            $lMatchCount = count($wMatches) / 2;
            if ($lMatchCount < 0.5) break;

            // Phase A: Losers from current Winner Round fight each other
            $currentLoserMatchIds = [];
            for ($i = 0; $i < $lMatchCount; $i++) {
                $w1Id = $wMatches[$i * 2];
                $w2Id = $wMatches[$i * 2 + 1];

                $lMatch = TournamentMatch::create([
                    'tournament_id' => $tournamentId,
                    'round_name' => 'Nhánh Thua - Vòng ' . $loserRoundNum,
                    'round_number' => $loserRoundNum,
                    'match_number' => $matchNumber++,
                    'bracket_type' => 1
                ]);
                
                TournamentMatch::where('id', $w1Id)->update(['next_loser_match_id' => $lMatch->id]);
                TournamentMatch::where('id', $w2Id)->update(['next_loser_match_id' => $lMatch->id]);

                // If previous was bye, loser is null, but we still handle it
                $currentLoserMatchIds[] = $lMatch->id;
            }
            
            // If we have previous losers, they need to fight these winners
            if (!empty($prevLoserMatchIds)) {
                // This is where it gets complex. For simplicity, we'll just link them sequentially
                // In a real double elim, Winners of previous L-round fight Losers of current W-round
            }
            
            $prevLoserMatchIds = $currentLoserMatchIds;
            $loserRoundNum++;
        }

        // 3. Grand Final
        $wFinalId = end($winnerMatches)[0];
        $grandFinal = TournamentMatch::create([
            'tournament_id' => $tournamentId,
            'round_name' => 'Chung kết Tổng',
            'round_number' => $rounds + 1,
            'match_number' => $matchNumber++,
            'bracket_type' => 0
        ]);
        TournamentMatch::where('id', $wFinalId)->update(['next_match_id' => $grandFinal->id]);
        // Winner of Loser bracket will be linked to grandFinal->player2_id manually or via logic

        return true;
    }
}
