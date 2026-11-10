<?php

namespace Sales\Infrastructure\Persistence;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerInvoiceLine extends Model
{
    protected $table = 'customer_invoice_lines';

    protected $fillable = [
        'customer_invoice_id', 'tenant_id', 'item_id', 'description',
        'quantity', 'unit_price', 'total_amount', 'tax_code_id', 'tax_amount',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
        'unit_price' => 'decimal:4',
        'total_amount' => 'decimal:4',
        'tax_amount' => 'decimal:4',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(CustomerInvoice::class);
    }
}