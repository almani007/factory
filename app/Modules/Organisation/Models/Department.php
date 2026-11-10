<?php

namespace App\Modules\Organisation\Models;

use App\Modules\Tenancy\Traits\HasTenant;
use App\Modules\Tenancy\Traits\HasFactoryScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Department extends Model
{
    use HasFactory, HasTenant, HasFactoryScope;

    protected $fillable = [
        'tenant_id',
        'factory_id',
        'name',
        'code',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function factory(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\Tenancy\Models\Factory::class);
    }

    public function workCentres(): HasMany
    {
        return $this->hasMany(WorkCentre::class);
    }
}