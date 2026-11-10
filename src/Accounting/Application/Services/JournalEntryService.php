<?php

namespace Accounting\Application\Services;

use Accounting\Infrastructure\Persistence\JournalEntry;
use Accounting\Infrastructure\Persistence\JournalLine;
use Accounting\Infrastructure\Persistence\Account;
use Shared\Domain\Exceptions\UnbalancedJournalException;
use Shared\Domain\Exceptions\PeriodClosedException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * JournalEntryService
 *
 * Implements the double-entry financial ledger. Every posted journal must
 * balance (Σ Debits = Σ Credits). Posted journals cannot be silently changed.
 *
 * Business rules:
 *   BR-010 Financial and inventory transactions must be atomic.
 *   BR-013 Period closing must protect historical financial records.
 *   BR-015 Reversals must use traceable compensating transactions.
 */
class JournalEntryService
{
    /**
     * Post a journal entry atomically.
     *
     * @param array $dto {
     *     tenant_id, accounting_book_id, fiscal_period_id,
     *     document_date, posting_date, currency, exchange_rate_to_base,
     *     reference, description, created_by, approved_by,
     *     lines: array of { account_id, debit_amount, credit_amount, currency,
     *                       exchange_rate, dimension_values, description }
     * }
     * @return JournalEntry
     * @throws UnbalancedJournalException
     * @throws PeriodClosedException
     */
    public function postJournal(array $dto): JournalEntry
    {
        return DB::transaction(function () use ($dto) {
            // Check period is not closed
            if (!empty($dto['fiscal_period_id'])) {
                $period = \Accounting\Infrastructure\Persistence\AccountingPeriod::find($dto['fiscal_period_id']);
                if ($period && $period->status === 'closed') {
                    throw new PeriodClosedException(
                        "Accounting period {$period->name} is closed. Cannot post backdated transactions."
                    );
                }
            }

            // Validate balance: Σ Debits = Σ Credits
            $totalDebit = 0.0;
            $totalCredit = 0.0;

            foreach ($dto['lines'] as $line) {
                $totalDebit += (float) ($line['debit_amount'] ?? 0);
                $totalCredit += (float) ($line['credit_amount'] ?? 0);
            }

            if (bccomp((string) $totalDebit, (string) $totalCredit, 4) !== 0) {
                throw new UnbalancedJournalException(
                    "Journal is not balanced. Debits: {$totalDebit}, Credits: {$totalCredit}"
                );
            }

            // Create journal entry header
            $entry = JournalEntry::create([
                'tenant_id' => $dto['tenant_id'],
                'accounting_book_id' => $dto['accounting_book_id'],
                'fiscal_period_id' => $dto['fiscal_period_id'] ?? null,
                'document_date' => $dto['document_date'],
                'posting_date' => $dto['posting_date'],
                'currency' => $dto['currency'] ?? 'PKR',
                'exchange_rate_to_base' => (float) ($dto['exchange_rate_to_base'] ?? 1),
                'reference' => $dto['reference'],
                'description' => $dto['description'] ?? null,
                'status' => 'posted',
                'created_by' => $dto['created_by'],
                'approved_by' => $dto['approved_by'] ?? null,
                'posted_at' => now(),
            ]);

            // Create journal lines
            foreach ($dto['lines'] as $line) {
                $debit = (float) ($line['debit_amount'] ?? 0);
                $credit = (float) ($line['credit_amount'] ?? 0);
                $rate = (float) ($line['exchange_rate'] ?? 1);

                JournalLine::create([
                    'entry_id' => $entry->id,
                    'tenant_id' => $dto['tenant_id'],
                    'account_id' => $line['account_id'],
                    'debit_amount' => $debit,
                    'credit_amount' => $credit,
                    'debit_amount_base' => $debit * $rate,
                    'credit_amount_base' => $credit * $rate,
                    'currency' => $line['currency'] ?? $dto['currency'] ?? 'PKR',
                    'exchange_rate' => $rate,
                    'dimension_values' => $line['dimension_values'] ?? null,
                    'description' => $line['description'] ?? null,
                ]);
            }

            Log::info('Journal entry posted', [
                'entry_id' => $entry->id,
                'tenant_id' => $dto['tenant_id'],
                'reference' => $dto['reference'],
                'total_debit' => $totalDebit,
                'total_credit' => $totalCredit,
                'user_id' => $dto['created_by'],
            ]);

            return $entry->load('lines.account');
        }, 3);
    }

    /**
     * Reverse a posted journal entry with a compensating transaction.
     *
     * @param int $entryId
     * @param string $reason
     * @param int $userId
     * @return JournalEntry
     */
    public function reverseJournal(int $entryId, string $reason, int $userId): JournalEntry
    {
        return DB::transaction(function () use ($entryId, $reason, $userId) {
            $original = JournalEntry::with('lines')->findOrFail($entryId);

            if ($original->status !== 'posted') {
                throw new \LogicException('Can only reverse posted journals.');
            }

            // Check period
            if ($original->fiscal_period_id) {
                $period = \Accounting\Infrastructure\Persistence\AccountingPeriod::find($original->fiscal_period_id);
                if ($period && $period->status === 'closed') {
                    throw new PeriodClosedException(
                        "Cannot reverse journal in closed period {$period->name}."
                    );
                }
            }

            // Create reversal (swap debit/credit)
            $reversal = JournalEntry::create([
                'tenant_id' => $original->tenant_id,
                'accounting_book_id' => $original->accounting_book_id,
                'fiscal_period_id' => $original->fiscal_period_id,
                'document_date' => now(),
                'posting_date' => now(),
                'currency' => $original->currency,
                'exchange_rate_to_base' => $original->exchange_rate_to_base,
                'reference' => 'REV-' . $original->reference,
                'description' => "Reversal of {$original->reference}: {$reason}",
                'status' => 'posted',
                'reversal_reference_id' => $original->id,
                'created_by' => $userId,
                'approved_by' => $userId,
                'posted_at' => now(),
            ]);

            foreach ($original->lines as $line) {
                JournalLine::create([
                    'entry_id' => $reversal->id,
                    'tenant_id' => $original->tenant_id,
                    'account_id' => $line->account_id,
                    'debit_amount' => (float) $line->credit_amount,
                    'credit_amount' => (float) $line->debit_amount,
                    'debit_amount_base' => (float) $line->credit_amount_base,
                    'credit_amount_base' => (float) $line->debit_amount_base,
                    'currency' => $line->currency,
                    'exchange_rate' => (float) $line->exchange_rate,
                    'dimension_values' => $line->dimension_values,
                    'description' => "Reversal: {$line->description}",
                ]);
            }

            // Mark original as reversed
            $original->status = 'reversed';
            $original->save();

            Log::info('Journal entry reversed', [
                'original_id' => $original->id,
                'reversal_id' => $reversal->id,
                'reason' => $reason,
                'user_id' => $userId,
            ]);

            return $reversal->load('lines.account');
        }, 3);
    }

    /**
     * Validate that a journal balances without posting.
     */
    public function validateBalance(array $lines): bool
    {
        $totalDebit = 0.0;
        $totalCredit = 0.0;

        foreach ($lines as $line) {
            $totalDebit += (float) ($line['debit_amount'] ?? 0);
            $totalCredit += (float) ($line['credit_amount'] ?? 0);
        }

        return bccomp((string) $totalDebit, (string) $totalCredit, 4) === 0;
    }

    /**
     * Get account balance (debit - credit) for a given account.
     */
    public function getAccountBalance(int $tenantId, string $accountId, ?string $periodId = null): float
    {
        $query = JournalLine::join('journal_entries', 'journal_lines.entry_id', '=', 'journal_entries.id')
            ->where('journal_lines.tenant_id', $tenantId)
            ->where('journal_lines.account_id', $accountId)
            ->where('journal_entries.status', 'posted');

        if ($periodId) {
            $query->where('journal_entries.fiscal_period_id', $periodId);
        }

        $debits = $query->sum('journal_lines.debit_amount');
        $credits = $query->sum('journal_lines.credit_amount');

        return (float) $debits - (float) $credits;
    }
}