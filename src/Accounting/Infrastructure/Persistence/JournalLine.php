<?php

namespace Accounting\Infrastructure\Persistence;

use Tenancy\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JournalLine extends Model
{
    use HasFactory, HasTenant;

    protected $fillable = [
        'entry_id',
        'tenant_id',
        'account_id',
        'debit_amount',
        'credit_amount',
        'debit_amount_base',
        'credit_amount_base',
        'currency',
        'exchange_rate',
        'dimension_values',
        'description',
    ];

    protected $casts = [
        'debit_amount' => 'decimal:4',
        'credit_amount' => 'decimal:4',
        'debit_amount_base' => 'decimal:4',
        'credit_amount_base' => 'decimal:4',
        'exchange_rate' => 'decimal:6',
        'dimension_values' => 'array',
    ];

    public function entry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class, 'entry_id');
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}