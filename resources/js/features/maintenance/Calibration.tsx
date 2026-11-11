/**
 * Brixel FactoryOS — Maintenance Calibration
 * Calibration management and scheduling.
 */

import { Head, Link, router, usePage } from "@inertiajs/react";
import { AppLayout } from "@/components/layout";
import { PageHeader } from "@/components/layout";
import { DataTable } from "@/components/DataTable";
import { StatStrip } from "@/components/StatStrip";
import { Plus, Activity, Clock } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";

interface Calibration {
  id: number;
  tool_code: string;
  tool_name: string;
  calibration_date: string;
  next_calibration_date: string;
  calibrated_by: string;
  status: string;
  certificate_number: string;
}

const STATUS_BADGE: Record<string, string> = {
  scheduled: "bg-sky-50 text-sky-700 ring-1 ring-inset ring-sky-200",
  completed: "bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-200",
  overdue: "bg-rose-50 text-rose-700 ring-1 ring-inset ring-rose-200",
  cancelled: "bg-slate-100 text-slate-500 ring-1 ring-inset ring-slate-200",
};

export default function CalibrationManagement() {
  const { props: data } = usePage() as any;
  const calibrations: Calibration[] = data?.calibrations?.data || [];
  const meta = data?.calibrations?.meta || {};

  const totalCalibrations = calibrations.length;
  const completedCalibrations = calibrations.filter((c: Calibration) => c.status === "completed").length;
  const overdueCalibrations = calibrations.filter((c: Calibration) => c.status === "overdue").length;

  const totalCalibrationsStr = String(totalCalibrations);
  const completedCalibrationsStr = String(completedCalibrations);
  const overdueCalibrationsStr = String(overdueCalibrations);

  return (
    <AppLayout title="Calibration Management">
      <PageHeader
        title="Calibration Management"
        subtitle="Tool calibration tracking and scheduling"
        breadcrumbs={[{ label: "Maintenance", href: "/maintenance" }, { label: "Calibration" }]}
        actions={
          <Link
            href="/maintenance/tools/create"
            className="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-factory-50 text-factory-600 font-medium text-sm hover:bg-factory-100 transition-colors"
          >
            <Plus size={14} /> New Tool
          </Link>
        }
      />

      <StatStrip
        items={[
          { label: "Total Calibrations", value: totalCalibrationsStr, hint: "All calibrations" },
          { label: "Completed", value: completedCalibrationsStr, hint: "Completed calibrations" },
          { label: "Overdue", value: overdueCalibrationsStr, hint: "Overdue calibrations", tone: "danger" },
        ]}
      />

      <DataTable
        title="Calibration Records"
        columns={[
          { key: "tool_code", label: "Tool Code" },
          { key: "tool_name", label: "Tool Name" },
          { key: "calibration_date", label: "Calibration Date" },
          { key: "next_calibration_date", label: "Next Calibration" },
          { key: "calibrated_by", label: "Calibrated By" },
          { key: "certificate_number", label: "Certificate #" },
          { key: "status", label: "Status", format: (value: string) => (
            <Badge className={STATUS_BADGE[value] || "bg-slate-100 text-slate-600"}>
              {value.replace(/_/g, " ")}
            </Badge>
          )},
        ]}
        data={calibrations}
        meta={meta}
        rowActions={[
          { label: "View", icon: <Activity size={14} />, variant: "ghost" },
          { label: "Edit", icon: <Clock size={14} />, variant: "outline" },
        ]}
        emptyMessage="No calibration records found."
      />
    </AppLayout>
  );
}
