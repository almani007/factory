/**
 * Brixel FactoryOS — Workflow Approvals
 * Advanced approval inbox with filtering and approve/reject actions.
 */

import * as React from "react";
import { router, usePage } from "@inertiajs/react";
import { AppLayout } from "@/components/layout";
import { PageHeader } from "@/components/layout";
import { DataTable } from "@/components/DataTable";
import { StatStrip } from "@/components/StatStrip";
import { Plus, Check, X, Search, Filter } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";

const STATUS_BADGE: Record<string, string> = {
  pending: "bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-200",
  approved: "bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-200",
  rejected: "bg-rose-50 text-rose-700 ring-1 ring-inset ring-rose-200",
  cancelled: "bg-slate-100 text-slate-500 ring-1 ring-inset ring-slate-200",
};

export default function Approvals() {
  const { props: data } = usePage() as any;
  const raw = data?.approvals;
  const approvals = Array.isArray(raw) ? raw : (raw?.data || []);
  const meta = Array.isArray(raw) ? {} : (raw?.meta || {});
  const summary = data?.summary || { total_approvals: 0, pending_approvals: 0, approved: 0, rejected: 0 };
  const filters = data?.filters || {};
  const [q, setQ] = React.useState(filters.q || "");
  const [status, setStatus] = React.useState(filters.status || "all");

  const apply = (next?: any) => {
    const p = { q, status, ...next };
    router.get('/workflow/approvals', { q: p.q || undefined, status: p.status !== 'all' ? p.status : undefined }, { preserveState: true, replace: true });
  };
  const act = (id: number, type: 'approve' | 'reject') => {
    const comments = window.prompt(type === 'approve' ? 'Approval comments (optional):' : 'Rejection reason/comments (optional):') || '';
    router.post(`/workflow/approvals/${id}/${type}`, { comments }, { preserveScroll: true });
  };

  return (
    <AppLayout title="Workflow Approvals">
      <PageHeader title="Workflow Approvals" subtitle="Approval inbox with actions, filters, and tracking" breadcrumbs={[{ label: "Workflow", href: "/workflow/approvals" }, { label: "Approvals" }]}>
        <Button onClick={() => router.visit("/workflow/rules")}><Plus size={16} /> Approval Rules</Button>
      </PageHeader>

      <StatStrip items={[
        { label: "Total", value: summary.total_approvals, hint: "All requests" },
        { label: "Pending", value: summary.pending_approvals, hint: "Needs action", tone: summary.pending_approvals > 0 ? "warning" : "success" },
        { label: "Approved", value: summary.approved, hint: "Approved", tone: "success" },
        { label: "Rejected", value: summary.rejected, hint: "Rejected", tone: "danger" },
      ]} />

      <div className="mb-6 rounded-xl border border-slate-200 bg-white p-4 shadow-sm flex flex-col gap-3 lg:flex-row lg:items-end">
        <div className="flex-1">
          <label className="mb-1 block text-xs font-semibold uppercase tracking-wider text-slate-500">Search</label>
          <div className="relative"><Search size={15} className="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" /><input value={q} onChange={e=>setQ(e.target.value)} onKeyDown={e=>e.key==='Enter'&&apply({q})} placeholder="Type, reference, approver, comments…" className="h-10 w-full rounded-lg border border-slate-200 bg-white pl-9 pr-3 text-sm focus:border-factory-500 focus:outline-none focus:ring-1 focus:ring-factory-500" /></div>
        </div>
        <div className="w-full lg:w-44">
          <label className="mb-1 block text-xs font-semibold uppercase tracking-wider text-slate-500">Status</label>
          <select value={status} onChange={e=>{setStatus(e.target.value); apply({status:e.target.value});}} className="h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm focus:border-factory-500 focus:outline-none focus:ring-1 focus:ring-factory-500"><option value="all">All</option><option value="pending">Pending</option><option value="approved">Approved</option><option value="rejected">Rejected</option><option value="cancelled">Cancelled</option></select>
        </div>
        <div className="flex gap-2"><Button variant="outline" onClick={()=>apply()}><Filter size={14}/> Apply</Button><Button variant="ghost" onClick={()=>{setQ('');setStatus('all');router.get('/workflow/approvals', {}, {preserveState:true, replace:true});}}>Reset</Button></div>
      </div>

      <DataTable
        title="Approval Requests"
        columns={[
          { key: "workflow_type", label: "Workflow", format: (value: string) => String(value||'').replace(/_/g, " ") },
          { key: "reference_number", label: "Reference" },
          { key: "status", label: "Status", format: (value: string) => (<Badge className={STATUS_BADGE[value] || "bg-slate-100 text-slate-600"}>{String(value||'').replace(/_/g, " ")}</Badge>) },
          { key: "requested_at", label: "Requested" },
          { key: "approved_at", label: "Decided" },
          { key: "approver_name", label: "Approver" },
          { key: "comments", label: "Comments" },
        ]}
        data={approvals}
        meta={meta}
        rowActions={[
          { label: "Approve", icon: <Check size={14} />, variant: "outline", onClick: (row: any) => act(row.id, 'approve') },
          { label: "Reject", icon: <X size={14} />, variant: "ghost", onClick: (row: any) => act(row.id, 'reject') },
        ]}
      />
    </AppLayout>
  );
}
