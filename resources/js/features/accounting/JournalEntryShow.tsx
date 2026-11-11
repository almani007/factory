/**
 * Brixel FactoryOS — Journal Entry Detail
 * Shows a single journal entry with its balanced debit/credit lines and lifecycle actions.
 */

import { Link, router, usePage } from "@inertiajs/react";
import { AppLayout } from "@/components/layout";
import { PageHeader } from "@/components/layout";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";
import { ArrowLeft, CheckCircle2, RotateCcw, Trash2, Printer, AlertTriangle } from "lucide-react";

function fmt(n: any) {
  return Number(n || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

const STATUS_BADGE: Record<string, string> = {
  posted: "bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-200",
  submitted: "bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-200",
  draft: "bg-slate-100 text-slate-600 ring-1 ring-inset ring-slate-200",
  reversed: "bg-rose-50 text-rose-700 ring-1 ring-inset ring-rose-200",
};

function InfoRow({ label, value }: { label: string; value: any }) {
  return (
    <div className="flex items-start justify-between gap-4 py-2">
      <span className="text-xs font-medium uppercase tracking-wider text-slate-500">{label}</span>
      <span className="text-right text-sm text-slate-800">{value || <span className="text-slate-400">—</span>}</span>
    </div>
  );
}

export default function JournalEntryShow() {
  const { props } = usePage() as any;
  const entry = props?.entry || {};
  const lines = props?.lines || [];
  const totals = props?.totals || {};
  const errors = props?.errors || {};

  const postEntry = () => {
    if (!window.confirm(`Post journal entry ${entry.reference}?`)) return;
    router.post(`/accounting/journal-entries/${entry.id}/post`, {}, { preserveScroll: true });
  };

  const reverseEntry = () => {
    if (!window.confirm(`Post a reversing entry for ${entry.reference}? The original entry will be marked reversed.`)) return;
    router.post(`/accounting/journal-entries/${entry.id}/reverse`, {}, { preserveScroll: true });
  };

  const deleteEntry = () => {
    if (!window.confirm(`Delete draft journal entry ${entry.reference}? This cannot be undone.`)) return;
    router.delete(`/accounting/journal-entries/${entry.id}`);
  };

  const isBalanced = Math.abs(Number(totals.debit || 0) - Number(totals.credit || 0)) < 0.0001 && Number(totals.debit || 0) > 0;

  return (
    <AppLayout title={`Journal Entry ${entry.reference || ""}`}>
      <PageHeader
        title={`Journal Entry ${entry.reference || ""}`.trim()}
        subtitle={entry.description || "Double-entry journal entry"}
        breadcrumbs={[
          { label: "Accounting", href: "/accounting" },
          { label: "Journal Entries", href: "/accounting" },
          { label: entry.reference || "Entry" },
        ]}
      >
        <div className="flex flex-wrap items-center gap-2">
          <Link href="/accounting" className="inline-flex h-9 items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50"><ArrowLeft size={16} /> Back</Link>
          <button onClick={() => window.print()} className="inline-flex h-9 items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50"><Printer size={14} /> Print</button>
          {["draft", "submitted"].includes(entry.status) && (
            <Button onClick={postEntry} disabled={!isBalanced}><CheckCircle2 size={16} /> Post</Button>
          )}
          {entry.status === "posted" && !entry.reversal_entry_id && (
            <Button variant="outline" onClick={reverseEntry}><RotateCcw size={16} /> Reverse</Button>
          )}
          {entry.status === "draft" && (
            <Button variant="danger" onClick={deleteEntry}><Trash2 size={16} /> Delete Draft</Button>
          )}
        </div>
      </PageHeader>

      {errors.entry && (
        <div className="mb-6 flex items-start gap-2 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
          <AlertTriangle size={18} className="mt-0.5 shrink-0" /> {errors.entry}
        </div>
      )}

      {entry.reversal_entry_id && (
        <div className="mb-6 flex flex-wrap items-center gap-2 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
          <RotateCcw size={18} /> This entry has been reversed by
          <Link href={`/accounting/journal-entries/${entry.reversal_entry_id}`} className="font-semibold underline">{entry.reversal_reference || "the reversal entry"}</Link>.
        </div>
      )}

      {!isBalanced && entry.status !== "reversed" && (
        <div className="mb-6 flex items-start gap-2 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
          <AlertTriangle size={18} className="mt-0.5 shrink-0" /> This entry is not balanced and cannot be posted.
        </div>
      )}

      <div className="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <Card>
          <CardContent className="p-5">
            <p className="text-xs font-medium text-slate-500">Status</p>
            <div className="mt-2"><Badge className={STATUS_BADGE[entry.status] || STATUS_BADGE.draft}>{entry.status || "—"}</Badge></div>
          </CardContent>
        </Card>
        <Card>
          <CardContent className="p-5">
            <p className="text-xs font-medium text-slate-500">Total Debit</p>
            <p className="mt-2 text-2xl font-semibold tabular-nums text-slate-900">{fmt(totals.debit)}</p>
          </CardContent>
        </Card>
        <Card>
          <CardContent className="p-5">
            <p className="text-xs font-medium text-slate-500">Total Credit</p>
            <p className="mt-2 text-2xl font-semibold tabular-nums text-slate-900">{fmt(totals.credit)}</p>
          </CardContent>
        </Card>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div className="lg:col-span-2 space-y-6">
          <div className="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <div className="flex items-center justify-between border-b border-slate-200/60 px-5 py-4">
              <h2 className="text-base font-semibold tracking-tight text-slate-900">Journal Lines</h2>
              <span className="text-xs text-slate-500">{totals.line_count ?? lines.length} line(s)</span>
            </div>
            <div className="overflow-x-auto">
              <table className="min-w-full divide-y divide-slate-100 text-sm">
                <thead className="bg-slate-50">
                  <tr>
                    <th className="px-4 py-2.5 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Account</th>
                    <th className="px-4 py-2.5 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Memo</th>
                    <th className="px-4 py-2.5 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Debit</th>
                    <th className="px-4 py-2.5 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Credit</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-slate-100">
                  {lines.length === 0 ? (
                    <tr><td colSpan={4} className="px-4 py-10 text-center text-sm text-slate-500">No lines recorded for this entry.</td></tr>
                  ) : (
                    lines.map((l: any) => (
                      <tr key={l.id} className="hover:bg-slate-50">
                        <td className="px-4 py-2.5">
                          <Link href={`/accounting/chart-of-accounts/${l.account_id}/ledger`} className="group inline-flex flex-col">
                            <span className="font-mono text-xs font-semibold text-slate-700 group-hover:text-factory-600">{l.account_code}</span>
                            <span className="text-xs text-slate-500">{l.account_name}</span>
                          </Link>
                        </td>
                        <td className="px-4 py-2.5 text-slate-700">{l.description || <span className="text-slate-400">—</span>}</td>
                        <td className="px-4 py-2.5 text-right tabular-nums text-slate-800">{Number(l.debit) ? fmt(l.debit) : <span className="text-slate-400">—</span>}</td>
                        <td className="px-4 py-2.5 text-right tabular-nums text-slate-800">{Number(l.credit) ? fmt(l.credit) : <span className="text-slate-400">—</span>}</td>
                      </tr>
                    ))
                  )}
                </tbody>
                <tfoot className="bg-slate-50">
                  <tr>
                    <td colSpan={2} className="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Totals</td>
                    <td className="px-4 py-3 text-right font-semibold tabular-nums text-slate-900">{fmt(totals.debit)}</td>
                    <td className="px-4 py-3 text-right font-semibold tabular-nums text-slate-900">{fmt(totals.credit)}</td>
                  </tr>
                </tfoot>
              </table>
            </div>
          </div>

          {entry.description && (
            <Card>
              <CardHeader><CardTitle className="text-sm font-semibold text-slate-900">Description</CardTitle></CardHeader>
              <CardContent><p className="whitespace-pre-line text-sm text-slate-700">{entry.description}</p></CardContent>
            </Card>
          )}
        </div>

        <div className="space-y-6">
          <Card>
            <CardHeader><CardTitle className="text-sm font-semibold text-slate-900">Entry Details</CardTitle></CardHeader>
            <CardContent className="divide-y divide-slate-100 pt-0">
              <InfoRow label="Reference" value={<span className="font-mono">{entry.reference}</span>} />
              <InfoRow label="Document Date" value={entry.document_date} />
              <InfoRow label="Posting Date" value={entry.posting_date} />
              <InfoRow label="Accounting Book" value={entry.book_name ? `${entry.book_name}${entry.book_code ? ` (${entry.book_code})` : ""}` : null} />
              <InfoRow label="Fiscal Period" value={entry.period_name} />
              <InfoRow label="Currency" value={entry.currency} />
              <InfoRow label="Created By" value={entry.created_by_name} />
              <InfoRow label="Created At" value={entry.created_at} />
              <InfoRow label="Posted At" value={entry.posted_at} />
              <InfoRow label="Approved By" value={entry.approved_by_name} />
            </CardContent>
          </Card>
        </div>
      </div>
    </AppLayout>
  );
}
