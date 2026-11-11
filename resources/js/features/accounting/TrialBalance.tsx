/**
 * Brixel FactoryOS — Trial Balance
 * Debit/credit balances by account with balance check.
 */

import { Head, Link, usePage } from "@inertiajs/react";
import { AppLayout } from "@/components/layout";
import { PageHeader } from "@/components/layout";
import { DataTable } from "@/components/DataTable";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { KPICard } from "@/components/KPICard";
import { ArrowRight, Eye } from "lucide-react";

interface TrialBalanceRow {
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

  return (
    <AppLayout title="Trial Balance">
      <PageHeader
        title="Trial Balance"
        subtitle="Debit and credit balances by account — verifies ledger integrity"
        breadcrumbs={[{ label: "Accounting", href: "/accounting" }, { label: "Trial Balance" }]}
      >
        <Link
          href="/accounting/journal-entries"
          className="inline-flex h-9 items-center gap-1.5 rounded-lg bg-factory-600 px-3 text-sm font-medium text-white shadow-sm transition-colors hover:bg-factory-700"
        >
          <ArrowRight size={16} /> View Journal Entries
        </Link>
      </PageHeader>

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
          { label: "View", icon: <Eye size={14} />, variant: "ghost" },
        ]}
      />
    </AppLayout>
  );
}
