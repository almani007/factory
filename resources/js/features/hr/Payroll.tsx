/**
 * Brixel FactoryOS — Payroll
 * Payroll run management and employee compensation tracking.
 */

import { Head, Link, router, usePage } from "@inertiajs/react";
import { AppLayout } from "@/components/layout";
import { PageHeader } from "@/components/layout";
import { DataTable } from "@/components/DataTable";
import { StatStrip } from "@/components/StatStrip";
import { Plus, Clock, Users } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";

interface PayrollRun {
  id: number;
  run_name: string;
  pay_period_start: string;
  pay_period_end: string;
  status: string;
  total_gross: number;
  total_net: number;
  created_at: string;
}

const STATUS_BADGE: Record<string, string> = {
  draft: "bg-slate-100 text-slate-500 ring-1 ring-inset ring-slate-200",
  processing: "bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-200",
  completed: "bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-200",
  paid: "bg-indigo-50 text-indigo-700 ring-1 ring-inset ring-indigo-200",
  cancelled: "bg-rose-50 text-rose-700 ring-1 ring-inset ring-rose-200",
};

export default function Payroll() {
  const { props: data } = usePage() as any;
  const runs: PayrollRun[] = data?.payroll_runs?.data || [];
  const meta = data?.payroll_runs?.meta || {};
  const summary = data?.summary || {
    total_runs: 0,
    completed_runs: 0,
    total_net_pay: 0,
  };

  return (
    <AppLayout title="Payroll">
      <PageHeader
        title="Payroll Management"
        subtitle="Payroll runs, employee compensation, and payment processing"
        breadcrumbs={[{ label: "HR", href: "/hr" }, { label: "Payroll" }]}
      >
        <Button onClick={() => router.visit("/hr/payroll/create")}>
          <Plus size={16} /> New Payroll Run
        </Button>
      </PageHeader>

      <StatStrip
        items={[
          { label: "Total Runs", value: summary.total_runs, hint: "All payroll runs" },
          { label: "Completed", value: summary.completed_runs, hint: "Processed runs", tone: "success" },
          { label: "Total Net Pay", value: `Rs. ${summary.total_net_pay?.toLocaleString(undefined, { maximumFractionDigits: 0 }) || "0"}`, hint: "Net amount paid" },
        ]}
      />

      <DataTable
        title="Payroll Runs"
        columns={[
          { key: "run_name", label: "Run Name" },
          { key: "pay_period_start", label: "Period Start" },
          { key: "pay_period_end", label: "Period End" },
          { key: "total_gross", label: "Gross Pay", align: "right", format: (v: number) => `Rs. ${Number(v).toLocaleString(undefined, { maximumFractionDigits: 2 })}` },
          { key: "total_net", label: "Net Pay", align: "right", format: (v: number) => `Rs. ${Number(v).toLocaleString(undefined, { maximumFractionDigits: 2 })}` },
          { key: "status", label: "Status", format: (value: string) => (
            <Badge className={STATUS_BADGE[value] || "bg-slate-100 text-slate-600"}>
              {value.replace(/_/g, " ")}
            </Badge>
          )},
        ]}
        data={runs}
        meta={meta}
        rowActions={[
          { label: "View", icon: <Users size={14} />, variant: "ghost" },
          { label: "Edit", icon: <Clock size={14} />, variant: "outline" },
        ]}
      />
    </AppLayout>
  );
}
