<?php

namespace App\Modules\Sales\Models;

use App\Modules\Tenancy\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Customer extends Model
{
    use HasFactory, HasTenant;

    protected $fillable = [
        'tenant_id',
        'name',
        'code',
        'contact_person',
        'email',
        'phone',
        'address',
        'city',
        'country',
        'tax_id',
        'credit_limit',
        'payment_terms',
        'is_active',
    ];

    protected $casts = [
        'credit_limit' => 'decimal:4',
        'is_active' => 'boolean',
    ];

    public function salesOrders(): HasMany
    {
        return $this->hasMany(SalesOrder::class);
    }
}