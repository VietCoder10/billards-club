<?php

namespace App\Models;

use App\Traits\SaveUserIdTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Kyslik\ColumnSortable\Sortable;

class Invoice extends Model
{
    use HasFactory, SoftDeletes;
    use Sortable;
    use SaveUserIdTrait;

    protected $fillable = [
        'customer_id',
        'invoice_number',
        'table_name',
        'table_total',
        'service_total',
        'total_amount',
        'discount',
        'final_amount',
        'payment_method',
        'paid_at',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    protected $appends = ['payment_method_label'];

    public function getPaymentMethodLabelAttribute()
    {
        return \App\Enums\PaymentMethod::getLabel($this->payment_method);
    }



    public function invoice_details(): HasMany
    {
        return $this->hasMany(InvoiceDetail::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
