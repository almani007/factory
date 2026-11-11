/**
 * Brixel FactoryOS — Account Groups
 */
import * as React from "react";
import { router, usePage } from "@inertiajs/react";
import { AppLayout } from "@/components/layout";
import { PageHeader } from "@/components/layout";
import { DataTable } from "@/components/DataTable";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Search, Filter, Plus, Edit, Trash2 } from "lucide-react";

const TYPE_BADGE: Record<string, string> = {
  asset: "bg-blue-50 text-blue-700 ring-1 ring-inset ring-blue-200",
  liability: "bg-indigo-50 text-indigo-700 ring-1 ring-inset ring-indigo-200",
  equity: "bg-purple-50 text-purple-700 ring-1 ring-inset ring-purple-200",
  revenue: "bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-200",
  expense: "bg-rose-50 text-rose-700 ring-1 ring-inset ring-rose-200",
};

export default function AccountGroups() {
  const { props } = usePage() as any;
  const raw = props?.groups;
  const groups = Array.isArray(raw) ? raw : (raw?.data || []);
  const meta = Array.isArray(raw) ? {} : (raw?.meta || {});
  const typeCounts = props?.type_counts || {};
  const summary = props?.summary || {};
  const filters = props?.filters || {};

  const [q, setQ] = React.useState(filters.q || "");
  const [type, setType] = React.useState(filters.type || "all");
  const [form, setForm] = React.useState<{ id?: number; code: string; name: string; type: string } | null>(null);
  const [errors, setErrors] = React.useState<Record<string, string>>({});

  const apply = (next?: any) => {
    const p = { q, type, ...next };
    router.get("/accounting/account-groups", {
      q: p.q || undefined,
      type: p.type && p.type !== "all" ? p.type : undefined,
    }, { preserveState: true, replace: true });
  };

  const openCreate = () => { setErrors({}); setForm({ code: "", name: "", type: "asset" }); };
  const openEdit = (row: any) => { setErrors({}); setForm({ id: row.id, code: row.code, name: row.name, type: row.type }); };

  const save = () => {
    if (!form) return;
    const setErr = (e: any) => { setErrors(e?.response?.data?.errors || {}); };
    if (form.id) {
      router.put(`/accounting/account-groups/${form.id}`, form, { preserveScroll: true, onSuccess: () => setForm(null), onError: setErr });
    } else {
      router.post("/accounting/account-groups", form, { preserveScroll: true, onSuccess: () => setForm(null), onError: setErr });
    }
  };

  const remove = (row: any) => {
    if (Number(row.accounts_count) > 0) {
      window.alert(`Cannot delete group. It has ${row.accounts_count} assigned account(s).`);
      return;
    }
    if (!window.confirm(`Delete account group ${row.code} — ${row.name}?`)) return;
    router.delete(`/accounting/account-groups/${row.id}`, { preserveScroll: true });
  };

  return (
    <AppLayout title="Account Groups">
      <PageHeader
        title="Account Groups"
        subtitle="Organize chart of accounts into reporting and classification groups"
        breadcrumbs={[{ label: "Accounting", href: "/accounting" }, { label: "Account Groups" }]}
      >
        <Button onClick={openCreate}><Plus size={16} /> New Group</Button>
      </PageHeader>

      <div className="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
        <Card><CardHeader className="p-4 pb-2"><CardTitle className="text-xs font-medium text-slate-500">Total Groups</CardTitle></CardHeader><CardContent className="p-4 pt-0"><p className="text-xl font-semibold text-slate-900 tabular-nums">{summary.total ?? 0}</p></CardContent></Card>
        {["asset", "liability", "equity", "revenue"].map((t) => (
          <Card key={t}><CardHeader className="p-4 pb-2"><CardTitle className="text-xs font-medium text-slate-500 capitalize">{t}</CardTitle></CardHeader><CardContent className="p-4 pt-0"><p className="text-xl font-semibold text-slate-900 tabular-nums">{typeCounts[t] || 0}</p></CardContent></Card>
        ))}
      </div>

      {form && (
        <Card className="mb-6">
          <CardHeader><CardTitle className="text-base font-semibold text-slate-900">{form.id ? "Edit Group" : "New Account Group"}</CardTitle></CardHeader>
          <CardContent className="space-y-3">
            <div className="grid grid-cols-1 md:grid-cols-3 gap-3">
              <div>
                <label className="mb-1 block text-xs font-semibold uppercase tracking-wider text-slate-500">Code</label>
                <input value={form.code} onChange={(e) => setForm({ ...form, code: e.target.value })} className="h-10 w-full rounded-lg border border-slate-200 px-3 text-sm" />
                {errors.code && <p className="text-xs text-rose-600 mt-1">{errors.code}</p>}
              </div>
              <div>
                <label className="mb-1 block text-xs font-semibold uppercase tracking-wider text-slate-500">Name</label>
                <input value={form.name} onChange={(e) => setForm({ ...form, name: e.target.value })} className="h-10 w-full rounded-lg border border-slate-200 px-3 text-sm" />
                {errors.name && <p className="text-xs text-rose-600 mt-1">{errors.name}</p>}
              </div>
              <div>
                <label className="mb-1 block text-xs font-semibold uppercase tracking-wider text-slate-500">Type</label>
                <select value={form.type} onChange={(e) => setForm({ ...form, type: e.target.value })} className="h-10 w-full rounded-lg border border-slate-200 px-3 text-sm">
                  <option value="asset">Asset</option><option value="liability">Liability</option><option value="equity">Equity</option><option value="revenue">Revenue</option><option value="expense">Expense</option>
                </select>
                {errors.type && <p className="text-xs text-rose-600 mt-1">{errors.type}</p>}
              </div>
            </div>
            <div className="flex gap-2 justify-end">
              <Button variant="ghost" onClick={() => setForm(null)}>Cancel</Button>
              <Button onClick={save}>{form.id ? "Save Changes" : "Create Group"}</Button>
            </div>
          </CardContent>
        </Card>
      )}

      <div className="mb-6 rounded-xl border border-slate-200 bg-white p-4 shadow-sm flex flex-col gap-3 lg:flex-row lg:items-end">
        <div className="flex-1">
          <label className="mb-1 block text-xs font-semibold uppercase tracking-wider text-slate-500">Search</label>
          <div className="relative"><Search size={15} className="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" /><input value={q} onChange={e=>setQ(e.target.value)} onKeyDown={e=>e.key==='Enter'&&apply({q})} placeholder="Code or name…" className="h-10 w-full rounded-lg border border-slate-200 bg-white pl-9 pr-3 text-sm" /></div>
        </div>
        <div className="w-full lg:w-48">
          <label className="mb-1 block text-xs font-semibold uppercase tracking-wider text-slate-500">Type</label>
          <select value={type} onChange={e=>{setType(e.target.value); apply({type:e.target.value});}} className="h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm"><option value="all">All types</option><option value="asset">Asset</option><option value="liability">Liability</option><option value="equity">Equity</option><option value="revenue">Revenue</option><option value="expense">Expense</option></select>
        </div>
        <div className="flex gap-2"><Button variant="outline" onClick={()=>apply()}><Filter size={14}/> Apply</Button><Button variant="ghost" onClick={()=>{setQ('');setType('all');router.get('/accounting/account-groups', {}, {preserveState:true, replace:true});}}>Reset</Button></div>
      </div>

      <DataTable
        title="Account Groups"
        columns={[
          { key: "code", label: "Code" },
          { key: "name", label: "Name" },
          { key: "type", label: "Type", format: (v: string) => <Badge className={TYPE_BADGE[v] || "bg-slate-100 text-slate-600"}>{v}</Badge> },
          { key: "accounts_count", label: "Accounts", align: "right" },
          { key: "posted_balance", label: "Posted Balance", align: "right", format: (v: number) => {
            const n = Number(v || 0);
            return <span className={`tabular-nums font-medium ${n > 0 ? "text-emerald-700" : n < 0 ? "text-rose-700" : "text-slate-500"}`}>{n.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</span>;
          }},
        ]}
        data={groups}
        meta={meta}
        rowActions={[
          { label: "Edit", icon: <Edit size={14} />, variant: "outline", onClick: (row: any) => openEdit(row) },
          { label: "Delete", icon: <Trash2 size={14} />, variant: "ghost", onClick: (row: any) => remove(row) },
        ]}
      />
    </AppLayout>
  );
}
