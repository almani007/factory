/**
 * Brixel FactoryOS — Chart of Accounts
 * Account master data with balances and account-type grouping.
 */

import { Head, Link, router, usePage } from "@inertiajs/react";
import { AppLayout } from "@/components/layout";
import { PageHeader } from "@/components/layout";
import { DataTable } from "@/components/DataTable";
import { Plus, Eye, Edit } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";

interface Account {
  id: number;
  code: string;
  name: string;
  type: string;
  allow_direct_posting: boolean;
  is_cash_account: boolean;
  is_bank_account: boolean;
}

const TYPE_LABELS: Record<string, string> = {
  asset: "Asset",
  liability: "Liability",
  equity: "Equity",
  revenue: "Revenue",
  expense: "Expense",
};

const TYPE_BADGE: Record<string, string> = {
  asset: "bg-blue-50 text-blue-700 ring-1 ring-inset ring-blue-200",
  liability: "bg-indigo-50 text-indigo-700 ring-1 ring-inset ring-indigo-200",
  equity: "bg-purple-50 text-purple-700 ring-1 ring-inset ring-purple-200",
  revenue: "bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-200",
  expense: "bg-rose-50 text-rose-700 ring-1 ring-inset ring-rose-200",
};

export default function ChartOfAccounts() {
  const { props: data } = usePage() as any;
  const accounts: Account[] = data?.accounts?.data || [];
  const meta = data?.accounts?.meta || {};
  const typeCounts = data?.type_counts || {};

  const totalAccounts = Object.values(typeCounts).reduce((sum: number, v: any) => sum + (Number(v) || 0), 0);

  return (
    <AppLayout title="Chart of Accounts">
      <PageHeader
        title="Chart of Accounts"
        subtitle="Account master data, balances, and account-type grouping"
        breadcrumbs={[{ label: "Accounting", href: "/accounting" }, { label: "Chart of Accounts" }]}
      >
        <Button onClick={() => router.visit("/accounting/chart-of-accounts/create")}>
          <Plus size={16} /> New Account
        </Button>
      </PageHeader>

      <div className="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
        <Card>
          <CardHeader className="p-5 pb-2">
            <CardTitle className="text-sm font-medium text-slate-500">Total Accounts</CardTitle>
          </CardHeader>
          <CardContent className="p-5 pt-0">
            <p className="text-2xl font-semibold tracking-tight text-slate-900 tabular-nums">{totalAccounts}</p>
            <p className="text-xs text-slate-500 mt-1">Chart of accounts</p>
          </CardContent>
        </Card>
        <Card>
          <CardHeader className="p-5 pb-2">
            <CardTitle className="text-sm font-medium text-slate-500">Assets</CardTitle>
          </CardHeader>
          <CardContent className="p-5 pt-0">
            <p className="text-2xl font-semibold tracking-tight text-slate-900 tabular-nums">{typeCounts.asset || 0}</p>
            <p className="text-xs text-slate-500 mt-1">Resource accounts</p>
          </CardContent>
        </Card>
        <Card>
          <CardHeader className="p-5 pb-2">
            <CardTitle className="text-sm font-medium text-slate-500">Liabilities & Equity</CardTitle>
          </CardHeader>
          <CardContent className="p-5 pt-0">
            <p className="text-2xl font-semibold tracking-tight text-slate-900 tabular-nums">
              {(typeCounts.liability || 0) + (typeCounts.equity || 0)}
            </p>
            <p className="text-xs text-slate-500 mt-1">Claims accounts</p>
          </CardContent>
        </Card>
        <Card>
          <CardHeader className="p-5 pb-2">
            <CardTitle className="text-sm font-medium text-slate-500">Revenue & Expense</CardTitle>
          </CardHeader>
          <CardContent className="p-5 pt-0">
            <p className="text-2xl font-semibold tracking-tight text-slate-900 tabular-nums">
              {(typeCounts.revenue || 0) + (typeCounts.expense || 0)}
            </p>
            <p className="text-xs text-slate-500 mt-1">Income statement accounts</p>
          </CardContent>
        </Card>
      </div>

      <DataTable
        title="Accounts"
        columns={[
          { key: "code", label: "Account Code" },
          { key: "name", label: "Account Name" },
          { key: "type", label: "Type", format: (value: string) => (
            <Badge className={TYPE_BADGE[value] || "bg-slate-100 text-slate-600"}>
              {TYPE_LABELS[value] || value}
            </Badge>
          )},
          { key: "allow_direct_posting", label: "Direct Posting", format: (value: boolean) => (
            <span className={value ? "text-emerald-600" : "text-slate-400"}>
              {value ? "Yes" : "No"}
            </span>
          )},
          { key: "is_cash_account", label: "Cash", format: (value: boolean) => (value ? <span className="text-sky-600">Yes</span> : <span className="text-slate-400">—</span>) },
          { key: "is_bank_account", label: "Bank", format: (value: boolean) => (value ? <span className="text-sky-600">Yes</span> : <span className="text-slate-400">—</span>) },
        ]}
        data={accounts}
        meta={meta}
        createUrl="/accounting/chart-of-accounts?create=1"
        rowActions={[
          { label: "View", icon: <Eye size={14} />, variant: "ghost" },
          { label: "Edit", icon: <Edit size={14} />, variant: "outline" },
        ]}
      />
    </AppLayout>
  );
}
