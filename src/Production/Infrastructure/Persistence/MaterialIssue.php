<?php

namespace Production\Infrastructure\Persistence;

use Tenancy\Traits\HasTenant;
use Tenancy\Traits\HasFactoryScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaterialIssue extends Model
{
    use HasFactory, HasTenant, HasFactoryScope;

    protected $fillable = [
        'tenant_id',
        'factory_id',
        'production_order_id',
        'work_order_id',
        'reference',
        'issue_date',
        'issued_by',
        'notes',
    ];

    protected $casts = [
        'issue_date' => 'datetime',
    ];

    public function lines(): HasMany
    {
        return $this->hasMany(MaterialIssueLine::class);
    }

    public function productionOrder(): BelongsTo
    {
        return $this->belongsTo(ProductionOrder::class);
    }
}