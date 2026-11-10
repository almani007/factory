<?php

namespace Accounting\Infrastructure\Persistence;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * SupplierPayment - payment to supplier.
 */
class SupplierPayment extends Model
{
    protected $table = 'supplier_payments';

    protected $fillable = [
        'tenant_id', 'supplier_id', 'payment_number', 'payment_date',
        'amount', 'journal_entry_id',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:4',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(\Purchasing\Infrastructure\Persistence\Supplier::class);
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