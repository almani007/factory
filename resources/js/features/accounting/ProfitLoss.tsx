/**
 * Brixel FactoryOS — Profit & Loss
 * Revenue, expenses, and net profit summary.
 */

import { Head, Link, usePage } from "@inertiajs/react";
import { AppLayout } from "@/components/layout";
import { PageHeader } from "@/components/layout";
import { DataTable } from "@/components/DataTable";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { KPICard } from "@/components/KPICard";
import { ArrowRight, Eye } from "lucide-react";

interface PLRow {
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
        <Link
          href="/accounting/trial-balance"
          className="inline-flex h-9 items-center gap-1.5 rounded-lg bg-factory-600 px-3 text-sm font-medium text-white shadow-sm transition-colors hover:bg-factory-700"
        >
          <ArrowRight size={16} /> View Trial Balance
        </Link>
      </PageHeader>

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
                { label: "View", icon: <Eye size={14} />, variant: "ghost" },
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
                { label: "View", icon: <Eye size={14} />, variant: "ghost" },
              ]}
            />
          </CardContent>
        </Card>
      </div>
    </AppLayout>
  );
}
