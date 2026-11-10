<?php

namespace Accounting\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use Accounting\Application\Services\JournalEntryService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * AccountingController - double-entry financial ledger operations.
 */
class AccountingController extends Controller
{
    protected JournalEntryService $journalService;

    public function __construct(JournalEntryService $journalService)
    {
        $this->journalService = $journalService;
    }

    /**
     * Chart of accounts.
     */
    public function chartOfAccounts(Request $request): JsonResponse
    {
        $query = \Accounting\Infrastructure\Persistence\Account::where(
            'tenant_id',
            $request->user()->tenant_id ?? $request->input('tenant_id')
        );

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        return response()->json([
            'status' => 'success',
            'data' => $query->orderBy('code')->get(),
        ]);
    }

    /**
     * Create a chart of account.
     */
    public function createAccount(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:chart_of_accounts,code',
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:asset,liability,equity,revenue,expense',
            'account_group_id' => 'nullable|exists:account_groups,id',
            'parent_id' => 'nullable|exists:chart_of_accounts,id',
            'is_bank_account' => 'nullable|boolean',
            'is_cash_account' => 'nullable|boolean',
            'allow_direct_posting' => 'nullable|boolean',
        ]);

        $account = \Accounting\Infrastructure\Persistence\Account::create(array_merge(
            $validated,
            ['tenant_id' => $request->user()->tenant_id ?? $request->input('tenant_id')]
        ));

        return response()->json([
            'status' => 'success',
            'data' => $account,
        ], 201);
    }

    /**
     * List journal entries.
     */
    public function journalEntries(Request $request): JsonResponse
    {
        $query = \Accounting\Infrastructure\Persistence\JournalEntry::with('lines.account')
            ->where('tenant_id', $request->user()->tenant_id ?? $request->input('tenant_id'));

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        if ($request->has('from_date')) {
            $query->where('posting_date', '>=', $request->from_date);
        }
        if ($request->has('to_date')) {
            $query->where('posting_date', '<=', $request->to_date);
        }

        return response()->json([
            'status' => 'success',
            'data' => $query->orderBy('posting_date', 'desc')->paginate($request->input('per_page', 15)),
        ]);
    }

    /**
     * Post a journal entry.
     */
    public function postJournal(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'accounting_book_id' => 'required|exists:accounting_books,id',
            'fiscal_period_id' => 'nullable|exists:accounting_periods,id',
            'document_date' => 'required|date',
            'posting_date' => 'required|date',
            'currency' => 'nullable|string|size:3',
            'exchange_rate_to_base' => 'nullable|numeric',
            'reference' => 'required|string|max:255|unique:journal_entries,reference',
            'description' => 'nullable|string',
            'created_by' => 'required|exists:users,id',
            'approved_by' => 'nullable|exists:users,id',
            'lines' => 'required|array|min:2',
            'lines.*.account_id' => 'required|exists:chart_of_accounts,id',
            'lines.*.debit_amount' => 'required|numeric|gte:0',
            'lines.*.credit_amount' => 'required|numeric|gte:0',
            'lines.*.currency' => 'nullable|string|size:3',
            'lines.*.exchange_rate' => 'nullable|numeric',
            'lines.*.dimension_values' => 'nullable|array',
            'lines.*.description' => 'nullable|string',
        ]);

        try {
            $entry = $this->journalService->postJournal($validated);

            return response()->json([
                'status' => 'success',
                'data' => $entry->load('lines.account'),
            ], 201);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'error' => [
                    'code' => 'JOURNAL_FAILED',
                    'message' => $e->getMessage(),
                ],
            ], 400);
        }
    }

    /**
     * Get a journal entry with lines.
     */
    public function getJournal(string $id): JsonResponse
    {
        $entry = \Accounting\Infrastructure\Persistence\JournalEntry::with('lines.account')->findOrFail($id);
        return response()->json(['status' => 'success', 'data' => $entry]);
    }

    /**
     * Reverse a journal entry.
     */
    public function reverseJournal(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'reason' => 'required|string',
            'user_id' => 'required|exists:users,id',
        ]);

        try {
            $reversal = $this->journalService->reverseJournal(
                (int) $id,
                $validated['reason'],
                (int) $validated['user_id']
            );

            return response()->json([
                'status' => 'success',
                'data' => $reversal->load('lines.account'),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'error' => [
                    'code' => 'REVERSAL_FAILED',
                    'message' => $e->getMessage(),
                ],
            ], 400);
        }
    }

    /**
     * Trial balance report.
     */
    public function trialBalance(Request $request): JsonResponse
    {
        $tenantId = $request->user()->tenant_id ?? $request->input('tenant_id');

        $accounts = \Accounting\Infrastructure\Persistence\Account::where('tenant_id', $tenantId)
            ->where('allow_direct_posting', true)
            ->get();

        $trialBalance = $accounts->map(function ($account) use ($tenantId) {
            $balance = $this->journalService->getAccountBalance($tenantId, $account->id);

            return [
                'account_code' => $account->code,
                'account_name' => $account->name,
                'account_type' => $account->type,
                'debit_balance' => $balance > 0 ? $balance : 0,
                'credit_balance' => $balance < 0 ? abs($balance) : 0,
            ];
        });

        $totalDebit = $trialBalance->sum('debit_balance');
        $totalCredit = $trialBalance->sum('credit_balance');

        return response()->json([
            'status' => 'success',
            'data' => [
                'accounts' => $trialBalance,
                'total_debit' => $totalDebit,
                'total_credit' => $totalCredit,
                'is_balanced' => bccomp((string) $totalDebit, (string) $totalCredit, 4) === 0,
            ],
        ]);
    }

    /**
     * Profit and Loss report.
     */
    public function profitAndLoss(Request $request): JsonResponse
    {
        $tenantId = $request->user()->tenant_id ?? $request->input('tenant_id');

        $revenue = $this->journalService->getAccountBalance($tenantId, null);
        // Simplified P&L - would filter by revenue/expense accounts by period

        return response()->json([
            'status' => 'success',
            'data' => [
                'revenue' => 0,
                'expenses' => 0,
                'net_profit' => 0,
                'message' => 'P&L report - implement with period filtering',
            ],
        ]);
    }

    /**
     * Balance Sheet report.
     */
    public function balanceSheet(Request $request): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data' => [
                'assets' => 0,
                'liabilities' => 0,
                'equity' => 0,
                'message' => 'Balance Sheet - implement with period filtering',
            ],
        ]);
    }
}