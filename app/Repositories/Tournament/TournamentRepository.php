<?php

namespace App\Repositories\Tournament;

use App\Components\CommonComponent;
use App\Models\Tournament;
use App\Models\TournamentParticipant;
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
        return $this->tournament->with(['participants.customer'])->find($id);
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
}
