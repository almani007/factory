/**
 * Brixel FactoryOS — Audit Trail
 * Advanced audit trail with filtering and export.
 */

import * as React from "react";
import { router, usePage } from "@inertiajs/react";
import { AppLayout } from "@/components/layout";
import { PageHeader } from "@/components/layout";
import { DataTable } from "@/components/DataTable";
import { Shield, Search, Filter, Download } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";

const EVENT_BADGE: Record<string, string> = {
  create: "bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-200",
  update: "bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-200",
  delete: "bg-rose-50 text-rose-700 ring-1 ring-inset ring-rose-200",
  login: "bg-indigo-50 text-indigo-700 ring-1 ring-inset ring-indigo-200",
  logout: "bg-slate-50 text-slate-700 ring-1 ring-inset ring-slate-200",
  approve: "bg-purple-50 text-purple-700 ring-1 ring-inset ring-purple-200",
  reject: "bg-rose-50 text-rose-700 ring-1 ring-inset ring-rose-200",
};

export default function AuditTrail() {
  const { props: data } = usePage() as any;
  const raw = data?.audit_logs;
  const auditLogs = Array.isArray(raw) ? raw : (raw?.data || []);
  const meta = Array.isArray(raw) ? {} : (raw?.meta || {});
  const filters = data?.filters || {};
  const [q, setQ] = React.useState(filters.q || "");
  const [event, setEvent] = React.useState(filters.event || "");

  const apply = (next?: any) => {
    const p = { q, event, ...next };
    router.get('/compliance/audit-trail', { q: p.q || undefined, event: p.event || undefined }, { preserveState: true, replace: true });
  };

  return (
    <AppLayout title="Audit Trail">
      <PageHeader title="Audit Trail" subtitle="Advanced compliance audit log with filtering and export" breadcrumbs={[{ label: "Compliance", href: "/compliance/audit-trail" }, { label: "Audit Trail" }]}>
        <a href="/compliance/audit-trail/export" className="inline-flex h-9 items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50"><Download size={14}/> Export CSV</a>
      </PageHeader>

      <div className="mb-6 rounded-xl border border-slate-200 bg-white p-4 shadow-sm flex flex-col gap-3 lg:flex-row lg:items-end">
        <div className="flex-1">
          <label className="mb-1 block text-xs font-semibold uppercase tracking-wider text-slate-500">Search</label>
          <div className="relative"><Search size={15} className="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" /><input value={q} onChange={e=>setQ(e.target.value)} onKeyDown={e=>e.key==='Enter'&&apply({q})} placeholder="Event, table/entity, user…" className="h-10 w-full rounded-lg border border-slate-200 bg-white pl-9 pr-3 text-sm focus:border-factory-500 focus:outline-none focus:ring-1 focus:ring-factory-500" /></div>
        </div>
        <div className="w-full lg:w-52">
          <label className="mb-1 block text-xs font-semibold uppercase tracking-wider text-slate-500">Event</label>
          <select value={event} onChange={e=>{setEvent(e.target.value); apply({event:e.target.value});}} className="h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm focus:border-factory-500 focus:outline-none focus:ring-1 focus:ring-factory-500"><option value="">All events</option><option value="create">Create</option><option value="update">Update</option><option value="delete">Delete</option><option value="login">Login</option><option value="approve">Approve</option><option value="reject">Reject</option></select>
        </div>
        <div className="flex gap-2"><Button variant="outline" onClick={()=>apply()}><Filter size={14}/> Apply</Button><Button variant="ghost" onClick={()=>{setQ('');setEvent('');router.get('/compliance/audit-trail', {}, {preserveState:true, replace:true});}}>Reset</Button></div>
      </div>

      <DataTable
        title="Audit Log"
        columns={[
          { key: "event_type", label: "Event", format: (value: string) => (<Badge className={EVENT_BADGE[value] || "bg-slate-100 text-slate-600"}>{String(value||'').replace(/_/g, " ")}</Badge>) },
          { key: "table_name", label: "Entity/Table" },
          { key: "record_id", label: "Record ID", align: "right" },
          { key: "user_name", label: "User" },
          { key: "created_at", label: "Timestamp" },
        ]}
        data={auditLogs}
        meta={meta}
        rowActions={[{ label: "View", icon: <Shield size={14} />, variant: "ghost" }]}
      />
    </AppLayout>
  );
}
