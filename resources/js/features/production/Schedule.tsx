/**
 * Brixel FactoryOS — Production Schedule
 * Production scheduling and Gantt-style order tracking.
 */

import { Head, Link, router, usePage } from "@inertiajs/react";
import { AppLayout } from "@/components/layout";
import { PageHeader } from "@/components/layout";
import { DataTable } from "@/components/DataTable";
import { Plus, GanttChart } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";

interface ScheduleOrder {
  id: number;
  order_number: string;
  item_name: string;
  planned_start_date: string;
  planned_end_date: string;
  actual_start_date: string;
  actual_end_date: string;
  status: string;
  status_label: string;
  planned_quantity: number;
}

const STATUS_BADGE: Record<string, string> = {
  draft: "bg-slate-100 text-slate-500 ring-1 ring-inset ring-slate-200",
  pending_approval: "bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-200",
  approved: "bg-indigo-50 text-indigo-700 ring-1 ring-inset ring-indigo-200",
  material_reserved: "bg-indigo-50 text-indigo-700 ring-1 ring-inset ring-indigo-200",
  in_production: "bg-sky-50 text-sky-700 ring-1 ring-inset ring-sky-200",
  quality_inspection: "bg-indigo-50 text-indigo-700 ring-1 ring-inset ring-indigo-200",
  completed: "bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-200",
  closed: "bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-200",
  cancelled: "bg-rose-50 text-rose-700 ring-1 ring-inset ring-rose-200",
};

export default function ProductionSchedule() {
  const { props: data } = usePage() as any;
  const orders: ScheduleOrder[] = data?.orders?.data || [];
  const meta = data?.orders?.meta || {};

  return (
    <AppLayout title="Production Schedule">
      <PageHeader
        title="Production Schedule"
        subtitle="Production scheduling and Gantt-style order tracking"
        breadcrumbs={[{ label: "Production", href: "/production" }, { label: "Schedule" }]}
      />
      <DataTable
        title="Scheduled Production Orders"
        columns={[
          { key: "order_number", label: "Order #" },
          { key: "item_name", label: "Product" },
          { key: "planned_quantity", label: "Planned Qty" },
          { key: "planned_start_date", label: "Planned Start" },
          { key: "planned_end_date", label: "Planned End" },
          { key: "actual_start_date", label: "Actual Start" },
          { key: "actual_end_date", label: "Actual End" },
          { key: "status", label: "Status", format: (value: string) => (
            <Badge className={STATUS_BADGE[value] || "bg-slate-100 text-slate-600"}>
              {value.replace(/_/g, " ")}
            </Badge>
          )},
        ]}
        data={orders}
        meta={meta}
        rowActions={[
          { label: "View", icon: <Plus size={14} />, variant: "ghost" },
          { label: "Edit", icon: <Plus size={14} />, variant: "outline" },
        ]}
      />
    </AppLayout>
  );
}
