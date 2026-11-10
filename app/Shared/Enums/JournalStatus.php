<?php

namespace App\Shared\Enums;

enum JournalStatus: string
{
    case DRAFT = 'draft';
    case SUBMITTED = 'submitted';
    case POSTED = 'posted';
    case REVERSED = 'reversed';
}