<?php

namespace App\Shared\Contracts;

interface JournalEntryServiceInterface
{
    public function postJournal(array $dto): \App\Modules\Accounting\Models\JournalEntry;
    public function reverseJournal(int $entryId, string $reason, int $userId): \App\Modules\Accounting\Models\JournalEntry;
    public function validateBalance(\App\Modules\Accounting\Models\JournalEntry $entry): bool;
}