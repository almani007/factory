/**
 * Brixel FactoryOS — Warehouses
 * Advanced warehouse management with filtering, status control, and insights.
 */

import * as React from "react";
import { router, usePage } from "@inertiajs/react";
import { AppLayout } from "@/components/layout";
import { PageHeader } from "@/components/layout";
import { DataTable } from "@/components/DataTable";
import { StatStrip } from "@/components/StatStrip";
import { Plus, Eye, Edit, Power, Search, Filter, Boxes } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";

interface Warehouse {
  id: number;
  code: string;
  name: string;
  type: string;
  location: string;
  status: string;
  factory_name?: string;
  bin_count?: number;
}

const TYPE_BADGE: Record<string, string> = {
  raw_material: "bg-blue-50 text-blue-700 ring-1 ring-inset ring-blue-200",
  finished_goods: "bg-indigo-50 text-indigo-700 ring-1 ring-inset ring-indigo-200",
  packaging: "bg-purple-50 text-purple-700 ring-1 ring-inset ring-purple-200",
  quarantine: "bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-200",
  wip: "bg-cyan-50 text-cyan-700 ring-1 ring-inset ring-cyan-200",
  general: "bg-slate-100 text-slate-700 ring-1 ring-inset ring-slate-200",
};

export default function Warehouses() {
  const { props: data } = usePage() as any;
  const warehouses: Warehouse[] = data?.warehouses?.data || [];
  const meta = data?.warehouses?.meta || {};
  const summary = data?.warehouse_summary || { total: 0, active: 0, inactive: 0, total_bins: 0, by_type: [] };
  const filters = data?.filters || {};
  const [q, setQ] = React.useState(filters.q || "");
  const [type, setType] = React.useState(filters.type || "");
  const [status, setStatus] = React.useState(filters.status || "");

  const applyFilters = (next?: Partial<{ q: string; type: string; status: string }>) => {
    const payload = { q, type, status, ...next };
    router.get("/warehouses", {
      q: payload.q || undefined,
      type: payload.type || undefined,
      status: payload.status || undefined,
    }, { preserveState: true, replace: true });
  };

  const toggle = (id: number) => {
    router.post(`/warehouses/${id}/toggle`, {}, { preserveScroll: true });
  };

  return (
    <AppLayout title="Warehouses">
      <PageHeader
        title="Warehouses"
        subtitle="Advanced warehouse master data, capacity insights, and status control"
        breadcrumbs={[{ label: "Warehouses", href: "/warehouses" }]}
      >
        <div className="flex items-center gap-2">
          <a href="/warehouses/export" className="inline-flex h-9 items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 text-sm font-medium text-slate-700 shadow-sm transition-colors hover:bg-slate-50">Export CSV</a>
          <Button onClick={() => router.visit("/warehouses/create")}><Plus size={16} /> New Warehouse</Button>
        </div>
      </PageHeader>

      <StatStrip
        items={[
          { label: "Total Warehouses", value: summary.total ?? 0, hint: "All locations" },
          { label: "Active", value: summary.active ?? 0, hint: "Operational", tone: "success" },
          { label: "Inactive", value: summary.inactive ?? Math.max(0, (summary.total ?? 0) - (summary.active ?? 0)), hint: "Disabled" },
          { label: "Storage Bins", value: summary.total_bins ?? 0, hint: "Configured bins" },
        ]}
      />

      <div className="mb-6 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
        <div className="flex flex-col gap-3 lg:flex-row lg:items-end">
          <div className="flex-1">
            <label className="mb-1 block text-xs font-semibold uppercase tracking-wider text-slate-500">Search</label>
            <div className="relative">
              <Search size={15} className="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" />
              <input
                value={q}
                onChange={(e) => setQ(e.target.value)}
                onKeyDown={(e) => e.key === "Enter" && applyFilters({ q })}
                placeholder="Search by code, name, type, location, factory…"
                className="h-10 w-full rounded-lg border border-slate-200 bg-white pl-9 pr-3 text-sm focus:border-factory-500 focus:outline-none focus:ring-1 focus:ring-factory-500"
              />
            </div>
          </div>
          <div className="w-full lg:w-52">
            <label className="mb-1 block text-xs font-semibold uppercase tracking-wider text-slate-500">Type</label>
            <select value={type} onChange={(e) => { setType(e.target.value); applyFilters({ type: e.target.value }); }} className="h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm focus:border-factory-500 focus:outline-none focus:ring-1 focus:ring-factory-500">
              <option value="">All types</option>
              <option value="raw_material">Raw Material</option>
              <option value="finished_goods">Finished Goods</option>
              <option value="packaging">Packaging</option>
              <option value="quarantine">Quarantine</option>
              <option value="wip">WIP</option>
              <option value="general">General</option>
            </select>
          </div>
          <div className="w-full lg:w-44">
            <label className="mb-1 block text-xs font-semibold uppercase tracking-wider text-slate-500">Status</label>
            <select value={status} onChange={(e) => { setStatus(e.target.value); applyFilters({ status: e.target.value }); }} className="h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm focus:border-factory-500 focus:outline-none focus:ring-1 focus:ring-factory-500">
              <option value="">All</option>
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
            </select>
          </div>
          <div className="flex gap-2">
            <Button variant="outline" onClick={() => applyFilters()}><Filter size={14} /> Apply</Button>
            <Button variant="ghost" onClick={() => { setQ(""); setType(""); setStatus(""); router.get("/warehouses", {}, { preserveState: true, replace: true }); }}>Reset</Button>
          </div>
        </div>
        {Array.isArray(summary.by_type) && summary.by_type.length > 0 && (
          <div className="mt-4 flex flex-wrap gap-2 border-t border-slate-100 pt-4">
            {summary.by_type.map((t: any) => (
              <button key={t.type} onClick={() => { setType(t.type); applyFilters({ type: t.type }); }} className="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-xs font-medium text-slate-700 hover:bg-slate-100">
                <Boxes size={12} /> {String(t.type || 'general').replace(/_/g, ' ')} <span className="rounded-full bg-white px-1.5 py-0.5 text-slate-900 ring-1 ring-slate-200">{t.count}</span>
              </button>
            ))}
          </div>
        )}
      </div>

      <DataTable
        title="Warehouses"
        columns={[
          { key: "code", label: "Code" },
          { key: "name", label: "Name" },
          { key: "type", label: "Type", format: (value: string) => (
            <Badge className={TYPE_BADGE[value] || "bg-slate-100 text-slate-600"}>{String(value || 'general').replace(/_/g, " ")}</Badge>
          )},
          { key: "location", label: "Location" },
          { key: "factory_name", label: "Factory", format: (v: string) => v || "—" },
          { key: "bin_count", label: "Bins", format: (v: number) => String(v ?? 0) },
          { key: "status", label: "Status", format: (value: string) => (
            <Badge className={value === "active" ? "bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-200" : "bg-slate-100 text-slate-500 ring-1 ring-inset ring-slate-200"}>
              {value}
            </Badge>
          )},
        ]}
        data={warehouses}
        meta={meta}
        rowActions={[
          { label: "Edit", icon: <Edit size={14} />, variant: "outline", onClick: (row: any) => router.visit(`/warehouses/${row.id}/edit`) },
          { label: "Toggle", icon: <Power size={14} />, variant: "ghost", onClick: (row: any) => toggle(row.id) },
        ]}
      />
    </AppLayout>
  );
}
