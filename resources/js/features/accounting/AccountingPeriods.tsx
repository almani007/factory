/**
 * Brixel FactoryOS — Accounting Periods
 * Open/close fiscal periods and monitor posting activity per period.
 */

import * as React from "react";
import { Link, router, useForm, usePage } from "@inertiajs/react";
import { AppLayout } from "@/components/layout";
import { PageHeader } from "@/components/layout";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";
import { Calendar, Lock, Unlock, AlertTriangle, ArrowLeft } from "lucide-react";

function fmt(n: any) {
  return Number(n || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

export default function AccountingPeriods() {
  const { props } = usePage() as any;
  const periods: any[] = props?.periods || [];
  const years: any[] = props?.financial_years || [];
  const summary = props?.summary || {};
  const flashError = props?.errors?.period;

  const { data, setData, post, processing, errors, reset } = useForm({
    name: "",
    financial_year_id: years.length > 0 ? String(years[0].id) : "",
    start_date: "",
    end_date: "",
  });

  const submit = (e: React.FormEvent) => {
    e.preventDefault();
    post("/accounting/periods", { preserveScroll: true, onSuccess: () => reset("name", "start_date", "end_date") });
  };

  const toggle = (period: any) => {
    const closing = period.status === "open";
    const message = closing
      ? `Close period “${period.name}”? No journal entries can be posted into it afterwards.`
      : `Reopen period “${period.name}”?`;
    if (!window.confirm(message)) return;
    router.post(`/accounting/periods/${period.id}/toggle`, {}, { preserveScroll: true });
  };

  return (
    <AppLayout title="Accounting Periods">
      <PageHeader
        title="Accounting Periods"
        subtitle="Control which fiscal periods accept journal entries"
        breadcrumbs={[{ label: "Accounting", href: "/accounting" }, { label: "Accounting Periods" }]}
      >
        <Link href="/accounting" className="inline-flex h-9 items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50"><ArrowLeft size={16} /> Back to Accounting</Link>
      </PageHeader>

      {flashError && (
        <div className="mb-6 flex items-start gap-2 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
          <AlertTriangle size={18} className="mt-0.5 shrink-0" /> {flashError}
        </div>
      )}

      <div className="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <Card><CardContent className="p-5"><p className="text-xs font-medium text-slate-500">Total Periods</p><p className="mt-1 text-2xl font-semibold tabular-nums text-slate-900">{summary.total ?? 0}</p></CardContent></Card>
        <Card><CardContent className="p-5"><p className="text-xs font-medium text-slate-500">Open</p><p className="mt-1 text-2xl font-semibold tabular-nums text-emerald-700">{summary.open ?? 0}</p></CardContent></Card>
        <Card><CardContent className="p-5"><p className="text-xs font-medium text-slate-500">Closed</p><p className="mt-1 text-2xl font-semibold tabular-nums text-slate-900">{summary.closed ?? 0}</p></CardContent></Card>
        <Card><CardContent className="p-5"><p className="text-xs font-medium text-slate-500">Unposted Entries</p><p className={`mt-1 text-2xl font-semibold tabular-nums ${Number(summary.draft_entries) > 0 ? "text-amber-700" : "text-slate-900"}`}>{summary.draft_entries ?? 0}</p></CardContent></Card>
      </div>

      <div className="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <Card className="xl:col-span-1 h-fit">
          <CardHeader><CardTitle className="text-base font-semibold text-slate-900 flex items-center gap-2"><Calendar size={16} /> New Period</CardTitle></CardHeader>
          <CardContent>
            <form onSubmit={submit} className="space-y-4">
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Period Name</label>
                <input value={data.name} onChange={(e) => setData("name", e.target.value)} placeholder="e.g. July 2026" className="h-10 w-full rounded-lg border border-slate-200 px-3 text-sm" />
                {errors.name && <p className="text-xs text-rose-600">{errors.name}</p>}
              </div>
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Financial Year</label>
                <select value={data.financial_year_id} onChange={(e) => setData("financial_year_id", e.target.value)} className="h-10 w-full rounded-lg border border-slate-200 px-3 text-sm">
                  <option value="">Auto-create from start date</option>
                  {years.map((y) => (
                    <option key={y.id} value={String(y.id)}>{y.name} ({String(y.start_date).slice(0, 10)} → {String(y.end_date).slice(0, 10)})</option>
                  ))}
                </select>
                {errors.financial_year_id && <p className="text-xs text-rose-600">{errors.financial_year_id}</p>}
              </div>
              <div className="grid grid-cols-2 gap-3">
                <div className="space-y-1">
                  <label className="text-sm font-medium text-slate-700">Start Date</label>
                  <input type="date" value={data.start_date} onChange={(e) => setData("start_date", e.target.value)} className="h-10 w-full rounded-lg border border-slate-200 px-3 text-sm" />
                  {errors.start_date && <p className="text-xs text-rose-600">{errors.start_date}</p>}
                </div>
                <div className="space-y-1">
                  <label className="text-sm font-medium text-slate-700">End Date</label>
                  <input type="date" value={data.end_date} onChange={(e) => setData("end_date", e.target.value)} className="h-10 w-full rounded-lg border border-slate-200 px-3 text-sm" />
                  {errors.end_date && <p className="text-xs text-rose-600">{errors.end_date}</p>}
                </div>
              </div>
              <Button type="submit" disabled={processing}>{processing ? "Creating…" : "Create Period"}</Button>
            </form>
          </CardContent>
        </Card>

        <div className="xl:col-span-2 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
          <div className="flex items-center justify-between border-b border-slate-200/60 px-5 py-4">
            <div>
              <h2 className="text-base font-semibold tracking-tight text-slate-900">Periods</h2>
              <p className="mt-0.5 text-xs text-slate-500">Closing a period blocks new postings until it is reopened.</p>
            </div>
            <span className="text-xs text-slate-500">{periods.length} period(s)</span>
          </div>
          <div className="overflow-x-auto">
            <table className="min-w-full divide-y divide-slate-100 text-sm">
              <thead className="bg-slate-50">
                <tr>
                  {["Period", "Financial Year", "Dates", "Entries", "Posted Debit", "Status", ""].map((h) => (
                    <th key={h} className={`px-4 py-2.5 text-xs font-semibold uppercase tracking-wider text-slate-500 ${["Posted Debit"].includes(h) ? "text-right" : "text-left"}`}>{h}</th>
                  ))}
                </tr>
              </thead>
              <tbody className="divide-y divide-slate-100">
                {periods.length === 0 ? (
                  <tr><td colSpan={7} className="px-4 py-10 text-center text-sm text-slate-500">No accounting periods yet. Create one to start closing books.</td></tr>
                ) : (
                  periods.map((p) => (
                    <tr key={p.id} className="hover:bg-slate-50">
                      <td className="px-4 py-3 font-medium text-slate-800">{p.name}</td>
                      <td className="px-4 py-3 text-slate-600">{p.financial_year_name || "—"}</td>
                      <td className="px-4 py-3 whitespace-nowrap text-slate-600">{p.start_date} → {p.end_date}</td>
                      <td className="px-4 py-3">
                        <span className="text-slate-700">{p.entry_count}</span>
                        {Number(p.draft_count) > 0 && <span className="ml-1.5 rounded-full bg-amber-50 px-1.5 py-0.5 text-[10px] font-semibold text-amber-700 ring-1 ring-inset ring-amber-200">{p.draft_count} unposted</span>}
                      </td>
                      <td className="px-4 py-3 text-right tabular-nums text-slate-800">{fmt(p.debit_total)}</td>
                      <td className="px-4 py-3">
                        <Badge className={p.status === "open" ? "bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-200" : "bg-slate-100 text-slate-600 ring-1 ring-inset ring-slate-200"}>{p.status}</Badge>
                      </td>
                      <td className="px-4 py-3 text-right">
                        <button
                          type="button"
                          onClick={() => toggle(p)}
                          className="inline-flex items-center gap-1 rounded-md border border-slate-200 bg-white px-2.5 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50"
                        >
                          {p.status === "open" ? <><Lock size={12} /> Close</> : <><Unlock size={12} /> Reopen</>}
                        </button>
                      </td>
                    </tr>
                  ))
                )}
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </AppLayout>
  );
}
