<?php

namespace App\Models;

use App\Enums\TableStatus;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Kyslik\ColumnSortable\Sortable;

class Table extends Model
{
    use HasFactory, SoftDeletes;
    use Sortable;

    protected $fillable = [
        'table_name',
        'status',
        'table_price_id',
    ];
    protected $casts = [
        'status' => 'integer',
        'table_price_id' => 'integer',
    ];
    protected $appends = ['status_label'];

    public function tablePrice(): BelongsTo
    {
        return $this->belongsTo(TablePriceMaster::class, 'table_price_id');
    }

    public function getStatusLabelAttribute()
    {
        return TableStatus::getLabel($this->status);
    }
}
