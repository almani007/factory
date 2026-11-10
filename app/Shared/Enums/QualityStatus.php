<?php

namespace App\Shared\Enums;

enum QualityStatus: string
{
    case PENDING = 'pending';
    case PASSED = 'passed';
    case FAILED = 'failed';
    case PARTIALLY_ACCEPTED = 'partially_accepted';
    case REWORK_REQUIRED = 'rework_required';
    case REJECTED = 'rejected';
}