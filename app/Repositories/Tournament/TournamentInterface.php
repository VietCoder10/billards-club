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
    public function registerParticipant($tournamentId, $customerId);
    public function updateParticipantStatus($participantId, $status);
    
    // Match methods
    public function getMatches($tournamentId);
    public function storeMatch($request);
    public function updateMatchScore($matchId, $data);
    public function generateBracket($tournamentId);
}
