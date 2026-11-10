<?php

namespace Shared\Contracts;

interface JournalEntryServiceInterface
{
    public function postJournal(array $dto): \Accounting\Infrastructure\Persistence\JournalEntry;
    public function reverseJournal(int $entryId, string $reason, int $userId): \Accounting\Infrastructure\Persistence\JournalEntry;
    public function validateBalance(\Accounting\Infrastructure\Persistence\JournalEntry $entry): bool;
}