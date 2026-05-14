<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;
use Illuminate\Database\Eloquent\SoftDeletes;

class TournamentMatch extends Model
{
    use HasFactory;
    use Sortable;
    use SoftDeletes;

    protected $fillable = [
        'tournament_id',
        'round_name',
        'player1_id',
        'player2_id',
        'player1_score',
        'player2_score',
        'winner_id',
        'status',
    ];

    public function tournament()
    {
        return $this->belongsTo(Tournament::class);
    }

    public function player1()
    {
        return $this->belongsTo(Customer::class, 'player1_id');
    }

    public function player2()
    {
        return $this->belongsTo(Customer::class, 'player2_id');
    }

    public function winner()
    {
        return $this->belongsTo(Customer::class, 'winner_id');
    }
}
