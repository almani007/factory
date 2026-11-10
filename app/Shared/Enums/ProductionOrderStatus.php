<?php

namespace App\Shared\Enums;

enum ProductionOrderStatus: string
{
    case DRAFT = 'draft';
    case PENDING_APPROVAL = 'pending_approval';
    case APPROVED = 'approved';
    case MATERIAL_RESERVED = 'material_reserved';
    case IN_PRODUCTION = 'in_production';
    case QUALITY_INSPECTION = 'quality_inspection';
    case COMPLETED = 'completed';
    case CLOSED = 'closed';
    case CANCELLED = 'cancelled';
}