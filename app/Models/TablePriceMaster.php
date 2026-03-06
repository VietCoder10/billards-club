<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Kyslik\ColumnSortable\Sortable;

class TablePriceMaster extends Model
{
    use HasFactory, SoftDeletes;
    use Sortable;

    protected $table = 'table_price_master';

    protected $fillable = [
        'price_name',
        'price_per_hour',
    ];

    protected $casts = [
        'price_per_hour' => 'decimal:2',
    ];
}
