/**
 * Brixel FactoryOS — Profit & Loss
 * Revenue, expenses, and net profit summary.
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

interface PLRow {
  account_id: number;
  code: string;
  name: string;
  turnover: number;
}

export default function ProfitLoss() {
  const { props: data } = usePage() as any;
  const revenue: PLRow[] = data?.revenue || [];
  const expenses: PLRow[] = data?.expenses || [];
  const totalRevenue: number = data?.total_revenue || 0;
  const totalExpenses: number = data?.total_expenses || 0;
  const netProfit: number = data?.net_profit || 0;
  const filters = data?.filters || {};
  const [from, setFrom] = React.useState(filters.from || "");
  const [to, setTo] = React.useState(filters.to || "");

  const apply = (next?: { from?: string; to?: string }) => {
    const f = next?.from !== undefined ? next.from : from;
    const t = next?.to !== undefined ? next.to : to;
    router.get("/accounting/profit-loss", {
      ...(f ? { from: f } : {}),
      ...(t ? { to: t } : {}),
    }, { preserveState: true, replace: true });
  };
  const exportUrl = `/accounting/profit-loss/export?${new URLSearchParams({
    ...(from ? { from } : {}),
    ...(to ? { to } : {}),
  }).toString()}`;

  const revenueColumns = [
    { key: "code", label: "Account Code" },
    { key: "name", label: "Revenue Account" },
    { key: "turnover", label: "Turnover", align: "right", format: (value: number) => `Rs. ${Number(value).toLocaleString(undefined, { maximumFractionDigits: 2 })}` },
  ];

  const expenseColumns = [
    { key: "code", label: "Account Code" },
    { key: "name", label: "Expense Account" },
    { key: "turnover", label: "Turnover", align: "right", format: (value: number) => `Rs. ${Number(value).toLocaleString(undefined, { maximumFractionDigits: 2 })}` },
  ];

  return (
    <AppLayout title="Profit & Loss">
      <PageHeader
        title="Profit & Loss"
        subtitle="Revenue, expenses, and net profit for the period"
        breadcrumbs={[{ label: "Accounting", href: "/accounting" }, { label: "Profit & Loss" }]}
      >
        <div className="flex items-center gap-2">
          <a href={exportUrl} className="inline-flex h-9 items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50"><Download size={14}/> Export CSV</a>
          <Link
            href="/accounting/trial-balance"
            className="inline-flex h-9 items-center gap-1.5 rounded-lg bg-factory-600 px-3 text-sm font-medium text-white shadow-sm transition-colors hover:bg-factory-700"
          >
            <ArrowRight size={16} /> View Trial Balance
          </Link>
        </div>
      </PageHeader>

      <div className="mb-6 rounded-xl border border-slate-200 bg-white p-4 shadow-sm flex flex-col gap-3 sm:flex-row sm:items-end">
        <div className="w-full sm:w-44">
          <label className="mb-1 block text-xs font-semibold uppercase tracking-wider text-slate-500">From</label>
          <input type="date" value={from} onChange={(e) => setFrom(e.target.value)} className="h-10 w-full rounded-lg border border-slate-200 px-3 text-sm" />
        </div>
        <div className="w-full sm:w-44">
          <label className="mb-1 block text-xs font-semibold uppercase tracking-wider text-slate-500">To</label>
          <input type="date" value={to} onChange={(e) => setTo(e.target.value)} className="h-10 w-full rounded-lg border border-slate-200 px-3 text-sm" />
        </div>
        <div className="flex gap-2">
          <Button variant="outline" onClick={() => apply()}><Filter size={14} /> Apply</Button>
          <Button variant="ghost" onClick={() => { setFrom(""); setTo(""); apply({ from: "", to: "" }); }}>Reset</Button>
        </div>
      </div>

      <div className="mb-6 grid grid-cols-2 gap-4 lg:grid-cols-4">
        <KPICard title="Total Revenue" value={`Rs. ${totalRevenue.toLocaleString(undefined, { maximumFractionDigits: 2 })}`} change="Income statement" trend="up" />
        <KPICard title="Total Expenses" value={`Rs. ${totalExpenses.toLocaleString(undefined, { maximumFractionDigits: 2 })}`} change="Operating costs" trend="down" />
        <KPICard title="Net Profit" value={`Rs. ${netProfit.toLocaleString(undefined, { maximumFractionDigits: 2 })}`} change={netProfit >= 0 ? "Profit" : "Loss"} trend={netProfit >= 0 ? "up" : "down"} />
        <KPICard title="Margin" value={totalRevenue > 0 ? `${((netProfit / totalRevenue) * 100).toFixed(1)}%` : "0.0%"} change="Net profit ratio" trend="neutral" />
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <Card>
          <CardHeader className="pb-4">
            <CardTitle className="text-base font-semibold text-slate-900">Revenue</CardTitle>
          </CardHeader>
          <CardContent className="p-5 pt-0">
            <DataTable
              title="Revenue Accounts"
              columns={revenueColumns}
              data={revenue}
              emptyMessage="No revenue accounts with activity."
              rowActions={[
                { label: "View Ledger", icon: <Eye size={14} />, variant: "ghost", onClick: (row: any) => router.visit(`/accounting/chart-of-accounts/${row.account_id}/ledger`) },
              ]}
            />
          </CardContent>
        </Card>

        <Card>
          <CardHeader className="pb-4">
            <CardTitle className="text-base font-semibold text-slate-900">Expenses</CardTitle>
          </CardHeader>
          <CardContent className="p-5 pt-0">
            <DataTable
              title="Expense Accounts"
              columns={expenseColumns}
              data={expenses}
              emptyMessage="No expense accounts with activity."
              rowActions={[
                { label: "View Ledger", icon: <Eye size={14} />, variant: "ghost", onClick: (row: any) => router.visit(`/accounting/chart-of-accounts/${row.account_id}/ledger`) },
              ]}
            />
          </CardContent>
        </Card>
      </div>
    </AppLayout>
  );
}
