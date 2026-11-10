<?php

namespace Accounting\Infrastructure\Persistence;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * CustomerPayment - receipt from customer.
 */
class CustomerPayment extends Model
{
    protected $table = 'customer_payments';

    protected $fillable = [
        'tenant_id', 'customer_id', 'payment_number', 'payment_date',
        'amount', 'journal_entry_id',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:4',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(\Sales\Infrastructure\Persistence\Customer::class);
    }

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class);
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(PaymentAllocation::class, 'payment_id');
    }
}