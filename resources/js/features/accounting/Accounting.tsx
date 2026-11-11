/**
 * Brixel FactoryOS — Accounting & Finance
 * Double-entry ledger, journal entries, and financial reports.
 */

import { Head, Link, router, usePage } from "@inertiajs/react";
import { AppLayout } from "@/components/layout";
import { PageHeader } from "@/components/layout";
import { DataTable } from "@/components/DataTable";
import { Plus, Eye, Edit } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";

export default function Accounting() {
  const { props: data } = usePage() as any;
  const journalEntries = data?.journal_entries?.data || [];
  const meta = data?.journal_entries?.meta || {};
  const summary = data?.finance_summary || {
    trial_balance: "Rs. 0",
    receivable: "Rs. 0",
    payable: "Rs. 0",
  };

  return (
    <AppLayout title="Accounting & Finance">
      <PageHeader
        title="Accounting & Finance"
        subtitle="Double-entry ledger, journal entries, and financial reports"
        breadcrumbs={[{ label: "Accounting", href: "/accounting" }]}
      >
        <div className="flex items-center gap-2">
          <Button onClick={() => router.visit("/accounting/journal-entries/create")}>
            <Plus size={16} /> New Journal Entry
          </Button>
          <Link
            href="/accounting/chart-of-accounts"
            className="inline-flex h-9 items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 text-sm font-medium text-slate-700 shadow-sm transition-colors hover:bg-slate-50"
          >
            Chart of Accounts
          </Link>
        </div>
      </PageHeader>

      {/* KPI cards */}
      <div className="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
        <Card>
          <CardHeader className="p-5 pb-2">
            <CardTitle className="text-sm font-medium text-slate-500">Trial Balance</CardTitle>
          </CardHeader>
          <CardContent className="p-5 pt-0">
            <p className="text-2xl font-semibold tracking-tight text-slate-900 tabular-nums">{summary.trial_balance}</p>
            <Link
              href="/accounting/trial-balance"
              className="mt-1 inline-flex items-center gap-1 text-xs text-factory-600 hover:text-factory-700"
            >
              View report <span className="text-factory-500">→</span>
            </Link>
          </CardContent>
        </Card>
        <Card>
          <CardHeader className="p-5 pb-2">
            <CardTitle className="text-sm font-medium text-slate-500">Accounts Receivable</CardTitle>
          </CardHeader>
          <CardContent className="p-5 pt-0">
            <p className="text-2xl font-semibold tracking-tight text-slate-900 tabular-nums">{summary.receivable}</p>
            <p className="text-xs text-slate-600 mt-1">Outstanding customer invoices</p>
          </CardContent>
        </Card>
        <Card>
          <CardHeader className="p-5 pb-2">
            <CardTitle className="text-sm font-medium text-slate-500">Accounts Payable</CardTitle>
          </CardHeader>
          <CardContent className="p-5 pt-0">
            <p className="text-2xl font-semibold tracking-tight text-slate-900 tabular-nums">{summary.payable}</p>
            <p className="text-xs text-slate-600 mt-1">Outstanding supplier bills</p>
          </CardContent>
        </Card>
      </div>

      {/* Quick access to financial reports */}
      <div className="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
        <Card onClick={() => router.visit("/accounting/trial-balance")} className="cursor-pointer hover:bg-slate-50 transition-colors">
          <CardHeader className="p-5 pb-2">
            <CardTitle className="text-sm font-semibold text-slate-900">Trial Balance</CardTitle>
          </CardHeader>
          <CardContent className="p-5 pt-0">
            <p className="text-sm text-slate-600">Verify debit and credit balances across all accounts.</p>
          </CardContent>
        </Card>
        <Card onClick={() => router.visit("/accounting/profit-loss")} className="cursor-pointer hover:bg-slate-50 transition-colors">
          <CardHeader className="p-5 pb-2">
            <CardTitle className="text-sm font-semibold text-slate-900">Profit & Loss</CardTitle>
          </CardHeader>
          <CardContent className="p-5 pt-0">
            <p className="text-sm text-slate-600">Revenue, expenses, and net profit for the period.</p>
          </CardContent>
        </Card>
        <Card onClick={() => router.visit("/accounting/balance-sheet")} className="cursor-pointer hover:bg-slate-50 transition-colors">
          <CardHeader className="p-5 pb-2">
            <CardTitle className="text-sm font-semibold text-slate-900">Balance Sheet</CardTitle>
          </CardHeader>
          <CardContent className="p-5 pt-0">
            <p className="text-sm text-slate-600">Assets, liabilities, and equity position.</p>
          </CardContent>
        </Card>
      </div>

      <DataTable
        title="Journal Entries"
        columns={[
          { key: "reference", label: "Reference" },
          { key: "document_date", label: "Date" },
          { key: "status", label: "Status" },
          { key: "description", label: "Description" },
        ]}
        data={journalEntries}
        meta={meta}
        createUrl="/accounting/journal-entries/create"
        rowActions={[
          { label: "View", icon: <Eye size={14} />, variant: "ghost" },
          { label: "Edit", icon: <Edit size={14} />, variant: "outline" },
        ]}
      />
    </AppLayout>
  );
}
