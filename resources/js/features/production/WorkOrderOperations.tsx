/**
 * Brixel FactoryOS — Work Order Operations
 * Track individual operations within a work order.
 */

import { Head, Link, router, usePage } from "@inertiajs/react";
import { AppLayout } from "@/components/layout";
import { PageHeader } from "@/components/layout";
import { DataTable } from "@/components/DataTable";
import { Plus, ClipboardList, ArrowLeft } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";

interface Operation {
  id: number;
  operation_name: string;
  operation_code: string;
  work_centre_name: string;
  status: string;
  planned_start_date: string;
  planned_end_date: string;
  actual_start_date: string;
  actual_end_date: string;
}

const STATUS_BADGE: Record<string, string> = {
  draft: "bg-slate-100 text-slate-500 ring-1 ring-inset ring-slate-200",
  released: "bg-indigo-50 text-indigo-700 ring-1 ring-inset ring-indigo-200",
  in_progress: "bg-sky-50 text-sky-700 ring-1 ring-inset ring-sky-200",
  completed: "bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-200",
  delayed: "bg-rose-50 text-rose-700 ring-1 ring-inset ring-rose-200",
};

export default function WorkOrderOperations() {
  const { props: data } = usePage() as any;
  const workOrderId: number = data?.work_order_id || 0;
  const operations: Operation[] = data?.operations || [];

  return (
    <AppLayout title="Work Order Operations">
      <PageHeader
        title="Work Order Operations"
        subtitle="Track individual operations within work order #{workOrderId}"
        breadcrumbs={[
          { label: "Production", href: "/production" },
          { label: "Work Orders", href: "/production/work-orders" },
          { label: `WO #${workOrderId}` },
          { label: "Operations" },
        ]}
      >
        <Link href="/production/work-orders" className="inline-flex h-9 items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 text-sm font-medium text-slate-700 shadow-sm transition-colors hover:bg-slate-50">
          <ArrowLeft size={16} /> Back to Work Orders
        </Link>
      </PageHeader>

      <DataTable
        title="Operations"
        columns={[
          { key: "operation_code", label: "Operation Code" },
          { key: "operation_name", label: "Operation Name" },
          { key: "work_centre_name", label: "Work Centre" },
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
        data={operations}
        rowActions={[
          { label: "View", icon: <ClipboardList size={14} />, variant: "ghost" },
          { label: "Edit", icon: <Plus size={14} />, variant: "outline" },
        ]}
      />
    </AppLayout>
  );
}
