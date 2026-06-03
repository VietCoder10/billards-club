<?php

namespace App\Repositories\Tournament;

use Illuminate\Http\Request;

interface TournamentInterface
{
    public function get($request);
    public function getActiveTournaments();
    public function store($request);
    public function getById(string $id);
    public function update($request, string $id);
    public function destroy(string $id);
    
    // Participant methods
    public function getParticipants($tournamentId, $request);
    public function registerParticipant($tournamentId, $customerId, $data = []);
    public function updateParticipantStatus($participantId, $status);
    public function cancelRegistration($tournamentId, $customerId);

    // Bracket & match methods
    public function generateBracket($tournamentId);
    public function generateNextRound($tournamentId);
    public function resetBracket($tournamentId);
    public function storeMatch($tournamentId, array $data);
    public function updateMatch($matchId, array $data);
    public function destroyMatch($matchId);
}
