<?php

namespace App\Models;

use App\Components\CommonComponent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Kyslik\ColumnSortable\Sortable;

class OrderDetail extends Model
{
    use HasFactory, SoftDeletes;
    use Sortable;

    protected $fillable = [
        'order_id',
        'product_id',
        'avatar',
        'product_name',
        'quantity',
        'price',
        'sub_total',
    ];
    protected $appends = ['avatar_url'];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
    public function getAvatarUrlAttribute()
    {
        return $this->avatar ? CommonComponent::getFullUrl($this->avatar) : url('/images/default-avatar.svg');
    }
}
