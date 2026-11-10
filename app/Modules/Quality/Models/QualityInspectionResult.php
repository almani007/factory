<?php

namespace App\Modules\Quality\Models;

use App\Modules\Tenancy\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QualityInspectionResult extends Model
{
    use HasFactory, HasTenant;

    protected $fillable = [
        'quality_inspection_id',
        'tenant_id',
        'template_item_id',
        'measured_value',
        'result',
        'notes',
    ];

    protected $casts = [
        'measured_value' => 'decimal:4',
    ];

    public function inspection(): BelongsTo
    {
        return $this->belongsTo(QualityInspection::class);
    }
}