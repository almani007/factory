/**
 * Brixel FactoryOS — Work Orders
 * Production work order management with completion tracking.
 */

import { Head, Link, router, usePage } from "@inertiajs/react";
import { AppLayout } from "@/components/layout";
import { PageHeader } from "@/components/layout";
import { DataTable } from "@/components/DataTable";
import { StatStrip } from "@/components/StatStrip";
import { Plus, ClipboardList } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";

interface WorkOrder {
  id: number;
  work_order_number: string;
  production_order: string;
  item_name: string;
  status: string;
  status_label: string;
  planned_start_date: string;
  planned_end_date: string;
  actual_start_date: string;
  actual_end_date: string;
  quantity: number;
  completed_quantity: number;
  completion_pct: number;
}

const STATUS_BADGE: Record<string, string> = {
  draft: "bg-slate-100 text-slate-500 ring-1 ring-inset ring-slate-200",
  released: "bg-indigo-50 text-indigo-700 ring-1 ring-inset ring-indigo-200",
  in_progress: "bg-sky-50 text-sky-700 ring-1 ring-inset ring-sky-200",
  completed: "bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-200",
  closed: "bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-200",
  delayed: "bg-rose-50 text-rose-700 ring-1 ring-inset ring-rose-200",
  cancelled: "bg-slate-100 text-slate-500 ring-1 ring-inset ring-slate-200",
};

export default function WorkOrders() {
  const { props: data } = usePage() as any;
  const workOrders: WorkOrder[] = data?.work_orders?.data || [];
  const meta = data?.work_orders?.meta || {};
  const summary = data?.wo_summary || { total: 0, in_progress: 0, completed: 0, delayed: 0 };

  return (
    <AppLayout title="Work Orders">
      <PageHeader
        title="Work Orders"
        subtitle="Production work order management with completion tracking"
        breadcrumbs={[{ label: "Production", href: "/production" }, { label: "Work Orders" }]}
      >
        <Button onClick={() => router.visit("/production/work-orders/create")}>
          <Plus size={16} /> New Work Order
        </Button>
      </PageHeader>

      <StatStrip
        items={[
          { label: "Total Work Orders", value: summary.total, hint: "All work orders" },
          { label: "In Progress", value: summary.in_progress, hint: "On the floor now", tone: "warning" },
          { label: "Completed", value: summary.completed, hint: "Finished", tone: "success" },
          { label: "Delayed", value: summary.delayed, hint: "Past due date", tone: summary.delayed > 0 ? "danger" : "success" },
        ]}
      />

      <DataTable
        title="Work Orders"
        columns={[
          { key: "work_order_number", label: "Work Order #" },
          { key: "production_order", label: "Production Order" },
          { key: "item_name", label: "Product" },
          { key: "quantity", label: "Planned Qty" },
          { key: "completed_quantity", label: "Completed Qty" },
          { key: "completion_pct", label: "Complete %", align: "right", format: (v: number) => `${v}%` },
          { key: "planned_start_date", label: "Planned Start" },
          { key: "planned_end_date", label: "Planned End" },
          { key: "status", label: "Status", format: (value: string) => (
            <Badge className={STATUS_BADGE[value] || "bg-slate-100 text-slate-600"}>
              {value.replace(/_/g, " ")}
            </Badge>
          )},
        ]}
        data={workOrders}
        meta={meta}
        rowActions={[
          { label: "View", icon: <Plus size={14} />, variant: "ghost" },
          { label: "Edit", icon: <Plus size={14} />, variant: "outline" },
        ]}
      />
    </AppLayout>
  );
}
