<?php

namespace Purchasing\Infrastructure\Persistence;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * SupplierInvoice - bill received from supplier.
 */
class SupplierInvoice extends Model
{
    protected $table = 'supplier_invoices';

    protected $fillable = [
        'tenant_id', 'supplier_id', 'invoice_number', 'invoice_date',
        'due_date', 'total_amount', 'tax_amount', 'status',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'total_amount' => 'decimal:4',
        'tax_amount' => 'decimal:4',
        'status' => 'string',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(SupplierInvoiceLine::class);
    }
}