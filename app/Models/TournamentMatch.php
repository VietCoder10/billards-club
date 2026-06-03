<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TournamentMatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'tournament_id',
        'round_id',
        'player1_id',
        'player2_id',
        'player1_score',
        'player2_score',
        'winner_id',
        'status',
        'match_time',
    ];

    protected $casts = [
        'match_time' => 'datetime',
    ];

    public function tournament()
    {
        return $this->belongsTo(Tournament::class);
    }

    public function round()
    {
        return $this->belongsTo(TournamentRound::class, 'round_id');
    }

    public function player1()
    {
        return $this->belongsTo(TournamentParticipant::class, 'player1_id');
    }

    public function player2()
    {
        return $this->belongsTo(TournamentParticipant::class, 'player2_id');
    }

    public function winner()
    {
        return $this->belongsTo(TournamentParticipant::class, 'winner_id');
    }
}
