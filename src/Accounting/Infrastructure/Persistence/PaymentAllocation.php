<?php

namespace Accounting\Infrastructure\Persistence;

use Illuminate\Database\Eloquent\Model;

/**
 * PaymentAllocation - links a payment to specific invoices/bills.
 */
class PaymentAllocation extends Model
{
    protected $table = 'payment_allocations';

    protected $fillable = [
        'tenant_id', 'payment_id', 'payment_type', 'document_id', 'amount',
    ];

    protected $casts = [
        'amount' => 'decimal:4',
    ];
}