<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Kyslik\ColumnSortable\Sortable;

class TournamentParticipant extends Model
{
    use HasFactory;
    use Sortable;
    use SoftDeletes;

    protected $fillable = [
        'tournament_id',
        'customer_id',
        'status',
        'payment_status',
    ];

    public function tournament()
    {
        return $this->belongsTo(Tournament::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
