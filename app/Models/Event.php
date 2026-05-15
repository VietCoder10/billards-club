<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Event extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'event_type',
        'name',
        'location',
        'start_date',
        'end_date',
        'target_date',
        'note',
        'private_flag',
        'created_by'
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_events', 'event_id', 'user_id')
            ->withPivot('is_complete', 'memo', 'id')
            ->withTimestamps();
    }

    public function user_events()
    {
        return $this->hasMany(UserEvent::class, 'event_id');
    }
}
