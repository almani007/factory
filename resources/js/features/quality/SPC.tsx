/**
 * Brixel FactoryOS — SPC Control Charts
 * Statistical Process Control — monitor quality trends and variation.
 */

import { Head, Link, router, usePage } from "@inertiajs/react";
import { AppLayout } from "@/components/layout";
import { PageHeader } from "@/components/layout";
import { DataTable } from "@/components/DataTable";
import { StatStrip } from "@/components/StatStrip";
import { Plus, ShieldAlert, Activity } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";

interface ControlChart {
  id: number;
  chart_name: string;
  chart_type: string;
  item_name: string;
  upper_control_limit: number;
  lower_control_limit: number;
  target_value: number;
  status: string;
  created_at: string;
}

const CHART_TYPE_BADGE: Record<string, string> = {
  xbar_r: "bg-blue-50 text-blue-700 ring-1 ring-inset ring-blue-200",
  p_chart: "bg-indigo-50 text-indigo-700 ring-1 ring-inset ring-indigo-200",
  c_chart: "bg-purple-50 text-purple-700 ring-1 ring-inset ring-purple-200",
  u_chart: "bg-pink-50 text-pink-700 ring-1 ring-inset ring-pink-200",
};

const STATUS_BADGE: Record<string, string> = {
  active: "bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-200",
  out_of_control: "bg-rose-50 text-rose-700 ring-1 ring-inset ring-rose-200",
  warning: "bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-200",
  inactive: "bg-slate-100 text-slate-500 ring-1 ring-inset ring-slate-200",
};

export default function SPC() {
  const { props: data } = usePage() as any;
  const charts: ControlChart[] = data?.control_charts?.data || [];
  const meta = data?.control_charts?.meta || {};
  const summary = data?.summary || {
    total_charts: 0,
    active_charts: 0,
    out_of_control: 0,
  };

  return (
    <AppLayout title="SPC Control Charts">
      <PageHeader
        title="Statistical Process Control"
        subtitle="Monitor quality trends, variation, and process stability"
        breadcrumbs={[{ label: "Quality", href: "/quality" }, { label: "SPC Charts" }]}
      >
        <Button onClick={() => router.visit("/quality/inspections/create")}>
          <Plus size={16} /> New Control Chart
        </Button>
      </PageHeader>

      <StatStrip
        items={[
          { label: "Total Charts", value: summary.total_charts, hint: "All control charts" },
          { label: "Active", value: summary.active_charts, hint: "In use", tone: "success" },
          { label: "Out of Control", value: summary.out_of_control, hint: "Needs investigation", tone: summary.out_of_control > 0 ? "danger" : "success" },
        ]}
      />

      <DataTable
        title="Control Charts"
        columns={[
          { key: "chart_name", label: "Chart Name" },
          { key: "chart_type", label: "Chart Type", format: (value: string) => (
            <Badge className={CHART_TYPE_BADGE[value] || "bg-slate-100 text-slate-600"}>
              {value.replace(/_/g, " ").toUpperCase()}
            </Badge>
          )},
          { key: "item_name", label: "Item" },
          { key: "upper_control_limit", label: "UCL", align: "right", format: (v: number) => Number(v).toFixed(2) },
          { key: "lower_control_limit", label: "LCL", align: "right", format: (v: number) => Number(v).toFixed(2) },
          { key: "target_value", label: "Target", align: "right", format: (v: number) => Number(v).toFixed(2) },
          { key: "status", label: "Status", format: (value: string) => (
            <Badge className={STATUS_BADGE[value] || "bg-slate-100 text-slate-600"}>
              {value.replace(/_/g, " ")}
            </Badge>
          )},
        ]}
        data={charts}
        meta={meta}
        rowActions={[
          { label: "View", icon: <Activity size={14} />, variant: "ghost", href: (row: any) => `/quality/spc/chart/${row.id}` },
          { label: "Edit", icon: <ShieldAlert size={14} />, variant: "outline" },
        ]}
      />
    </AppLayout>
  );
}
