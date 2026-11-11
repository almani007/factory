/**
 * Brixel FactoryOS — Account Ledger
 */
import * as React from "react";
import { Link, router, usePage } from "@inertiajs/react";
import { AppLayout } from "@/components/layout";
import { PageHeader } from "@/components/layout";
import { Button } from "@/components/ui/button";
import { Card, CardContent } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";
import { ArrowLeft, Download, Filter, BookOpen } from "lucide-react";

function fmt(n: any) {
  return Number(n || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

export default function AccountLedger() {
  const { props } = usePage() as any;
  const account = props?.account || {};
  const lines = props?.lines || [];
  const summary = props?.summary || {};
  const filters = props?.filters || {};

  const [from, setFrom] = React.useState(filters.from || "");
  const [to, setTo] = React.useState(filters.to || "");
  const [status, setStatus] = React.useState(filters.status || "posted");

  const apply = (next?: any) => {
    const p = { from, to, status, ...next };
    router.get(`/accounting/chart-of-accounts/${account.id}/ledger`, {
      from: p.from || undefined,
      to: p.to || undefined,
      status: p.status && p.status !== "posted" ? p.status : undefined,
    }, { preserveState: true, replace: true });
  };

  const exportUrl = `/accounting/chart-of-accounts/${account.id}/ledger/export?from=${encodeURIComponent(from)}&to=${encodeURIComponent(to)}&status=${encodeURIComponent(status)}`;

  return (
    <AppLayout title={`Ledger ${account.code || ""}`}>
      <PageHeader
        title={`Ledger — ${account.code || ""} ${account.name || ""}`.trim()}
        subtitle="Account transaction history with running balance"
        breadcrumbs={[{ label: "Accounting", href: "/accounting" }, { label: "Chart of Accounts", href: "/accounting/chart-of-accounts" }, { label: account.code || "Account", href: `/accounting/chart-of-accounts/${account.id}` }, { label: "Ledger" }]}
      >
        <div className="flex flex-wrap items-center gap-2">
          <Link href={`/accounting/chart-of-accounts/${account.id}`} className="inline-flex h-9 items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50"><ArrowLeft size={16}/> Back</Link>
          <a href={exportUrl} className="inline-flex h-9 items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50"><Download size={14}/> Export CSV</a>
        </div>
      </PageHeader>

      <div className="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
        <Card><CardContent className="p-4"><p className="text-xs font-medium text-slate-500">Opening Balance</p><p className="text-lg font-semibold tabular-nums text-slate-900">{fmt(summary.opening_balance)}</p></CardContent></Card>
        <Card><CardContent className="p-4"><p className="text-xs font-medium text-slate-500">Total Debit</p><p className="text-lg font-semibold tabular-nums text-emerald-700">{fmt(summary.total_debit)}</p></CardContent></Card>
        <Card><CardContent className="p-4"><p className="text-xs font-medium text-slate-500">Total Credit</p><p className="text-lg font-semibold tabular-nums text-rose-700">{fmt(summary.total_credit)}</p></CardContent></Card>
        <Card><CardContent className="p-4"><p className="text-xs font-medium text-slate-500">Closing Balance</p><p className={`text-lg font-semibold tabular-nums ${Number(summary.closing_balance || 0) >= 0 ? "text-slate-900" : "text-rose-700"}`}>{fmt(summary.closing_balance)}</p></CardContent></Card>
        <Card><CardContent className="p-4"><p className="text-xs font-medium text-slate-500">Lines</p><p className="text-lg font-semibold tabular-nums text-slate-900">{summary.line_count ?? 0}</p></CardContent></Card>
      </div>

      <div className="mb-6 rounded-xl border border-slate-200 bg-white p-4 shadow-sm flex flex-col gap-3 lg:flex-row lg:items-end">
        <div className="w-full lg:w-44">
          <label className="mb-1 block text-xs font-semibold uppercase tracking-wider text-slate-500">From</label>
          <input type="date" value={from} onChange={e=>setFrom(e.target.value)} className="h-10 w-full rounded-lg border border-slate-200 px-3 text-sm" />
        </div>
        <div className="w-full lg:w-44">
          <label className="mb-1 block text-xs font-semibold uppercase tracking-wider text-slate-500">To</label>
          <input type="date" value={to} onChange={e=>setTo(e.target.value)} className="h-10 w-full rounded-lg border border-slate-200 px-3 text-sm" />
        </div>
        <div className="w-full lg:w-44">
          <label className="mb-1 block text-xs font-semibold uppercase tracking-wider text-slate-500">Entry Status</label>
          <select value={status} onChange={e=>{setStatus(e.target.value); apply({status:e.target.value});}} className="h-10 w-full rounded-lg border border-slate-200 px-3 text-sm">
            <option value="posted">Posted only</option>
            <option value="all">All statuses</option>
            <option value="draft">Draft</option>
            <option value="submitted">Submitted</option>
            <option value="reversed">Reversed</option>
          </select>
        </div>
        <div className="flex gap-2">
          <Button variant="outline" onClick={()=>apply()}><Filter size={14}/> Apply</Button>
          <Button variant="ghost" onClick={()=>{setFrom("");setTo("");setStatus("posted");router.get(`/accounting/chart-of-accounts/${account.id}/ledger`, {}, {preserveState:true, replace:true});}}>Reset</Button>
        </div>
      </div>

      <div className="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div className="flex items-center justify-between border-b border-slate-200/60 px-5 py-4">
          <h2 className="text-base font-semibold tracking-tight text-slate-900 flex items-center gap-2"><BookOpen size={16}/> Ledger Entries</h2>
          <span className="text-xs text-slate-500">{lines.length} line(s)</span>
        </div>
        <div className="overflow-x-auto">
          <table className="min-w-full divide-y divide-slate-100 text-sm">
            <thead className="bg-slate-50">
              <tr>
                {["Date", "Entry Reference", "Status", "Description", "Debit", "Credit", "Balance"].map((h) => (
                  <th key={h} className={`px-4 py-2.5 text-xs font-semibold uppercase tracking-wider text-slate-500 ${["Debit","Credit","Balance"].includes(h) ? "text-right" : "text-left"}`}>{h}</th>
                ))}
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-100">
              {lines.length === 0 ? (
                <tr><td colSpan={7} className="px-4 py-10 text-center text-sm text-slate-500">No ledger entries for the selected criteria.</td></tr>
              ) : (
                lines.map((r: any) => (
                  <tr key={r.id} className="hover:bg-slate-50">
                    <td className="px-4 py-2 text-slate-600 whitespace-nowrap">{r.posting_date || "—"}</td>
                    <td className="px-4 py-2 font-mono text-xs text-slate-700">{r.entry_reference || "—"}</td>
                    <td className="px-4 py-2">
                      {r.entry_status ? <Badge className={r.entry_status === "posted" ? "bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-200" : "bg-slate-100 text-slate-600 ring-1 ring-inset ring-slate-200"}>{r.entry_status}</Badge> : <span className="text-slate-400">—</span>}
                    </td>
                    <td className="px-4 py-2 text-slate-700 max-w-xs truncate">{r.description || "—"}</td>
                    <td className="px-4 py-2 text-right tabular-nums text-slate-800">{Number(r.debit) ? fmt(r.debit) : <span className="text-slate-400">—</span>}</td>
                    <td className="px-4 py-2 text-right tabular-nums text-slate-800">{Number(r.credit) ? fmt(r.credit) : <span className="text-slate-400">—</span>}</td>
                    <td className={`px-4 py-2 text-right tabular-nums font-medium ${Number(r.balance) < 0 ? "text-rose-700" : "text-slate-900"}`}>{fmt(r.balance)}</td>
                  </tr>
                ))
              )}
            </tbody>
            <tfoot className="bg-slate-50">
              <tr>
                <td colSpan={4} className="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Totals</td>
                <td className="px-4 py-3 text-right tabular-nums font-semibold text-slate-900">{fmt(summary.total_debit)}</td>
                <td className="px-4 py-3 text-right tabular-nums font-semibold text-slate-900">{fmt(summary.total_credit)}</td>
                <td className={`px-4 py-3 text-right tabular-nums font-semibold ${Number(summary.closing_balance) < 0 ? "text-rose-700" : "text-slate-900"}`}>{fmt(summary.closing_balance)}</td>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>
    </AppLayout>
  );
}
