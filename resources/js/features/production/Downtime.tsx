/**
 * Brixel FactoryOS — Production Downtime Tracking
 * Production downtime tracking and analysis.
 */

import { Head, Link, router, usePage } from "@inertiajs/react";
import { AppLayout } from "@/components/layout";
import { PageHeader } from "@/components/layout";
import { DataTable } from "@/components/DataTable";
import { StatStrip } from "@/components/StatStrip";
import { Plus, Activity, Clock } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";

interface Downtime {
  id: number;
  downtime_number: string;
  machine_name: string;
  downtime_type: string;
  start_time: string;
  end_time: string;
  duration_minutes: number;
  reason: string;
  status: string;
}

const STATUS_BADGE: Record<string, string> = {
  scheduled: "bg-sky-50 text-sky-700 ring-1 ring-inset ring-sky-200",
  in_progress: "bg-rose-50 text-rose-700 ring-1 ring-inset ring-rose-200",
  completed: "bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-200",
  cancelled: "bg-slate-100 text-slate-500 ring-1 ring-inset ring-slate-200",
};

export default function DowntimeTracking() {
  const { props: data } = usePage() as any;
  const downtime = data?.downtime?.data || [];
  const meta = data?.downtime?.meta || {};
  const summary = data?.summary || {
    total_downtime: 0,
    total_incidents: 0,
  };

  return (
    <AppLayout title="Downtime Tracking">
      <PageHeader
        title="Downtime Tracking"
        subtitle="Production downtime tracking and analysis"
        breadcrumbs={[{ label: "Production", href: "/production" }, { label: "Downtime" }]}
      >
        <Button onClick={() => router.visit("/production/downtime/create")}>
          <Plus size={16} /> New Downtime Entry
        </Button>
      </PageHeader>

      <StatStrip
        items={[
          { label: "Total Downtime", value: summary.total_downtime.toLocaleString?.() || "0", hint: "Minutes of downtime" },
          { label: "Total Incidents", value: summary.total_incidents.toLocaleString?.() || "0", hint: "Downtime incidents" },
        ]}
      />

      <DataTable
        title="Downtime Records"
        columns={[
          { key: "downtime_number", label: "Downtime #" },
          { key: "machine_name", label: "Machine" },
          { key: "downtime_type", label: "Type", format: (value: string) => value.replace(/_/g, " ") },
          { key: "start_time", label: "Start Time" },
          { key: "end_time", label: "End Time" },
          { key: "duration_minutes", label: "Duration (min)", align: "right" },
          { key: "reason", label: "Reason" },
          { key: "status", label: "Status", format: (value: string) => (
            <Badge className={STATUS_BADGE[value] || "bg-slate-100 text-slate-600"}>
              {value.replace(/_/g, " ")}
            </Badge>
          )},
        ]}
        data={downtime}
        meta={meta}
        rowActions={[
          { label: "View", icon: <Activity size={14} />, variant: "ghost" },
          { label: "Edit", icon: <Clock size={14} />, variant: "outline" },
        ]}
        emptyMessage="No downtime records found."
      />
    </AppLayout>
  );
}
