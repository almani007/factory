/**
 * Brixel FactoryOS — Cash Flow Statement
 * Operating, investing, and financing cash movements derived from posted journals.
 */

import * as React from "react";
import { Link, router, usePage } from "@inertiajs/react";
import { AppLayout } from "@/components/layout";
import { PageHeader } from "@/components/layout";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import { Download, Filter, ArrowRight, AlertTriangle, Wallet, TrendingUp, TrendingDown, RefreshCw } from "lucide-react";

function fmt(n: any) {
  return Number(n || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

const CATEGORY_STYLE: Record<string, { badge: string; accent: string }> = {
  operating: { badge: "bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-200", accent: "text-emerald-700" },
  investing: { badge: "bg-sky-50 text-sky-700 ring-1 ring-inset ring-sky-200", accent: "text-sky-700" },
  financing: { badge: "bg-indigo-50 text-indigo-700 ring-1 ring-inset ring-indigo-200", accent: "text-indigo-700" },
};

export default function CashFlow() {
  const { props } = usePage() as any;
  const categories: any[] = props?.categories || [];
  const summary = props?.summary || {};
  const filters = props?.filters || {};

  const [from, setFrom] = React.useState(filters.from || "");
  const [to, setTo] = React.useState(filters.to || "");

  const apply = (next?: { from?: string; to?: string }) => {
    const f = next?.from !== undefined ? next.from : from;
    const t = next?.to !== undefined ? next.to : to;
    router.get("/accounting/cash-flow", {
      ...(f ? { from: f } : {}),
      ...(t ? { to: t } : {}),
    }, { preserveState: true, replace: true });
  };

  const exportUrl = `/accounting/cash-flow/export?${new URLSearchParams({
    ...(from ? { from } : {}),
    ...(to ? { to } : {}),
  }).toString()}`;

  return (
    <AppLayout title="Cash Flow Statement">
      <PageHeader
        title="Cash Flow Statement"
        subtitle="Operating, investing, and financing movements in cash and bank accounts"
        breadcrumbs={[{ label: "Accounting", href: "/accounting" }, { label: "Cash Flow" }]}
      >
        <div className="flex flex-wrap items-center gap-2">
          <a href={exportUrl} className="inline-flex h-9 items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50"><Download size={14} /> Export CSV</a>
          <Link href="/accounting/balance-sheet" className="inline-flex h-9 items-center gap-1.5 rounded-lg bg-factory-600 px-3 text-sm font-medium text-white shadow-sm hover:bg-factory-700">
            <ArrowRight size={16} /> Balance Sheet
          </Link>
        </div>
      </PageHeader>

      {Number(summary.cash_accounts || 0) === 0 && (
        <div className="mb-6 flex items-start gap-2 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
          <AlertTriangle size={18} className="mt-0.5 shrink-0" />
          <div>No cash or bank accounts are flagged. Enable <span className="font-semibold">Cash account</span> or <span className="font-semibold">Bank account</span> on accounts in the{" "}
            <Link href="/accounting/chart-of-accounts" className="font-semibold underline">Chart of Accounts</Link> to populate this report.
          </div>
        </div>
      )}

      <div className="mb-6 grid grid-cols-2 gap-4 lg:grid-cols-4">
        <Card><CardContent className="p-5"><p className="text-xs font-medium text-slate-500 flex items-center gap-1"><Wallet size={13}/> Opening Cash</p><p className="mt-1 text-xl font-semibold tabular-nums text-slate-900">{fmt(summary.opening_cash)}</p></CardContent></Card>
        <Card><CardContent className="p-5"><p className="text-xs font-medium text-slate-500 flex items-center gap-1">{Number(summary.net_change) >= 0 ? <TrendingUp size={13}/> : <TrendingDown size={13}/>} Net Change</p><p className={`mt-1 text-xl font-semibold tabular-nums ${Number(summary.net_change) >= 0 ? "text-emerald-700" : "text-rose-700"}`}>{fmt(summary.net_change)}</p></CardContent></Card>
        <Card><CardContent className="p-5"><p className="text-xs font-medium text-slate-500">Closing Cash</p><p className="mt-1 text-xl font-semibold tabular-nums text-slate-900">{fmt(summary.closing_cash)}</p></CardContent></Card>
        <Card><CardContent className="p-5"><p className="text-xs font-medium text-slate-500">Reconciliation</p><div className="mt-2 flex items-center gap-2"><Badge className={summary.reconciled ? "bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-200" : "bg-rose-50 text-rose-700 ring-1 ring-inset ring-rose-200"}>{summary.reconciled ? "Reconciled" : "Variance"}</Badge><span className="text-xs text-slate-500">{summary.line_count ?? 0} accounts</span></div></CardContent></Card>
      </div>

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

      <div className="space-y-6">
        {categories.map((category) => {
          const style = CATEGORY_STYLE[category.key] || CATEGORY_STYLE.operating;
          return (
            <div key={category.key} className="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
              <div className="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200/60 px-5 py-4">
                <div className="flex items-center gap-2">
                  <h2 className="text-base font-semibold tracking-tight text-slate-900">{category.label}</h2>
                  <Badge className={style.badge}>{category.accounts?.length || 0} accounts</Badge>
                </div>
                <div className="flex items-center gap-4 text-xs">
                  <span className="text-slate-500">Inflow <span className="font-semibold tabular-nums text-emerald-700">{fmt(category.inflow)}</span></span>
                  <span className="text-slate-500">Outflow <span className="font-semibold tabular-nums text-rose-700">{fmt(category.outflow)}</span></span>
                  <span className="text-slate-500">Net <span className={`font-semibold tabular-nums ${Number(category.net) >= 0 ? "text-emerald-700" : "text-rose-700"}`}>{fmt(category.net)}</span></span>
                </div>
              </div>
              <div className="overflow-x-auto">
                <table className="min-w-full divide-y divide-slate-100 text-sm">
                  <thead className="bg-slate-50">
                    <tr>
                      <th className="px-4 py-2.5 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Account</th>
                      <th className="px-4 py-2.5 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Inflow</th>
                      <th className="px-4 py-2.5 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Outflow</th>
                      <th className="px-4 py-2.5 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Net</th>
                    </tr>
                  </thead>
                  <tbody className="divide-y divide-slate-100">
                    {(category.accounts || []).length === 0 ? (
                      <tr><td colSpan={4} className="px-4 py-8 text-center text-sm text-slate-500">No cash movements in this category for the selected period.</td></tr>
                    ) : (
                      (category.accounts || []).map((row: any) => (
                        <tr key={row.account_id} className="hover:bg-slate-50">
                          <td className="px-4 py-2.5">
                            <Link href={`/accounting/chart-of-accounts/${row.account_id}/ledger`} className="group inline-flex flex-col">
                              <span className="font-mono text-xs font-semibold text-slate-700 group-hover:text-factory-600">{row.account_code}</span>
                              <span className="text-xs text-slate-500">{row.account_name}</span>
                            </Link>
                          </td>
                          <td className="px-4 py-2.5 text-right tabular-nums text-emerald-700">{Number(row.inflow) ? fmt(row.inflow) : <span className="text-slate-400">—</span>}</td>
                          <td className="px-4 py-2.5 text-right tabular-nums text-rose-700">{Number(row.outflow) ? fmt(row.outflow) : <span className="text-slate-400">—</span>}</td>
                          <td className={`px-4 py-2.5 text-right font-medium tabular-nums ${Number(row.net) >= 0 ? "text-slate-900" : "text-rose-700"}`}>{fmt(row.net)}</td>
                        </tr>
                      ))
                    )}
                  </tbody>
                  {(category.accounts || []).length > 0 && (
                    <tfoot className="bg-slate-50">
                      <tr>
                        <td className="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Total {category.label}</td>
                        <td className="px-4 py-3 text-right font-semibold tabular-nums text-emerald-700">{fmt(category.inflow)}</td>
                        <td className="px-4 py-3 text-right font-semibold tabular-nums text-rose-700">{fmt(category.outflow)}</td>
                        <td className={`px-4 py-3 text-right font-semibold tabular-nums ${Number(category.net) >= 0 ? "text-slate-900" : "text-rose-700"}`}>{fmt(category.net)}</td>
                      </tr>
                    </tfoot>
                  )}
                </table>
              </div>
            </div>
          );
        })}
      </div>

      <div className="mt-6 flex flex-col items-stretch justify-between gap-3 rounded-xl border border-slate-200 bg-white px-5 py-4 shadow-sm sm:flex-row sm:items-center">
        <div className="flex items-center gap-2 text-sm text-slate-600"><RefreshCw size={15} /> Opening {fmt(summary.opening_cash)} + Net change {fmt(summary.net_change)} = Closing {fmt(summary.closing_cash)}</div>
        <Link href="/accounting/chart-of-accounts" className="text-sm font-medium text-factory-600 hover:text-factory-700">Manage cash accounts →</Link>
      </div>
    </AppLayout>
  );
}
