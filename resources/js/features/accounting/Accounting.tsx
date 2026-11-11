/**
 * Brixel FactoryOS — Accounting & Finance
 * Double-entry ledger, journal entries, and financial reports.
 */

import * as React from "react";
import { Link, router, usePage } from "@inertiajs/react";
import { AppLayout } from "@/components/layout";
import { PageHeader } from "@/components/layout";
import { DataTable } from "@/components/DataTable";
import { Plus, Eye, Filter, Download, CheckCircle2, RotateCcw, Trash2 } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";

const STATUSES = [
  { value: "", label: "All" },
  { value: "draft", label: "Draft" },
  { value: "submitted", label: "Submitted" },
  { value: "posted", label: "Posted" },
  { value: "reversed", label: "Reversed" },
];

export default function Accounting() {
  const { props: data } = usePage() as any;
  const journalEntries = data?.journal_entries?.data || [];
  const meta = data?.journal_entries?.meta || {};
  const summary = data?.finance_summary || {
    trial_balance: "Rs. 0",
    receivable: "Rs. 0",
    payable: "Rs. 0",
  };
  const filters = data?.filters || {};
  const counts = data?.journal_entry_counts || {};

  const [status, setStatus] = React.useState(filters.status || "");
  const [from, setFrom] = React.useState(filters.from || "");
  const [to, setTo] = React.useState(filters.to || "");

  const currentQ = filters.q || "";

  const apply = (next?: { status?: string; from?: string; to?: string }) => {
    const s = next?.status !== undefined ? next.status : status;
    const f = next?.from !== undefined ? next.from : from;
    const t = next?.to !== undefined ? next.to : to;
    router.get("/accounting", {
      ...(currentQ ? { q: currentQ } : {}),
      ...(s ? { status: s } : {}),
      ...(f ? { from: f } : {}),
      ...(t ? { to: t } : {}),
    }, { preserveState: true, replace: true });
  };

  const reset = () => {
    setStatus("");
    setFrom("");
    setTo("");
    router.get("/accounting", {}, { preserveState: true, replace: true });
  };

  const exportUrl = `/accounting/journal-entries/export?${new URLSearchParams({
    ...(currentQ ? { q: currentQ } : {}),
    ...(status ? { status } : {}),
    ...(from ? { from } : {}),
    ...(to ? { to } : {}),
  }).toString()}`;

  return (
    <AppLayout title="Accounting & Finance">
      <PageHeader
        title="Accounting & Finance"
        subtitle="Double-entry ledger, journal entries, and financial reports"
        breadcrumbs={[{ label: "Accounting", href: "/accounting" }]}
      >
        <div className="flex flex-wrap items-center gap-2">
          <Button onClick={() => router.visit("/accounting/journal-entries/create")}>
            <Plus size={16} /> New Journal Entry
          </Button>
          <a href={exportUrl} className="inline-flex h-9 items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50"><Download size={14} /> Export CSV</a>
          <Link
            href="/accounting/chart-of-accounts"
            className="inline-flex h-9 items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 text-sm font-medium text-slate-700 shadow-sm transition-colors hover:bg-slate-50"
          >
            Chart of Accounts
          </Link>
          <Link
            href="/accounting/periods"
            className="inline-flex h-9 items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 text-sm font-medium text-slate-700 shadow-sm transition-colors hover:bg-slate-50"
          >
            Periods
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
      <div className="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
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
        <Card onClick={() => router.visit("/accounting/cash-flow")} className="cursor-pointer hover:bg-slate-50 transition-colors">
          <CardHeader className="p-5 pb-2">
            <CardTitle className="text-sm font-semibold text-slate-900">Cash Flow</CardTitle>
          </CardHeader>
          <CardContent className="p-5 pt-0">
            <p className="text-sm text-slate-600">Operating, investing, and financing cash movements.</p>
          </CardContent>
        </Card>
      </div>

      {/* Journal entry filters */}
      <div className="mb-6 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
        <div className="mb-3 flex flex-wrap items-center gap-2">
          {STATUSES.map((s) => {
            const active = status === s.value;
            const count = s.value === "" ? counts.total : counts[s.value];
            return (
              <button
                key={s.value || "all"}
                type="button"
                onClick={() => { setStatus(s.value); apply({ status: s.value }); }}
                className={`inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-medium ring-1 ring-inset transition-colors ${
                  active
                    ? "bg-factory-600 text-white ring-factory-600"
                    : "bg-white text-slate-600 ring-slate-200 hover:bg-slate-50"
                }`}
              >
                {s.label}
                <span className={`rounded-full px-1.5 text-[10px] tabular-nums ${active ? "bg-white/20" : "bg-slate-100 text-slate-500"}`}>{count ?? 0}</span>
              </button>
            );
          })}
        </div>
        <div className="flex flex-col gap-3 lg:flex-row lg:items-end">
          <div className="w-full lg:w-44">
            <label className="mb-1 block text-xs font-semibold uppercase tracking-wider text-slate-500">From Date</label>
            <input type="date" value={from} onChange={(e) => setFrom(e.target.value)} className="h-10 w-full rounded-lg border border-slate-200 px-3 text-sm" />
          </div>
          <div className="w-full lg:w-44">
            <label className="mb-1 block text-xs font-semibold uppercase tracking-wider text-slate-500">To Date</label>
            <input type="date" value={to} onChange={(e) => setTo(e.target.value)} className="h-10 w-full rounded-lg border border-slate-200 px-3 text-sm" />
          </div>
          <div className="flex gap-2">
            <Button variant="outline" onClick={() => apply()}><Filter size={14} /> Apply</Button>
            <Button variant="ghost" onClick={reset}>Reset</Button>
          </div>
        </div>
      </div>

      <DataTable
        title="Journal Entries"
        columns={[
          { key: "reference", label: "Reference" },
          { key: "document_date", label: "Date" },
          { key: "status", label: "Status" },
          { key: "debit_total", label: "Debit", align: "right", format: (v: number) => <span className="tabular-nums">{Number(v || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</span> },
          { key: "credit_total", label: "Credit", align: "right", format: (v: number) => <span className="tabular-nums">{Number(v || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</span> },
          { key: "description", label: "Description" },
        ]}
        data={journalEntries}
        meta={meta}
        createUrl="/accounting/journal-entries/create"
        rowActions={[
          { label: "View", icon: <Eye size={14} />, variant: "ghost", onClick: (row: any) => router.visit(`/accounting/journal-entries/${row.id}`) },
          { label: "Post", icon: <CheckCircle2 size={14} />, variant: "ghost", visible: (row: any) => ["draft", "submitted"].includes(row.status), onClick: (row: any) => { if (window.confirm(`Post journal entry ${row.reference}?`)) router.post(`/accounting/journal-entries/${row.id}/post`, {}, { preserveScroll: true }); } },
          { label: "Reverse", icon: <RotateCcw size={14} />, variant: "ghost", visible: (row: any) => row.status === "posted", onClick: (row: any) => { if (window.confirm(`Post a reversing entry for ${row.reference}?`)) router.post(`/accounting/journal-entries/${row.id}/reverse`, {}, { preserveScroll: true }); } },
          { label: "Delete", icon: <Trash2 size={14} />, variant: "ghost", visible: (row: any) => row.status === "draft", onClick: (row: any) => { if (window.confirm(`Delete draft journal entry ${row.reference}?`)) router.delete(`/accounting/journal-entries/${row.id}`); } },
        ]}
      />
    </AppLayout>
  );
}
