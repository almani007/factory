/**
 * Brixel FactoryOS — Chart of Account Detail
 */
import { Link, router, usePage } from "@inertiajs/react";
import { AppLayout } from "@/components/layout";
import { PageHeader } from "@/components/layout";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";
import { DataTable } from "@/components/DataTable";
import { Button } from "@/components/ui/button";
import { ArrowLeft, Edit, Power, Copy, Plus, BookOpen } from "lucide-react";

const TYPE_BADGE: Record<string, string> = {
  asset: "bg-blue-50 text-blue-700 ring-1 ring-inset ring-blue-200",
  liability: "bg-indigo-50 text-indigo-700 ring-1 ring-inset ring-indigo-200",
  equity: "bg-purple-50 text-purple-700 ring-1 ring-inset ring-purple-200",
  revenue: "bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-200",
  expense: "bg-rose-50 text-rose-700 ring-1 ring-inset ring-rose-200",
};

function fmt(n: any) {
  const v = Number(n || 0);
  return v.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

export default function AccountShow() {
  const { props } = usePage() as any;
  const account = props?.account || {};
  const children = props?.children || [];
  const stats = props?.stats || {};
  const recent = props?.recent_lines || [];
  const auditHistory = props?.audit_history || [];

  return (
    <AppLayout title={`Account ${account.code || ""}`}>
      <PageHeader
        title={`${account.code || ""} — ${account.name || "Account"}`}
        subtitle="Chart of account detail, hierarchy, balances, and recent postings"
        breadcrumbs={[{ label: "Accounting", href: "/accounting" }, { label: "Chart of Accounts", href: "/accounting/chart-of-accounts" }, { label: account.code || "Detail" }]}
      >
        <div className="flex flex-wrap items-center gap-2">
          <Link href="/accounting/chart-of-accounts" className="inline-flex h-9 items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50"><ArrowLeft size={16}/> Back</Link>
          <Link href={`/accounting/chart-of-accounts/create?parent_id=${account.id}`} className="inline-flex h-9 items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50"><Plus size={16}/> New Child</Link>
          <Link href="/accounting/chart-of-accounts/create" className="inline-flex h-9 items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50"><Plus size={16}/> New</Link>
          <Link href={`/accounting/chart-of-accounts/${account.id}/ledger`} className="inline-flex h-9 items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50"><BookOpen size={16}/> Ledger</Link>
          <Button variant="outline" onClick={() => router.visit(`/accounting/chart-of-accounts/${account.id}/duplicate`)}><Copy size={16}/> Duplicate</Button>
          <Button variant="outline" onClick={() => router.visit(`/accounting/chart-of-accounts/${account.id}/edit`)}><Edit size={16}/> Edit</Button>
          <Button variant="outline" onClick={() => router.post(`/accounting/chart-of-accounts/${account.id}/toggle`, {}, { preserveScroll: true })}><Power size={16}/> {account.is_active ? "Deactivate" : "Activate"}</Button>
        </div>
      </PageHeader>

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <Card className="lg:col-span-2">
          <CardHeader><CardTitle className="text-base font-semibold text-slate-900">Account Information</CardTitle></CardHeader>
          <CardContent className="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
            <div><p className="text-slate-500">Code</p><p className="font-semibold text-slate-900">{account.code}</p></div>
            <div><p className="text-slate-500">Name</p><p className="font-semibold text-slate-900">{account.name}</p></div>
            <div><p className="text-slate-500">Type</p><Badge className={TYPE_BADGE[account.type] || "bg-slate-100 text-slate-600"}>{account.type}</Badge></div>
            <div><p className="text-slate-500">Status</p><Badge className={account.is_active ? "bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-200" : "bg-slate-100 text-slate-500 ring-1 ring-inset ring-slate-200"}>{account.is_active ? "Active" : "Inactive"}</Badge></div>
            <div><p className="text-slate-500">Parent</p><p className="text-slate-900">{account.parent_label || "—"}</p></div>
            <div><p className="text-slate-500">Group</p><p className="text-slate-900">{account.group_label || "—"}</p></div>
            <div><p className="text-slate-500">Flags</p><div className="flex flex-wrap gap-1.5 mt-1">
              <Badge className={account.allow_direct_posting ? "bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-200" : "bg-slate-100 text-slate-500 ring-1 ring-inset ring-slate-200"}>{account.allow_direct_posting ? "Direct Posting" : "No Direct Posting"}</Badge>
              {account.is_cash_account && <Badge className="bg-sky-50 text-sky-700 ring-1 ring-inset ring-sky-200">Cash</Badge>}
              {account.is_bank_account && <Badge className="bg-sky-50 text-sky-700 ring-1 ring-inset ring-sky-200">Bank</Badge>}
            </div></div>
            <div><p className="text-slate-500">Timestamps</p><p className="text-slate-700 text-xs">Created: {account.created_at || "—"}<br/>Updated: {account.updated_at || "—"}</p></div>
          </CardContent>
        </Card>

        <Card>
          <CardHeader><CardTitle className="text-base font-semibold text-slate-900">Posted Balance & Usage</CardTitle></CardHeader>
          <CardContent className="space-y-3 text-sm">
            <div className="flex justify-between"><span className="text-slate-500">Children</span><span className="font-semibold text-slate-900">{stats.children_count ?? 0}</span></div>
            <div className="flex justify-between"><span className="text-slate-500">Journal lines</span><span className="font-semibold text-slate-900">{stats.journal_lines_count ?? 0}</span></div>
            <div className="flex justify-between"><span className="text-slate-500">Posted lines</span><span className="font-semibold text-slate-900">{stats.posted_lines_count ?? 0}</span></div>
            <div className="border-t border-slate-100 pt-3 flex justify-between"><span className="text-slate-500">Posted debit</span><span className="font-medium text-slate-900 tabular-nums">{fmt(stats.posted_debit)}</span></div>
            <div className="flex justify-between"><span className="text-slate-500">Posted credit</span><span className="font-medium text-slate-900 tabular-nums">{fmt(stats.posted_credit)}</span></div>
            <div className="flex justify-between"><span className="text-slate-500">Net balance</span><span className={`font-semibold tabular-nums ${Number(stats.posted_balance||0) >= 0 ? 'text-emerald-700' : 'text-rose-700'}`}>{fmt(stats.posted_balance)}</span></div>
            <p className="text-xs text-slate-400 pt-1">Balance = posted debit − posted credit (base).</p>
          </CardContent>
        </Card>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <DataTable
          title="Child Accounts"
          columns={[
            { key: "code", label: "Code" },
            { key: "name", label: "Name" },
            { key: "type", label: "Type", format: (v: string) => <Badge className={TYPE_BADGE[v] || "bg-slate-100 text-slate-600"}>{v}</Badge> },
            { key: "is_active", label: "Status", format: (v: boolean) => <Badge className={v ? "bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-200" : "bg-slate-100 text-slate-500 ring-1 ring-inset ring-slate-200"}>{v ? "Active" : "Inactive"}</Badge> },
          ]}
          data={children}
          rowActions={[{ label: "View", variant: "ghost", onClick: (row: any) => router.visit(`/accounting/chart-of-accounts/${row.id}`) }]}
        />
        <DataTable
          title="Recent Journal Lines"
          columns={[
            { key: "posting_date", label: "Date" },
            { key: "entry_reference", label: "Entry Ref" },
            { key: "entry_status", label: "Status", format: (v: string) => v ? <Badge className={v === 'posted' ? "bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-200" : "bg-slate-100 text-slate-600 ring-1 ring-inset ring-slate-200"}>{v}</Badge> : <span className="text-slate-400">—</span> },
            { key: "debit", label: "Debit", align: "right", format: (v: number) => Number(v) ? fmt(v) : <span className="text-slate-400">—</span> },
            { key: "credit", label: "Credit", align: "right", format: (v: number) => Number(v) ? fmt(v) : <span className="text-slate-400">—</span> },
            { key: "description", label: "Description" },
          ]}
          data={recent}
        />
      </div>

      <div className="mt-6">
        <DataTable
          title="Account Change History"
          columns={[
            { key: "created_at", label: "Timestamp" },
            { key: "action", label: "Action", format: (v: string) => v ? <Badge className="bg-slate-100 text-slate-700 ring-1 ring-inset ring-slate-200">{String(v).replace(/_/g, " ")}</Badge> : <span className="text-slate-400">—</span> },
            { key: "user_name", label: "User" },
            { key: "old_values", label: "Before", format: (v: string) => <span className="block max-w-xs truncate font-mono text-[11px] text-slate-500" title={v || ""}>{v || "—"}</span> },
            { key: "new_values", label: "After", format: (v: string) => <span className="block max-w-xs truncate font-mono text-[11px] text-slate-500" title={v || ""}>{v || "—"}</span> },
          ]}
          data={auditHistory}
          emptyMessage="No recorded changes for this account yet."
        />
      </div>
    </AppLayout>
  );
}
