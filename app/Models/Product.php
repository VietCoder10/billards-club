<?php

namespace App\Models;

use App\Components\CommonComponent;
use App\Enums\ProductCategory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Kyslik\ColumnSortable\Sortable;

class Product extends Model
{
    use HasFactory, SoftDeletes;
    use Sortable;

    protected $fillable = [
        'product_name',
        'avatar',
        'category',
        'sku',
        'supplier_id',
        'cost_price',
        'sale_price',
        'quantity',
        'total_amount',
        'description',
    ];
    protected $appends = ['avatar_url', 'category_label'];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }
    public function supplierNameSortable($query, $direction)
    {
        return $query->orderBy('suppliers.supplier_name', $direction);
    }
    public function getCategoryLabelAttribute()
    {
        return ProductCategory::getLabel($this->category);
    }
    public function getAvatarUrlAttribute()
    {
        return $this->avatar ? CommonComponent::getFullUrl($this->avatar) : url('/images/default-avatar.svg');
    }
}
