<?php

namespace Shared\Enums;

enum CostingMethod: string
{
    case STANDARD = 'standard';
    case ACTUAL = 'actual';
    case WEIGHTED_AVERAGE = 'weighted_average';
    case FIFO = 'fifo';
    case JOB = 'job';
    case PROCESS = 'process';
    case BATCH = 'batch';
}