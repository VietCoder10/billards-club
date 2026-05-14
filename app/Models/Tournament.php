<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Kyslik\ColumnSortable\Sortable;

class Tournament extends Model
{
    use HasFactory;
    use Sortable;
    use \Illuminate\Database\Eloquent\SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'start_date',
        'end_date',
        'registration_deadline',
        'max_participants',
        'entry_fee',
        'prize_pool',
        'status',
        'created_by'
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'registration_deadline' => 'datetime',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function participants()
    {
        return $this->hasMany(TournamentParticipant::class);
    }

    public function matches()
    {
        return $this->hasMany(TournamentMatch::class);
    }
}
