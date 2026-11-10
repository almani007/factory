<?php

namespace App\Modules\Accounting\Models;

use App\Modules\Tenancy\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JournalEntry extends Model
{
    use HasFactory, HasTenant;

    const STATUS_DRAFT = 'draft';
    const STATUS_SUBMITTED = 'submitted';
    const STATUS_POSTED = 'posted';
    const STATUS_REVERSED = 'reversed';

    protected $fillable = [
        'tenant_id',
        'accounting_book_id',
        'fiscal_period_id',
        'document_date',
        'posting_date',
        'currency',
        'exchange_rate_to_base',
        'reference',
        'description',
        'status',
        'reversal_reference_id',
        'source_transaction_type',
        'source_transaction_id',
        'created_by',
        'approved_by',
        'posted_at',
    ];

    protected $casts = [
        'exchange_rate_to_base' => 'decimal:6',
        'document_date' => 'datetime',
        'posting_date' => 'datetime',
        'posted_at' => 'datetime',
        'status' => 'string',
    ];

    public function lines(): HasMany
    {
        return $this->hasMany(JournalLine::class, 'entry_id');
    }

    public function reversal(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class, 'reversal_reference_id');
    }

    public function isPosted(): bool
    {
        return $this->status === self::STATUS_POSTED;
    }
}