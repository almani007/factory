<?php

namespace Sales\Infrastructure\Persistence;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * CustomerInvoice - sales invoice sent to customer.
 */
class CustomerInvoice extends Model
{
    protected $table = 'customer_invoices';

    protected $fillable = [
        'tenant_id', 'customer_id', 'invoice_number', 'invoice_date',
        'due_date', 'total_amount', 'tax_amount', 'status',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'total_amount' => 'decimal:4',
        'tax_amount' => 'decimal:4',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(CustomerInvoiceLine::class);
    }
}