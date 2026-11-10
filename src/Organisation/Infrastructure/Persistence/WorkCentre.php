<?php

namespace Organisation\Infrastructure\Persistence;

use Tenancy\Traits\HasTenant;
use Tenancy\Traits\HasFactoryScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkCentre extends Model
{
    use HasFactory, HasTenant, HasFactoryScope;

    protected $fillable = [
        'tenant_id',
        'factory_id',
        'department_id',
        'name',
        'code',
        'capacity_per_hour',
        'efficiency_percentage',
        'is_active',
    ];

    protected $casts = [
        'capacity_per_hour' => 'decimal:4',
        'efficiency_percentage' => 'decimal:4',
        'is_active' => 'boolean',
    ];

    public function factory(): BelongsTo
    {
        return $this->belongsTo(\Tenancy\Models\Factory::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }
}