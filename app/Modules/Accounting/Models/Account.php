<?php

namespace App\Modules\Accounting\Models;

use App\Modules\Tenancy\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Account extends Model
{
    use HasFactory, HasTenant;

    const TYPE_ASSET = 'asset';
    const TYPE_LIABILITY = 'liability';
    const TYPE_EQUITY = 'equity';
    const TYPE_REVENUE = 'revenue';
    const TYPE_EXPENSE = 'expense';

    protected $fillable = [
        'tenant_id',
        'code',
        'name',
        'type',
        'group_id',
        'parent_id',
        'is_bank_account',
        'is_cash_account',
        'allow_direct_posting',
        'is_active',
    ];

    protected $casts = [
        'is_bank_account' => 'boolean',
        'is_cash_account' => 'boolean',
        'allow_direct_posting' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function journalLines(): HasMany
    {
        return $this->hasMany(JournalLine::class);
    }
}