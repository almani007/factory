<?php

namespace App\Modules\Accounting\Models;

use App\Modules\Tenancy\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountingPeriod extends Model
{
    use HasFactory, HasTenant;

    const STATUS_OPEN = 'open';
    const STATUS_CLOSED = 'closed';

    protected $fillable = [
        'tenant_id',
        'financial_year_id',
        'name',
        'start_date',
        'end_date',
        'status',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'status' => 'string',
    ];
}