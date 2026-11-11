/**
 * Brixel FactoryOS — Production Scrap Tracking
 * Production scrap and waste tracking.
 */

import { Head, Link, router, usePage } from "@inertiajs/react";
import { AppLayout } from "@/components/layout";
import { PageHeader } from "@/components/layout";
import { DataTable } from "@/components/DataTable";
import { StatStrip } from "@/components/StatStrip";
import { Plus, Trash2, Clock } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";

interface Scrap {
  id: number;
  scrap_number: string;
  item_name: string;
  quantity: number;
  scrap_date: string;
  reason: string;
  status: string;
  cost: number;
}

const STATUS_BADGE: Record<string, string> = {
  draft: "bg-slate-100 text-slate-500 ring-1 ring-inset ring-slate-200",
  submitted: "bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-200",
  approved: "bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-200",
  disposed: "bg-indigo-50 text-indigo-700 ring-1 ring-inset ring-indigo-200",
  cancelled: "bg-rose-50 text-rose-700 ring-1 ring-inset ring-rose-200",
};

export default function ScrapTracking() {
  const { props: data } = usePage() as any;
  const scrap: Scrap[] = data?.scrap?.data || [];
  const meta = data?.scrap?.meta || {};
  const summary = data?.summary || {
    total_scrap: 0,
    total_cost: 0,
  };

  const totalScrapStr = String(summary.total_scrap || 0);
  const totalCostStr = String(summary.total_cost?.toLocaleString(undefined, { maximumFractionDigits: 2 }) || "0");

  return (
    <AppLayout title="Scrap Tracking">
      <PageHeader
        title="Scrap Tracking"
        subtitle="Production scrap and waste tracking"
        breadcrumbs={[{ label: "Production", href: "/production" }, { label: "Scrap" }]}
        actions={
          <Button onClick={() => router.visit("/production/scrap/create")}>
            <Plus size={16} /> New Scrap Entry
          </Button>
        }
      />

      <StatStrip
        items={[
          { label: "Total Scrap", value: totalScrapStr, hint: "Units scrapped" },
          { label: "Total Cost", value: totalCostStr, hint: "Scrap cost" },
        ]}
      />

      <DataTable
        title="Scrap Records"
        columns={[
          { key: "scrap_number", label: "Scrap #" },
          { key: "item_name", label: "Item" },
          { key: "quantity", label: "Quantity", align: "right" },
          { key: "scrap_date", label: "Scrap Date" },
          { key: "reason", label: "Reason" },
          { key: "cost", label: "Cost", align: "right", format: (v: number) => `Rs. ${Number(v).toLocaleString(undefined, { maximumFractionDigits: 2 })}` },
          { key: "status", label: "Status", format: (value: string) => (
            <Badge className={STATUS_BADGE[value] || "bg-slate-100 text-slate-600"}>
              {value.replace(/_/g, " ")}
            </Badge>
          )},
        ]}
        data={scrap}
        meta={meta}
        rowActions={[
          { label: "View", icon: <Trash2 size={14} />, variant: "ghost" },
          { label: "Edit", icon: <Clock size={14} />, variant: "outline" },
        ]}
        emptyMessage="No scrap records found."
      />
    </AppLayout>
  );
}
