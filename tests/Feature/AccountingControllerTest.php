<?php

namespace Tests\Feature;

use Tests\TestCase;

class AccountingControllerTest extends TestCase
{
    /** @test */
    public function user_can_view_accounting_dashboard()
    {
        $response = $this->get('/accounting');

        $response->assertStatus(200);
        $response->assertInertia(
            fn ($page) => $page->component('Accounting/Accounting')
        );
    }

    /** @test */
    public function user_can_view_chart_of_accounts()
    {
        $response = $this->get('/accounting/chart-of-accounts');

        $response->assertStatus(200);
        $response->assertInertia(
            fn ($page) => $page->component('Accounting/ChartOfAccounts')
        );
    }

    /** @test */
    public function user_can_view_trial_balance()
    {
        $response = $this->get('/accounting/trial-balance');

        $response->assertStatus(200);
        $response->assertInertia(
            fn ($page) => $page->component('Accounting/TrialBalance')
        );
    }

    /** @test */
    public function user_can_view_profit_and_loss()
    {
        $response = $this->get('/accounting/profit-loss');

        $response->assertStatus(200);
        $response->assertInertia(
            fn ($page) => $page->component('Accounting/ProfitLoss')
        );
    }

    /** @test */
    public function user_can_view_balance_sheet()
    {
        $response = $this->get('/accounting/balance-sheet');

        $response->assertStatus(200);
        $response->assertInertia(
            fn ($page) => $page->component('Accounting/BalanceSheet')
        );
    }

    /** @test */
    public function user_can_view_bank_reconciliation()
    {
        $response = $this->get('/accounting/bank-reconciliation');

        $response->assertStatus(200);
        $response->assertInertia(
            fn ($page) => $page->component('Accounting/BankReconciliation')
        );
    }

    /** @test */
    public function user_can_view_fixed_assets()
    {
        $response = $this->get('/accounting/fixed-assets');

        $response->assertStatus(200);
        $response->assertInertia(
            fn ($page) => $page->component('Accounting/FixedAssets')
        );
    }
}
