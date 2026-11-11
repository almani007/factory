/**
 * Brixel FactoryOS — Balance Sheet
 * Assets, liabilities, and equity with accounting equation check.
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

interface BSRow {
  account_id: number;
  code: string;
  name: string;
  balance: number;
}

export default function BalanceSheet() {
  const { props: data } = usePage() as any;
  const assets: BSRow[] = data?.assets || [];
  const liabilities: BSRow[] = data?.liabilities || [];
  const equity: BSRow[] = data?.equity || [];
  const totalAssets: number = data?.total_assets || 0;
  const totalLiabilities: number = data?.total_liabilities || 0;
  const totalEquity: number = data?.total_equity || 0;
  const totalLAndE = totalLiabilities + totalEquity;
  const isBalanced = Math.abs(totalAssets - totalLAndE) < 0.01;
  const filters = data?.filters || {};
  const [asOf, setAsOf] = React.useState(filters.as_of || "");

  const apply = (value?: string) => {
    const next = value !== undefined ? value : asOf;
    router.get("/accounting/balance-sheet", next ? { as_of: next } : {}, { preserveState: true, replace: true });
  };
  const exportUrl = `/accounting/balance-sheet/export${asOf ? `?as_of=${encodeURIComponent(asOf)}` : ""}`;

  const columns = [
    { key: "code", label: "Account Code" },
    { key: "name", label: "Account Name" },
    { key: "balance", label: "Balance", align: "right", format: (value: number) => `Rs. ${Number(value).toLocaleString(undefined, { maximumFractionDigits: 2 })}` },
  ];

  return (
    <AppLayout title="Balance Sheet">
      <PageHeader
        title="Balance Sheet"
        subtitle="Assets, liabilities, and equity — accounting equation verification"
        breadcrumbs={[{ label: "Accounting", href: "/accounting" }, { label: "Balance Sheet" }]}
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
        <KPICard title="Total Assets" value={`Rs. ${totalAssets.toLocaleString(undefined, { maximumFractionDigits: 2 })}`} change="Resources owned" trend="up" />
        <KPICard title="Total Liabilities" value={`Rs. ${totalLiabilities.toLocaleString(undefined, { maximumFractionDigits: 2 })}`} change="Obligations owed" trend="down" />
        <KPICard title="Total Equity" value={`Rs. ${totalEquity.toLocaleString(undefined, { maximumFractionDigits: 2 })}`} change="Owner's interest" trend="neutral" />
        <KPICard title="Equation" value={isBalanced ? "Balanced" : "Out of balance"} change={isBalanced ? "Assets = Liab. + Equity" : "Investigate"} trend={isBalanced ? "up" : "down"} />
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <Card className="lg:col-span-1">
          <CardHeader className="flex-row items-center justify-between pb-4">
            <CardTitle className="text-base font-semibold text-slate-900">Assets</CardTitle>
            <span className="inline-flex items-center gap-1 text-[11px] font-semibold text-emerald-600">
              {isBalanced ? <span className="h-1.5 w-1.5 rounded-full bg-emerald-500" /> : <span className="h-1.5 w-1.5 rounded-full bg-slate-400" />}
              {isBalanced ? "Balanced" : "Out of balance"}
            </span>
          </CardHeader>
          <CardContent className="p-5 pt-0">
            <DataTable
              title="Asset Accounts"
              columns={columns}
              data={assets}
              emptyMessage="No asset accounts with balances."
              rowActions={[
                { label: "View Ledger", icon: <Eye size={14} />, variant: "ghost", onClick: (row: any) => router.visit(`/accounting/chart-of-accounts/${row.account_id}/ledger`) },
              ]}
            />
          </CardContent>
        </Card>

        <Card className="lg:col-span-2">
          <CardHeader className="flex-row items-center justify-between pb-4">
            <CardTitle className="text-base font-semibold text-slate-900">Liabilities & Equity</CardTitle>
          </CardHeader>
          <CardContent className="p-5 pt-0">
            <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
              <div>
                <p className="mb-2 text-xs font-semibold uppercase tracking-wider text-slate-400">Liabilities</p>
                <DataTable
                  title=""
                  columns={columns}
                  data={liabilities}
                  emptyMessage="No liability accounts with balances."
                  rowActions={[
                    { label: "View Ledger", icon: <Eye size={14} />, variant: "ghost", onClick: (row: any) => router.visit(`/accounting/chart-of-accounts/${row.account_id}/ledger`) },
                  ]}
                />
              </div>
              <div>
                <p className="mb-2 text-xs font-semibold uppercase tracking-wider text-slate-400">Equity</p>
                <DataTable
                  title=""
                  columns={columns}
                  data={equity}
                  emptyMessage="No equity accounts with balances."
                  rowActions={[
                    { label: "View Ledger", icon: <Eye size={14} />, variant: "ghost", onClick: (row: any) => router.visit(`/accounting/chart-of-accounts/${row.account_id}/ledger`) },
                  ]}
                />
              </div>
            </div>
          </CardContent>
        </Card>
      </div>

      <div className="mt-6 border border-slate-200 rounded-xl bg-slate-50 p-4">
        <div className="flex justify-between items-center">
          <span className="text-sm font-medium text-slate-600">Total Liabilities & Equity</span>
          <span className={`text-sm font-semibold tabular-nums ${isBalanced ? "text-emerald-600" : "text-slate-900"}`}>
            Rs. {totalLAndE.toLocaleString(undefined, { maximumFractionDigits: 2 })}
          </span>
        </div>
        <div className="flex justify-between items-center mt-2">
          <span className="text-sm font-medium text-slate-600">Total Assets</span>
          <span className="text-sm font-semibold tabular-nums text-slate-900">
            Rs. {totalAssets.toLocaleString(undefined, { maximumFractionDigits: 2 })}
          </span>
        </div>
        <div className="mt-2 text-xs text-slate-400">
          Accounting equation: {isBalanced ? "Assets = Liabilities + Equity ✓" : "Out of balance — review entries"}
        </div>
      </div>
    </AppLayout>
  );
}
