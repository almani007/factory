<?php

namespace Dispatch\Infrastructure\Persistence;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliveryNote extends Model
{
    protected $table = 'delivery_notes';

    protected $fillable = [
        'tenant_id', 'dispatch_id', 'note_number', 'note_date', 'notes',
    ];

    protected $casts = [
        'note_date' => 'date',
    ];

    public function dispatch(): BelongsTo
    {
        return $this->belongsTo(Dispatch::class);
    }
}