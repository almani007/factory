<?php

namespace Shared\Contracts;

interface JournalEntryServiceInterface
{
    public function postJournal(array $dto): \Accounting\Models\JournalEntry;
    public function reverseJournal(int $entryId, string $reason, int $userId): \Accounting\Models\JournalEntry;
    public function validateBalance(\Accounting\Models\JournalEntry $entry): bool;
}