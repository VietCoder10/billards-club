<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Kyslik\ColumnSortable\Sortable;

class Supplier extends Model
{
    //
    use HasFactory, SoftDeletes;
    use Sortable;
    protected $primaryKey = 'id';
    protected $table = "suppliers";
    protected $fillable = [
        'supplier_name',
        'contact_person',
        'email',
        'phone',
        'address',
        'note',
        'status',
    ];
    protected $casts = [
        'status' => 'int',
    ];
    protected $hidden = [
        'deleted_at',
        'created_at',
        'updated_at'
    ];
}
