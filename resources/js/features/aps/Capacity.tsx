/**
 * Brixel FactoryOS — APS Capacity
 * Work centre capacity planning and utilization tracking.
 */

import { Head, Link, router, usePage } from "@inertiajs/react";
import { AppLayout } from "@/components/layout";
import { PageHeader } from "@/components/layout";
import { DataTable } from "@/components/DataTable";
import { StatStrip } from "@/components/StatStrip";
import { ArrowLeft, Wrench, Activity } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";

interface CapacityCentre {
  id: number;
  code: string;
  name: string;
  capacity_per_hour: number;
  active_work_orders: number;
}

const UTILIZATION_BADGE: Record<string, string> = {
  low: "bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-200",
  medium: "bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-200",
  high: "bg-rose-50 text-rose-700 ring-1 ring-inset ring-rose-200",
};

export default function APSCapacity() {
  const { props: data } = usePage() as any;
  const centres: CapacityCentre[] = data?.capacity_data?.data || [];

  const centresWithUtilization = centres.map((c) => {
    const utilization = c.capacity_per_hour > 0
      ? Math.min(100, Math.round((c.active_work_orders / c.capacity_per_hour) * 100))
      : 0;
    const level = utilization < 50 ? "low" : utilization < 80 ? "medium" : "high";
    return { ...c, utilization, level };
  });

  return (
    <AppLayout title="Capacity Planning">
      <PageHeader
        title="Capacity Planning"
        subtitle="Work centre capacity utilization and workload balancing"
        breadcrumbs={[{ label: "Operations", href: "/aps" }, { label: "APS Planning" }, { label: "Capacity" }]}
        actions={
          <Link href="/aps" className="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-slate-200 bg-white px-3 text-sm font-medium text-slate-700 shadow-sm transition-colors hover:bg-slate-50">
            <ArrowLeft size={14} /> Back to Planning
          </Link>
        }
      />

      <StatStrip
        items={[
          { label: "Total Work Centres", value: centres.length, hint: "All active centres" },
          { label: "Avg Utilization", value: centres.length > 0 ? `${Math.round(centresWithUtilization.reduce((sum, c) => sum + c.utilization, 0) / centres.length)}%` : "0%", hint: "Average across centres" },
          { label: "Overloaded", value: centresWithUtilization.filter((c) => c.level === "high").length, hint: "Above 80% capacity", tone: "danger" },
        ]}
      />

      <DataTable
        title="Work Centre Capacity"
        columns={[
          { key: "code", label: "Centre Code" },
          { key: "name", label: "Work Centre Name" },
          { key: "capacity_per_hour", label: "Capacity/Hr" },
          { key: "active_work_orders", label: "Active Orders" },
          { key: "utilization", label: "Utilization", align: "right", format: (value: number) => `${value}%` },
          { key: "level", label: "Load Level", format: (value: string) => (
            <Badge className={UTILIZATION_BADGE[value] || "bg-slate-100 text-slate-600"}>
              {value}
            </Badge>
          )},
        ]}
        data={centresWithUtilization}
        rowActions={[
          { label: "View", icon: <Activity size={14} />, variant: "ghost" },
          { label: "Edit", icon: <Wrench size={14} />, variant: "outline" },
        ]}
      />
    </AppLayout>
  );
}
