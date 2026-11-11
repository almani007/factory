/**
 * Brixel FactoryOS — Trial Balance
 * Debit/credit balances by account with balance check.
 */

import { Head, Link, router, usePage } from "@inertiajs/react";
import { AppLayout } from "@/components/layout";
import { PageHeader } from "@/components/layout";
import { DataTable } from "@/components/DataTable";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { KPICard } from "@/components/KPICard";
import { Button } from "@/components/ui/button";
import { ArrowRight, Eye, Download, Filter } from "lucide-react";
import * as React from "react";

interface TrialBalanceRow {
  account_id: number;
  account_code: string;
  account_name: string;
  account_type: string;
  debit_balance: number;
  credit_balance: number;
}

export default function TrialBalance() {
  const { props: data } = usePage() as any;
  const accounts: TrialBalanceRow[] = data?.accounts || [];
  const totalDebit: number = data?.total_debit || 0;
  const totalCredit: number = data?.total_credit || 0;
  const isBalanced: boolean = data?.is_balanced ?? false;
  const filters = data?.filters || {};
  const [asOf, setAsOf] = React.useState(filters.as_of || "");

  const apply = (value?: string) => {
    const next = value !== undefined ? value : asOf;
    router.get("/accounting/trial-balance", next ? { as_of: next } : {}, { preserveState: true, replace: true });
  };
  const exportUrl = `/accounting/trial-balance/export${asOf ? `?as_of=${encodeURIComponent(asOf)}` : ""}`;

  return (
    <AppLayout title="Trial Balance">
      <PageHeader
        title="Trial Balance"
        subtitle="Debit and credit balances by account — verifies ledger integrity"
        breadcrumbs={[{ label: "Accounting", href: "/accounting" }, { label: "Trial Balance" }]}
      >
        <div className="flex items-center gap-2">
          <a href={exportUrl} className="inline-flex h-9 items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50"><Download size={14}/> Export CSV</a>
          <Link
            href="/accounting"
            className="inline-flex h-9 items-center gap-1.5 rounded-lg bg-factory-600 px-3 text-sm font-medium text-white shadow-sm transition-colors hover:bg-factory-700"
          >
            <ArrowRight size={16} /> View Journal Entries
          </Link>
        </div>
      </PageHeader>

      <div className="mb-6 rounded-xl border border-slate-200 bg-white p-4 shadow-sm flex flex-col gap-3 sm:flex-row sm:items-end">
        <div className="w-full sm:w-48">
          <label className="mb-1 block text-xs font-semibold uppercase tracking-wider text-slate-500">As of Date</label>
          <input type="date" value={asOf} onChange={(e) => setAsOf(e.target.value)} className="h-10 w-full rounded-lg border border-slate-200 px-3 text-sm" />
        </div>
        <div className="flex gap-2">
          <Button variant="outline" onClick={() => apply()}><Filter size={14} /> Apply</Button>
          <Button variant="ghost" onClick={() => { setAsOf(""); apply(""); }}>Reset</Button>
        </div>
      </div>

      <div className="mb-6 grid grid-cols-2 gap-4 lg:grid-cols-4">
        <KPICard title="Total Debits" value={`Rs. ${totalDebit.toLocaleString(undefined, { maximumFractionDigits: 2 })}`} change={isBalanced ? "Balanced" : "Out of balance"} trend={isBalanced ? "up" : "down"} />
        <KPICard title="Total Credits" value={`Rs. ${totalCredit.toLocaleString(undefined, { maximumFractionDigits: 2 })}`} change={isBalanced ? "Balanced" : "Out of balance"} trend={isBalanced ? "up" : "down"} />
        <KPICard title="Difference" value={`Rs. ${Math.abs(totalDebit - totalCredit).toLocaleString(undefined, { maximumFractionDigits: 2 })}`} change={isBalanced ? "Zero" : "Needs review"} trend={isBalanced ? "up" : "down"} />
        <KPICard title="Status" value={isBalanced ? "Balanced" : "Out of Balance"} change={isBalanced ? "Ledger in balance" : "Investigate discrepancies"} trend={isBalanced ? "up" : "down"} />
      </div>

      <DataTable
        title="Account Balances"
        columns={[
          { key: "account_code", label: "Account Code" },
          { key: "account_name", label: "Account Name" },
          { key: "account_type", label: "Type" },
          { key: "debit_balance", label: "Debit Balance", align: "right", format: (value: number) => `Rs. ${Number(value).toLocaleString(undefined, { maximumFractionDigits: 2 })}` },
          { key: "credit_balance", label: "Credit Balance", align: "right", format: (value: number) => `Rs. ${Number(value).toLocaleString(undefined, { maximumFractionDigits: 2 })}` },
        ]}
        data={accounts}
        emptyMessage="No trial balance data available. Post journal entries to generate balances."
        rowActions={[
          { label: "View Ledger", icon: <Eye size={14} />, variant: "ghost", onClick: (row: any) => router.visit(`/accounting/chart-of-accounts/${row.account_id}/ledger`) },
        ]}
      />
    </AppLayout>
  );
}
