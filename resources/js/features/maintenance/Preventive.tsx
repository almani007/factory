/**
 * Brixel FactoryOS — Maintenance — Preventive Maintenance
 * Scheduled maintenance planning and tracking.
 */

import { Head, Link, router, usePage } from "@inertiajs/react";
import { AppLayout } from "@/components/layout";
import { PageHeader } from "@/components/layout";
import { DataTable } from "@/components/DataTable";
import { StatStrip } from "@/components/StatStrip";
import { Plus, Eye, Edit, RotateCcw } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";

interface PM {
  id: number;
  title: string;
  machine_name: string;
  schedule_type: string;
  next_due_date: string;
  last_done_date: string;
  status: string;
  status_label: string;
  assigned_to: string;
}

const STATUS_BADGE: Record<string, string> = {
  overdue: "bg-rose-50 text-rose-700 ring-1 ring-inset ring-rose-200",
  due_soon: "bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-200",
  completed: "bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-200",
  scheduled: "bg-sky-50 text-sky-700 ring-1 ring-inset ring-sky-200",
};

export default function PreventiveMaintenance() {
  const { props: data } = usePage() as any;
  const maintenance: PM[] = data?.maintenance?.data || [];
  const meta = data?.maintenance?.meta || {};
  const summary = data?.pm_summary || { total: 0, overdue: 0, due_soon: 0 };

  const totalCalibrations = String(summary.total || 0);
  const completedCalibrations = String(summary.completed || 0);
  const overdueCalibrations = String(summary.overdue || 0);

  return (
    <AppLayout title="Preventive Maintenance">
      <PageHeader
        title="Preventive Maintenance"
        subtitle="Scheduled maintenance planning and tracking"
        breadcrumbs={[{ label: "Maintenance", href: "/maintenance" }, { label: "Preventive" }]}
        actions={
          <Link
            href="/maintenance/preventive/create"
            className="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-factory-50 text-factory-600 font-medium text-sm hover:bg-factory-100 transition-colors"
          >
            <Plus size={14} /> New PM Schedule
          </Link>
        }
      />

      <StatStrip
        items={[
          { label: "Total Calibrations", value: totalCalibrationsStr, hint: "All calibrations" },
          { label: "Completed", value: completedCalibrationsStr, hint: "Completed calibrations", tone: "success" },
          { label: "Overdue", value: overdueCalibrationsStr, hint: "Overdue calibrations", tone: summary.overdue > 0 ? "danger" : "success" },
        ]}
      />

      <DataTable
        title="Preventive Maintenance"
        columns={[
          { key: "title", label: "Title" },
          { key: "machine_name", label: "Machine" },
          { key: "schedule_type", label: "Schedule Type", format: (value: string) => value.replace(/_/g, " ") },
          { key: "next_due_date", label: "Next Due Date" },
          { key: "last_done_date", label: "Last Done Date" },
          { key: "status", label: "Status", format: (value: string) => (
            <Badge className={STATUS_BADGE[value] || "bg-slate-100 text-slate-600"}>
              {value.replace(/_/g, " ")}
            </Badge>
          )},
          { key: "assigned_to", label: "Assigned To" },
        ]}
        data={calibrations}
        meta={meta}
        rowActions={[
          { label: "View", icon: <Eye size={14} />, variant: "ghost" },
          { label: "Edit", icon: <Edit size={14} />, variant: "outline" },
        ]}
        emptyMessage="No calibration records found."
      />
    </AppLayout>
  );
}
