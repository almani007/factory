/**
 * Brixel FactoryOS — Chart of Accounts
 * Complete COA management: search/filter, hierarchy/group info, CRUD actions, export.
 */
import * as React from "react";
import { Link, router, usePage } from "@inertiajs/react";
import { AppLayout } from "@/components/layout";
import { PageHeader } from "@/components/layout";
import { DataTable } from "@/components/DataTable";
import { Plus, Edit, Search, Filter, Download, Power, Trash2, Eye, Copy, Table2, GitBranch, ChevronRight, ChevronDown, BookOpen } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";

const TYPE_LABELS: Record<string, string> = { asset: "Asset", liability: "Liability", equity: "Equity", revenue: "Revenue", expense: "Expense" };
const TYPE_BADGE: Record<string, string> = {
  asset: "bg-blue-50 text-blue-700 ring-1 ring-inset ring-blue-200",
  liability: "bg-indigo-50 text-indigo-700 ring-1 ring-inset ring-indigo-200",
  equity: "bg-purple-50 text-purple-700 ring-1 ring-inset ring-purple-200",
  revenue: "bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-200",
  expense: "bg-rose-50 text-rose-700 ring-1 ring-inset ring-rose-200",
};

export default function ChartOfAccounts() {
  const { props } = usePage() as any;
  const raw = props?.accounts;
  const accounts = Array.isArray(raw) ? raw : (raw?.data || []);
  const meta = Array.isArray(raw) ? {} : (raw?.meta || {});
  const typeCounts = props?.type_counts || {};
  const summary = props?.summary || {};
  const filters = props?.filters || {};
  const groupOptions = props?.groups || [];
  const [q, setQ] = React.useState(filters.q || "");
  const [type, setType] = React.useState(filters.type || "all");
  const [status, setStatus] = React.useState(filters.status || "all");
  const [usage, setUsage] = React.useState(filters.usage || "all");
  const [posting, setPosting] = React.useState(filters.posting || "all");
  const [flag, setFlag] = React.useState(filters.flag || "all");
  const [hasChildren, setHasChildren] = React.useState(filters.has_children || "all");
  const [group, setGroup] = React.useState(filters.group || "all");
  const [view, setView] = React.useState(props?.view || "table");
  const tree = props?.tree || [];

  const totalAccounts = summary.total ?? Object.values(typeCounts).reduce((sum: number, v: any) => sum + (Number(v) || 0), 0);
  const apply = (next?: any) => {
    const p = { q, type, status, usage, posting, flag, has_children: hasChildren, group, view, ...next };
    router.get("/accounting/chart-of-accounts", {
      q: p.q || undefined,
      type: p.type && p.type !== "all" ? p.type : undefined,
      status: p.status && p.status !== "all" ? p.status : undefined,
      usage: p.usage && p.usage !== "all" ? p.usage : undefined,
      posting: p.posting && p.posting !== "all" ? p.posting : undefined,
      flag: p.flag && p.flag !== "all" ? p.flag : undefined,
      has_children: p.has_children && p.has_children !== "all" ? p.has_children : undefined,
      group: p.group && p.group !== "all" ? p.group : undefined,
      view: p.view === "tree" ? "tree" : undefined,
    }, { preserveState: true, replace: true });
  };
  const switchView = (next: "table" | "tree") => { setView(next); apply({ view: next }); };
  const toggle = (row: any) => router.post(`/accounting/chart-of-accounts/${row.id}/toggle`, {}, { preserveScroll: true });
  const bulk = (action: "activate" | "deactivate") => {
    const label = action === "activate" ? "activate" : "deactivate";
    if (!window.confirm(`Are you sure you want to ${label} all accounts matching the current filters?`)) return;
    router.post("/accounting/chart-of-accounts/bulk-toggle", {
      action,
      q: q || undefined,
      type: type !== "all" ? type : undefined,
      status: status !== "all" ? status : undefined,
      usage: usage !== "all" ? usage : undefined,
      posting: posting !== "all" ? posting : undefined,
      flag: flag !== "all" ? flag : undefined,
      has_children: hasChildren !== "all" ? hasChildren : undefined,
      group: group !== "all" ? group : undefined,
    }, { preserveScroll: true });
  };
  const remove = (row: any) => {
    if (!row.can_delete) {
      window.alert(`Cannot delete this account.${row.children_count ? ` It has ${row.children_count} child account(s).` : ""}${row.journal_lines_count ? ` It has ${row.journal_lines_count} journal line(s). Deactivate instead.` : ""}`);
      return;
    }
    if (!window.confirm(`Delete account ${row.code} — ${row.name}? This cannot be undone.`)) return;
    router.delete(`/accounting/chart-of-accounts/${row.id}`, { preserveScroll: true });
  };

  return (
    <AppLayout title="Chart of Accounts">
      <PageHeader
        title="Chart of Accounts"
        subtitle="Complete account master: hierarchy, groups, posting controls, and status management"
        breadcrumbs={[{ label: "Accounting", href: "/accounting" }, { label: "Chart of Accounts" }]}
      >
        <div className="flex flex-wrap items-center gap-2">
          <div className="inline-flex rounded-lg border border-slate-200 bg-white p-0.5 shadow-sm">
            <button type="button" onClick={() => switchView("table")} className={`inline-flex h-8 items-center gap-1.5 rounded-md px-2.5 text-xs font-medium ${view === "table" ? "bg-slate-900 text-white" : "text-slate-600 hover:bg-slate-50"}`}><Table2 size={13}/> Table</button>
            <button type="button" onClick={() => switchView("tree")} className={`inline-flex h-8 items-center gap-1.5 rounded-md px-2.5 text-xs font-medium ${view === "tree" ? "bg-slate-900 text-white" : "text-slate-600 hover:bg-slate-50"}`}><GitBranch size={13}/> Tree</button>
          </div>
          <Button variant="outline" onClick={() => router.visit("/accounting/account-groups")}>Account Groups</Button>
          <Button variant="outline" onClick={() => router.visit("/accounting/chart-of-accounts/import")}>Import CSV</Button>
          <a href="/accounting/chart-of-accounts/export" className="inline-flex h-9 items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50"><Download size={14} /> Export CSV</a>
          <Button onClick={() => router.visit("/accounting/chart-of-accounts/create")}><Plus size={16} /> New Account</Button>
        </div>
      </PageHeader>

      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <Card><CardHeader className="p-5 pb-2"><CardTitle className="text-sm font-medium text-slate-500">Total Accounts</CardTitle></CardHeader><CardContent className="p-5 pt-0"><p className="text-2xl font-semibold tracking-tight text-slate-900 tabular-nums">{totalAccounts}</p><p className="text-xs text-slate-500 mt-1">{summary.active ?? 0} active · {summary.inactive ?? 0} inactive</p></CardContent></Card>
        <Card><CardHeader className="p-5 pb-2"><CardTitle className="text-sm font-medium text-slate-500">Assets</CardTitle></CardHeader><CardContent className="p-5 pt-0"><p className="text-2xl font-semibold tracking-tight text-slate-900 tabular-nums">{typeCounts.asset || 0}</p><p className="text-xs text-slate-500 mt-1">Resource accounts</p></CardContent></Card>
        <Card><CardHeader className="p-5 pb-2"><CardTitle className="text-sm font-medium text-slate-500">Liabilities & Equity</CardTitle></CardHeader><CardContent className="p-5 pt-0"><p className="text-2xl font-semibold tracking-tight text-slate-900 tabular-nums">{(typeCounts.liability || 0) + (typeCounts.equity || 0)}</p><p className="text-xs text-slate-500 mt-1">Claims accounts</p></CardContent></Card>
        <Card><CardHeader className="p-5 pb-2"><CardTitle className="text-sm font-medium text-slate-500">Revenue & Expense</CardTitle></CardHeader><CardContent className="p-5 pt-0"><p className="text-2xl font-semibold tracking-tight text-slate-900 tabular-nums">{(typeCounts.revenue || 0) + (typeCounts.expense || 0)}</p><p className="text-xs text-slate-500 mt-1">P&L accounts · {summary.posting ?? 0} posting-enabled</p></CardContent></Card>
      </div>

      <div className="mb-6 rounded-xl border border-slate-200 bg-white p-4 shadow-sm space-y-3">
        <div className="flex flex-col gap-3 lg:flex-row lg:items-end">
          <div className="flex-1">
            <label className="mb-1 block text-xs font-semibold uppercase tracking-wider text-slate-500">Search</label>
            <div className="relative"><Search size={15} className="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" /><input value={q} onChange={e=>setQ(e.target.value)} onKeyDown={e=>e.key==='Enter'&&apply({q})} placeholder="Code, name, parent, group…" className="h-10 w-full rounded-lg border border-slate-200 bg-white pl-9 pr-3 text-sm focus:border-factory-500 focus:outline-none focus:ring-1 focus:ring-factory-500" /></div>
          </div>
          <div className="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-7 gap-3 flex-[2]">
            <div><label className="mb-1 block text-xs font-semibold uppercase tracking-wider text-slate-500">Type</label><select value={type} onChange={e=>{setType(e.target.value); apply({type:e.target.value});}} className="h-10 w-full rounded-lg border border-slate-200 bg-white px-2 text-sm"><option value="all">All</option><option value="asset">Asset</option><option value="liability">Liability</option><option value="equity">Equity</option><option value="revenue">Revenue</option><option value="expense">Expense</option></select></div>
            <div><label className="mb-1 block text-xs font-semibold uppercase tracking-wider text-slate-500">Group</label><select value={group} onChange={e=>{setGroup(e.target.value); apply({group:e.target.value});}} className="h-10 w-full rounded-lg border border-slate-200 bg-white px-2 text-sm"><option value="all">All</option>{groupOptions.map((g: any) => <option key={g.id} value={g.id}>{g.code ? `${g.code} — ` : ""}{g.name}</option>)}</select></div>
            <div><label className="mb-1 block text-xs font-semibold uppercase tracking-wider text-slate-500">Status</label><select value={status} onChange={e=>{setStatus(e.target.value); apply({status:e.target.value});}} className="h-10 w-full rounded-lg border border-slate-200 bg-white px-2 text-sm"><option value="all">All</option><option value="active">Active</option><option value="inactive">Inactive</option></select></div>
            <div><label className="mb-1 block text-xs font-semibold uppercase tracking-wider text-slate-500">Usage</label><select value={usage} onChange={e=>{setUsage(e.target.value); apply({usage:e.target.value});}} className="h-10 w-full rounded-lg border border-slate-200 bg-white px-2 text-sm"><option value="all">All</option><option value="used">Used</option><option value="unused">Unused</option></select></div>
            <div><label className="mb-1 block text-xs font-semibold uppercase tracking-wider text-slate-500">Posting</label><select value={posting} onChange={e=>{setPosting(e.target.value); apply({posting:e.target.value});}} className="h-10 w-full rounded-lg border border-slate-200 bg-white px-2 text-sm"><option value="all">All</option><option value="direct">Direct</option><option value="blocked">Blocked</option></select></div>
            <div><label className="mb-1 block text-xs font-semibold uppercase tracking-wider text-slate-500">Flag</label><select value={flag} onChange={e=>{setFlag(e.target.value); apply({flag:e.target.value});}} className="h-10 w-full rounded-lg border border-slate-200 bg-white px-2 text-sm"><option value="all">All</option><option value="cash">Cash</option><option value="bank">Bank</option></select></div>
            <div><label className="mb-1 block text-xs font-semibold uppercase tracking-wider text-slate-500">Children</label><select value={hasChildren} onChange={e=>{setHasChildren(e.target.value); apply({has_children:e.target.value});}} className="h-10 w-full rounded-lg border border-slate-200 bg-white px-2 text-sm"><option value="all">All</option><option value="yes">Has</option><option value="no">None</option></select></div>
          </div>
        </div>
        <div className="flex flex-wrap gap-2">
          <Button variant="outline" onClick={()=>apply()}><Filter size={14}/> Apply</Button>
          <Button variant="ghost" onClick={()=>{setQ('');setType('all');setStatus('all');setUsage('all');setPosting('all');setFlag('all');setHasChildren('all');setGroup('all');router.get('/accounting/chart-of-accounts', {}, {preserveState:true, replace:true});}}>Reset</Button>
          <Button variant="outline" onClick={()=>bulk("activate")}><Power size={14}/> Activate matching</Button>
          <Button variant="ghost" onClick={()=>bulk("deactivate")}><Power size={14}/> Deactivate matching</Button>
        </div>
      </div>

      {view === "tree" ? (
        <div className="rounded-xl border border-slate-200 bg-white shadow-sm">
          <div className="flex items-center justify-between border-b border-slate-200/60 px-5 py-4">
            <div>
              <h2 className="text-base font-semibold tracking-tight text-slate-900">Account Hierarchy</h2>
              <p className="mt-0.5 text-xs text-slate-500">{countTreeNodes(tree)} accounts in hierarchical view</p>
            </div>
          </div>
          <div className="p-3">
            {tree.length === 0 ? (
              <p className="py-10 text-center text-sm text-slate-500">No accounts match the current filters.</p>
            ) : (
              <div className="space-y-0.5">
                {tree.map((node: any) => <AccountTreeNode key={node.id} node={node} depth={0} onToggle={toggle} />)}
              </div>
            )}
          </div>
        </div>
      ) : (
      <DataTable
        title="Accounts"
        columns={[
          { key: "code", label: "Code" },
          { key: "name", label: "Account Name" },
          { key: "type", label: "Type", format: (v: string) => <Badge className={TYPE_BADGE[v] || "bg-slate-100 text-slate-600"}>{TYPE_LABELS[v] || v}</Badge> },
          { key: "parent_label", label: "Parent", format: (v: string) => v || <span className="text-slate-400">—</span> },
          { key: "group_name", label: "Group", format: (v: string) => v || <span className="text-slate-400">—</span> },
          { key: "posted_balance", label: "Posted Balance", align: "right", format: (v: number) => {
            const n = Number(v || 0); return <span className={`tabular-nums font-medium ${n > 0 ? 'text-emerald-700' : n < 0 ? 'text-rose-700' : 'text-slate-500'}`}>{n.toLocaleString(undefined,{minimumFractionDigits:2,maximumFractionDigits:2})}</span>;
          }},
          { key: "children_count", label: "Children", format: (v: number) => <span className={Number(v) > 0 ? "text-slate-700 font-medium" : "text-slate-400"}>{Number(v) || 0}</span> },
          { key: "journal_lines_count", label: "Usage", format: (v: number) => Number(v) > 0 ? <Badge className="bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-200">{v} entries</Badge> : <span className="text-slate-400">Unused</span> },
          { key: "allow_direct_posting", label: "Posting", format: (v: boolean) => <span className={v ? "text-emerald-600 font-medium" : "text-slate-400"}>{v ? "Direct" : "Blocked"}</span> },
          { key: "is_active", label: "Status", format: (v: boolean) => <Badge className={v ? "bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-200" : "bg-slate-100 text-slate-500 ring-1 ring-inset ring-slate-200"}>{v ? "Active" : "Inactive"}</Badge> },
        ]}
        data={accounts}
        meta={meta}
        rowActions={[
          { label: "View", icon: <Eye size={14} />, variant: "ghost", onClick: (row: any) => router.visit(`/accounting/chart-of-accounts/${row.id}`) },
          { label: "Ledger", icon: <BookOpen size={14} />, variant: "ghost", onClick: (row: any) => router.visit(`/accounting/chart-of-accounts/${row.id}/ledger`) },
          { label: "Edit", icon: <Edit size={14} />, variant: "outline", onClick: (row: any) => router.visit(`/accounting/chart-of-accounts/${row.id}/edit`) },
          { label: "Duplicate", icon: <Copy size={14} />, variant: "ghost", onClick: (row: any) => router.visit(`/accounting/chart-of-accounts/${row.id}/duplicate`) },
          { label: "Toggle", icon: <Power size={14} />, variant: "ghost", onClick: (row: any) => toggle(row) },
          { label: "Delete", icon: <Trash2 size={14} />, variant: "ghost", onClick: (row: any) => remove(row) },
        ]}
      />
      )}
    </AppLayout>
  );
}

function countTreeNodes(nodes: any[]): number {
  return (nodes || []).reduce((sum, n) => sum + 1 + countTreeNodes(n.children || []), 0);
}

function AccountTreeNode({ node, depth, onToggle }: { node: any; depth: number; onToggle: (row: any) => void }) {
  const [open, setOpen] = React.useState(depth < 2);
  const children = node.children || [];
  const hasChildren = children.length > 0;
  const n = Number(node.posted_balance || 0);

  return (
    <div>
      <div className="flex items-center gap-2 rounded-lg px-2 py-1.5 hover:bg-slate-50" style={{ paddingLeft: `${depth * 18 + 8}px` }}>
        <button type="button" onClick={() => setOpen(!open)} className={`flex h-6 w-6 items-center justify-center rounded text-slate-400 ${hasChildren ? "hover:bg-slate-100 hover:text-slate-700" : "invisible"}`}>
          {open ? <ChevronDown size={14} /> : <ChevronRight size={14} />}
        </button>
        <span className="w-20 shrink-0 font-mono text-xs font-semibold text-slate-700">{node.code}</span>
        <span className="min-w-0 flex-1 truncate text-sm text-slate-800">{node.name}</span>
        <Badge className={TYPE_BADGE[node.type] || "bg-slate-100 text-slate-600"}>{TYPE_LABELS[node.type] || node.type}</Badge>
        <span className={`w-28 shrink-0 text-right text-xs tabular-nums font-medium ${n > 0 ? "text-emerald-700" : n < 0 ? "text-rose-700" : "text-slate-400"}`}>{n.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</span>
        <span className={`h-2 w-2 shrink-0 rounded-full ${node.is_active ? "bg-emerald-500" : "bg-slate-300"}`} title={node.is_active ? "Active" : "Inactive"} />
        <div className="flex items-center gap-1">
          <button type="button" onClick={() => router.visit(`/accounting/chart-of-accounts/${node.id}`)} className="rounded p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-700" title="View"><Eye size={13} /></button>
          <button type="button" onClick={() => router.visit(`/accounting/chart-of-accounts/${node.id}/edit`)} className="rounded p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-700" title="Edit"><Edit size={13} /></button>
          <button type="button" onClick={() => router.visit(`/accounting/chart-of-accounts/create?parent_id=${node.id}`)} className="rounded p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-700" title="Add child"><Plus size={13} /></button>
          <button type="button" onClick={() => onToggle(node)} className="rounded p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-700" title={node.is_active ? "Deactivate" : "Activate"}><Power size={13} /></button>
        </div>
      </div>
      {open && hasChildren && (
        <div>
          {children.map((child: any) => <AccountTreeNode key={child.id} node={child} depth={depth + 1} onToggle={onToggle} />)}
        </div>
      )}
    </div>
  );
}
