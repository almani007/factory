<?php

namespace App\Modules\Organisation\Models;

use App\Modules\Tenancy\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WarehouseBin extends Model
{
    use HasFactory, HasTenant;

    protected $fillable = [
        'tenant_id',
        'warehouse_id',
        'name',
        'code',
        'rack',
        'level',
        'bin',
    ];

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }
}