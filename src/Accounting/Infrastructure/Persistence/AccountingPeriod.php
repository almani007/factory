<?php

namespace Accounting\Infrastructure\Persistence;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * AccountingPeriod - fiscal period with open/closed status.
 * Closed periods block postings (BR-013).
 */
class AccountingPeriod extends Model
{
    protected $table = 'accounting_periods';

    protected $fillable = [
        'tenant_id', 'financial_year_id', 'name', 'start_date',
        'end_date', 'status',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'status' => 'string',
    ];

    public function journalEntries(): HasMany
    {
        return $this->hasMany(JournalEntry::class, 'fiscal_period_id');
    }
}