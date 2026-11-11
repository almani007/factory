/**
 * Brixel FactoryOS — Bank Reconciliation
 * Reconcile bank statements with book balances.
 */

import * as React from "react";
import { router, usePage } from "@inertiajs/react";
import { AppLayout, PageHeader } from "@/components/layout";
import { DataTable } from "@/components/DataTable";
import { Plus, CheckCircle2, Trash2, Filter, BookOpen, AlertTriangle, Download } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Card, CardContent } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";

interface Reconciliation {
  id: number;
  account_id: number;
  reference: string;
  account_name: string;
  statement_date: string;
  book_balance: number;
  bank_balance: number;
  difference: number;
  status: string;
  status_label: string;
  reconciled_date: string;
}

const STATUS_BADGE: Record<string, string> = {
  reconciled: "bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-200",
  pending: "bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-200",
  open: "bg-slate-100 text-slate-500 ring-1 ring-inset ring-slate-200",
};

const STATUSES = [
  { value: "", label: "All" },
  { value: "open", label: "Open" },
  { value: "pending", label: "Pending" },
  { value: "reconciled", label: "Reconciled" },
];

export default function BankReconciliation() {
  const { props: data } = usePage() as any;
  const reconciliations: Reconciliation[] = data?.reconciliations?.data || [];
  const meta = data?.reconciliations?.meta || {};
  const summary = data?.reconciliation_summary || {};
  const filters = data?.filters || {};
  const flashError = data?.errors?.reconciliation;

  const [status, setStatus] = React.useState(filters.status || "");
  const [from, setFrom] = React.useState(filters.from || "");
  const [to, setTo] = React.useState(filters.to || "");
  const currentQ = filters.q || "";

  const apply = (next?: { status?: string; from?: string; to?: string }) => {
    const s = next?.status !== undefined ? next.status : status;
    const f = next?.from !== undefined ? next.from : from;
    const t = next?.to !== undefined ? next.to : to;
    router.get("/accounting/bank-reconciliation", {
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
    router.get("/accounting/bank-reconciliation", {}, { preserveState: true, replace: true });
  };

  const reconcile = (row: Reconciliation) => {
    if (!window.confirm(`Mark reconciliation ${row.reference} as reconciled?`)) return;
    router.post(`/accounting/bank-reconciliation/${row.id}/reconcile`, {}, { preserveScroll: true });
  };

  const remove = (row: Reconciliation) => {
    if (!window.confirm(`Delete reconciliation ${row.reference}? This cannot be undone.`)) return;
    router.delete(`/accounting/bank-reconciliation/${row.id}`, { preserveScroll: true });
  };

  return (
    <AppLayout title="Bank Reconciliation">
      <PageHeader
        title="Bank Reconciliation"
        subtitle="Reconcile bank statements with book balances"
        breadcrumbs={[{ label: "Accounting", href: "/accounting" }, { label: "Bank Reconciliation" }]}
      >
        <div className="flex flex-wrap items-center gap-2">
          <Button onClick={() => router.visit("/accounting/bank-reconciliation/create")}>
            <Plus size={16} /> New Reconciliation
          </Button>
          <a
            href={`/accounting/bank-reconciliation/export?${new URLSearchParams({
              ...(currentQ ? { q: currentQ } : {}),
              ...(status ? { status } : {}),
              ...(from ? { from } : {}),
              ...(to ? { to } : {}),
            }).toString()}`}
            className="inline-flex h-9 items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50"
          >
            <Download size={14} /> Export CSV
          </a>
        </div>
      </PageHeader>

      {flashError && (
        <div className="mb-6 flex items-start gap-2 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
          <AlertTriangle size={18} className="mt-0.5 shrink-0" /> {flashError}
        </div>
      )}

      <div className="mb-6 grid grid-cols-2 gap-4 lg:grid-cols-5">
        <Card><CardContent className="p-5"><p className="text-xs font-medium text-slate-500">Reconciliations</p><p className="mt-1 text-2xl font-semibold tabular-nums text-slate-900">{summary.total ?? 0}</p></CardContent></Card>
        <Card><CardContent className="p-5"><p className="text-xs font-medium text-slate-500">Reconciled</p><p className="mt-1 text-2xl font-semibold tabular-nums text-emerald-700">{summary.reconciled ?? 0}</p></CardContent></Card>
        <Card><CardContent className="p-5"><p className="text-xs font-medium text-slate-500">Pending</p><p className="mt-1 text-2xl font-semibold tabular-nums text-amber-700">{summary.pending ?? 0}</p></CardContent></Card>
        <Card><CardContent className="p-5"><p className="text-xs font-medium text-slate-500">Bank Total</p><p className="mt-1 text-lg font-semibold tabular-nums text-slate-900">{summary.bank_total ?? "Rs. 0"}</p></CardContent></Card>
        <Card><CardContent className="p-5"><p className="text-xs font-medium text-slate-500">Book Total</p><p className="mt-1 text-lg font-semibold tabular-nums text-slate-900">{summary.book_total ?? "Rs. 0"}</p></CardContent></Card>
      </div>

      <div className="mb-6 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
        <div className="mb-3 flex flex-wrap items-center gap-2">
          {STATUSES.map((s) => (
            <button
              key={s.value || "all"}
              type="button"
              onClick={() => { setStatus(s.value); apply({ status: s.value }); }}
              className={`rounded-full px-3 py-1.5 text-xs font-medium ring-1 ring-inset transition-colors ${status === s.value ? "bg-factory-600 text-white ring-factory-600" : "bg-white text-slate-600 ring-slate-200 hover:bg-slate-50"}`}
            >
              {s.label}
            </button>
          ))}
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
        title="Bank Reconciliations"
        columns={[
          { key: "reference", label: "Reference" },
          { key: "account_name", label: "Bank Account" },
          { key: "statement_date", label: "Statement Date" },
          { key: "book_balance", label: "Book Balance", align: "right", format: (v: number) => `Rs. ${Number(v).toLocaleString(undefined, { maximumFractionDigits: 2 })}` },
          { key: "bank_balance", label: "Bank Balance", align: "right", format: (v: number) => `Rs. ${Number(v).toLocaleString(undefined, { maximumFractionDigits: 2 })}` },
          { key: "difference", label: "Difference", align: "right", format: (v: number) => <span className={`tabular-nums font-medium ${Math.abs(Number(v)) < 0.01 ? "text-emerald-700" : "text-rose-700"}`}>Rs. {Number(v || 0).toLocaleString(undefined, { maximumFractionDigits: 2 })}</span> },
          { key: "status", label: "Status", format: (value: string) => (
            <Badge className={STATUS_BADGE[value] || "bg-slate-100 text-slate-600"}>
              {value.replace(/_/g, " ")}
            </Badge>
          )},
          { key: "reconciled_date", label: "Reconciled On" },
        ]}
        data={reconciliations}
        meta={meta}
        createUrl="/accounting/bank-reconciliation/create"
        rowActions={[
          { label: "Account Ledger", icon: <BookOpen size={14} />, variant: "ghost", onClick: (row: any) => router.visit(`/accounting/chart-of-accounts/${row.account_id}/ledger`) },
          { label: "Reconcile", icon: <CheckCircle2 size={14} />, variant: "ghost", visible: (row: any) => row.status !== "reconciled", onClick: (row: any) => reconcile(row) },
          { label: "Delete", icon: <Trash2 size={14} />, variant: "ghost", onClick: (row: any) => remove(row) },
        ]}
      />
    </AppLayout>
  );
}
