<?php

namespace App\Modules\Organisation\Models;

use App\Modules\Tenancy\Traits\HasTenant;
use App\Modules\Tenancy\Traits\HasFactoryScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Warehouse extends Model
{
    use HasFactory, HasTenant, HasFactoryScope;

    protected $fillable = [
        'tenant_id',
        'factory_id',
        'name',
        'code',
        'address',
        'type',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function factory(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\Tenancy\Models\Factory::class);
    }

    public function bins(): HasMany
    {
        return $this->hasMany(WarehouseBin::class);
    }
}