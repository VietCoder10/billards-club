<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Kyslik\ColumnSortable\Sortable;

class Order extends Model
{
    use HasFactory, SoftDeletes;
    use Sortable;

    protected $fillable = [
        'table_id',
        'user_id',
        'status',
        'started_at',
        'ended_at',
        'total_minutes',
        'table_total',
        'service_total',
        'final_total',
        'price_per_hour',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at'   => 'datetime',
        'table_total' => 'decimal:2',
        'service_total' => 'decimal:2',
        'final_total' => 'decimal:2',
        'price_per_hour' => 'decimal:2',
    ];
    protected $appends = ['order_status_label'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function details(): HasMany
    {
        return $this->hasMany(OrderDetail::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'id', 'order_id');
    }
    public function getOrderStatusLabelAttribute()
    {
        return OrderStatus::getLabel($this->status);
    }
}
