<?php

namespace App\Repositories\Tournament;

use App\Components\CommonComponent;
use App\Enums\TournamentMatchStatus;
use App\Enums\TournamentParticipantStatus;
use App\Enums\TournamentStatus;
use App\Models\Tournament;
use App\Models\TournamentMatch;
use App\Models\TournamentParticipant;
use App\Models\TournamentRound;
use Illuminate\Support\Facades\DB;
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
                $query->where(CommonComponent::escapeLikeSentence('name', $request['free_word']));
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
        return $this->tournament->withCount('participants')->whereIn('status', [TournamentStatus::OPEN, TournamentStatus::ONGOING])->orderBy('start_date', 'asc')->get();
    }

    public function store($request)
    {
        $tournament = $this->tournament->fill($request->only([
            'name',
            'description',
            'start_date',
            'end_date',
            'registration_deadline',
            'max_participants',
            'entry_fee',
            'prize_pool',
            'status'
        ]));

        $tournament->created_by = auth('admin')->id();
        $saved = $tournament->save();

        if ($saved && $tournament->status == TournamentStatus::OPEN) { // Open
            $customers = \App\Models\Customer::all();
            \Illuminate\Support\Facades\Notification::send($customers, new \App\Notifications\TournamentCreated($tournament));
        }

        return $saved;
    }

    public function getById(string $id)
    {
        return $this->tournament->with(['participants.customer'])->find($id);
    }

    public function update($request, string $id)
    {
        $tournament = $this->getById($id);
        if (!$tournament) return false;

        $oldStatus = $tournament->status;

        $tournament->fill($request->only([
            'name',
            'description',
            'start_date',
            'end_date',
            'registration_deadline',
            'max_participants',
            'entry_fee',
            'prize_pool',
            'status'
        ]));

        $saved = $tournament->save();

        if ($saved && $oldStatus != TournamentStatus::OPEN && $tournament->status == TournamentStatus::OPEN) { // Changed to Open
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

    public function registerParticipant($tournamentId, $customerId, $data = [])
    {
        $exists = TournamentParticipant::where('tournament_id', $tournamentId)
            ->where('customer_id', $customerId)
            ->exists();

        if ($exists) {
            return false;
        }

        return TournamentParticipant::create(array_merge([
            'tournament_id' => $tournamentId,
            'customer_id' => $customerId,
            'status' => TournamentParticipantStatus::PENDING,
            'payment_status' => 0 // Unpaid
        ], $data));
    }

    public function updateParticipantStatus($participantId, $status)
    {
        $participant = TournamentParticipant::find($participantId);
        if (!$participant) return false;

        $participant->status = $status;
        return $participant->save();
    }

    public function cancelRegistration($tournamentId, $customerId)
    {
        $participant = TournamentParticipant::where('tournament_id', $tournamentId)
            ->where('customer_id', $customerId)
            ->first();

        if ($participant) {
            return $participant->delete();
        }

        return false;
    }

    public function generateBracket($tournamentId)
    {
        $tournament = $this->tournament->find($tournamentId);
        if (!$tournament) {
            return ['success' => false, 'error' => 'not_found'];
        }

        $participants = TournamentParticipant::where('tournament_id', $tournamentId)
            ->where('status', TournamentParticipantStatus::APPROVED)
            ->get();

        if ($participants->count() < 2) {
            return ['success' => false, 'error' => 'not_enough_participants'];
        }

        DB::transaction(function () use ($tournament, $tournamentId, $participants) {
            TournamentMatch::where('tournament_id', $tournamentId)->delete();
            TournamentRound::where('tournament_id', $tournamentId)->delete();

            $shuffled = $participants->shuffle()->values();

            $round = TournamentRound::create([
                'tournament_id' => $tournamentId,
                'round_number' => 1,
                'name' => 'Vòng 1',
            ]);

            for ($i = 0; $i < $shuffled->count(); $i += 2) {
                $player1 = $shuffled[$i];
                $player2 = isset($shuffled[$i + 1]) ? $shuffled[$i + 1] : null;

                $matchData = [
                    'tournament_id' => $tournamentId,
                    'round_id' => $round->id,
                    'player1_id' => $player1->id,
                    'player2_id' => $player2 ? $player2->id : null,
                    'player1_score' => 0,
                    'player2_score' => 0,
                    'status' => TournamentMatchStatus::UPCOMING,
                ];

                if (!$player2) {
                    $matchData['winner_id'] = $player1->id;
                    $matchData['status'] = TournamentMatchStatus::COMPLETED;
                }

                TournamentMatch::create($matchData);
            }

            if ($tournament->status == TournamentStatus::OPEN) {
                $tournament->update(['status' => TournamentStatus::ONGOING]);
            }
        });

        return ['success' => true];
    }

    public function generateNextRound($tournamentId)
    {
        $tournament = $this->tournament->find($tournamentId);
        if (!$tournament) {
            return ['success' => false, 'error' => 'not_found'];
        }

        $latestRound = TournamentRound::where('tournament_id', $tournamentId)
            ->orderBy('round_number', 'desc')
            ->first();

        if (!$latestRound) {
            return ['success' => false, 'error' => 'round_not_found'];
        }

        $unfinished = TournamentMatch::where('round_id', $latestRound->id)
            ->where('status', '!=', TournamentMatchStatus::COMPLETED)
            ->exists();

        if ($unfinished) {
            return ['success' => false, 'error' => 'unfinished_matches'];
        }

        $winners = TournamentMatch::where('round_id', $latestRound->id)
            ->orderBy('id', 'asc')
            ->pluck('winner_id')
            ->filter()
            ->values();

        if ($winners->count() === 0) {
            return ['success' => false, 'error' => 'winner_not_found'];
        }

        if ($winners->count() === 1) {
            $tournament->update(['status' => TournamentStatus::COMPLETED]);
            return ['success' => true, 'completed' => true];
        }

        DB::transaction(function () use ($tournamentId, $latestRound, $winners) {
            $nextRoundNumber = $latestRound->round_number + 1;
            $roundName = $this->getRoundName($nextRoundNumber, $winners->count());

            $nextRound = TournamentRound::create([
                'tournament_id' => $tournamentId,
                'round_number' => $nextRoundNumber,
                'name' => $roundName,
            ]);

            for ($i = 0; $i < $winners->count(); $i += 2) {
                $player1Id = $winners[$i];
                $player2Id = isset($winners[$i + 1]) ? $winners[$i + 1] : null;

                $matchData = [
                    'tournament_id' => $tournamentId,
                    'round_id' => $nextRound->id,
                    'player1_id' => $player1Id,
                    'player2_id' => $player2Id,
                    'player1_score' => 0,
                    'player2_score' => 0,
                    'status' => TournamentMatchStatus::UPCOMING,
                ];

                if (!$player2Id) {
                    $matchData['winner_id'] = $player1Id;
                    $matchData['status'] = TournamentMatchStatus::COMPLETED;
                }

                TournamentMatch::create($matchData);
            }
        });

        return ['success' => true, 'completed' => false];
    }

    public function resetBracket($tournamentId)
    {
        $tournament = $this->tournament->find($tournamentId);
        if (!$tournament) {
            return ['success' => false, 'error' => 'not_found'];
        }

        DB::transaction(function () use ($tournament, $tournamentId) {
            TournamentMatch::where('tournament_id', $tournamentId)->delete();
            TournamentRound::where('tournament_id', $tournamentId)->delete();

            if ($tournament->status == TournamentStatus::ONGOING) {
                $tournament->update(['status' => TournamentStatus::OPEN]);
            }
        });

        return ['success' => true];
    }

    public function storeMatch($tournamentId, array $data)
    {
        $round = TournamentRound::firstOrCreate([
            'tournament_id' => $tournamentId,
            'round_number' => 1,
        ], [
            'name' => $data['round_name'],
        ]);

        return TournamentMatch::create([
            'tournament_id' => $tournamentId,
            'round_id' => $round->id,
            'player1_id' => $data['player1_id'],
            'player2_id' => $data['player2_id'],
            'player1_score' => 0,
            'player2_score' => 0,
            'status' => TournamentMatchStatus::UPCOMING,
        ]);
    }

    public function updateMatch($matchId, array $data)
    {
        $match = TournamentMatch::find($matchId);
        if (!$match) {
            return false;
        }

        if ($data['status'] == TournamentMatchStatus::COMPLETED && empty($data['winner_id'])) {
            if ($data['player1_score'] > $data['player2_score']) {
                $data['winner_id'] = $match->player1_id;
            } elseif ($data['player2_score'] > $data['player1_score']) {
                $data['winner_id'] = $match->player2_id;
            }
        }

        return $match->update($data);
    }

    public function destroyMatch($tournamentId, $matchId)
    {
        $match = TournamentMatch::where('tournament_id', $tournamentId)->find($matchId);
        if (!$match) {
            return false;
        }

        return $match->delete();
    }

    private function getRoundName($roundNumber, $winnerCount): string
    {
        if ($winnerCount <= 2) {
            return 'Chung kết';
        }

        if ($winnerCount <= 4) {
            return 'Bán kết';
        }

        if ($winnerCount <= 8) {
            return 'Tứ kết';
        }

        return 'Vòng ' . $roundNumber;
    }
}
