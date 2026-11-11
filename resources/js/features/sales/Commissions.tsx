/**
 * Brixel FactoryOS — Sales Commissions
 * Sales commission tracking and payment management.
 */

import { Head, Link, router, usePage } from "@inertiajs/react";
import { AppLayout } from "@/components/layout";
import { PageHeader } from "@/components/layout";
import { DataTable } from "@/components/DataTable";
import { StatStrip } from "@/components/StatStrip";
import { Plus, Shield, Truck } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";

interface Commission {
  id: number;
  commission_period: string;
  commission_type: string;
  employee_name: string;
  base_amount: number;
  commission_rate: number;
  commission_amount: number;
  status: string;
  paid_at: string;
}

const STATUS_BADGE: Record<string, string> = {
  pending: "bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-200",
  approved: "bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-200",
  paid: "bg-indigo-50 text-indigo-700 ring-1 ring-inset ring-indigo-200",
  disputed: "bg-rose-50 text-rose-700 ring-1 ring-inset ring-rose-200",
  cancelled: "bg-slate-100 text-slate-500 ring-1 ring-inset ring-slate-200",
};

export default function Commissions() {
  const { props: data } = usePage() as any;
  const commissions: Commission[] = data?.commissions?.data || [];
  const meta = data?.commissions?.meta || {};
  const summary = data?.summary || {
    total_commissions: 0,
    total_amount: 0,
    paid_amount: 0,
  };

  return (
    <AppLayout title="Sales Commissions">
      <PageHeader
        title="Sales Commissions"
        subtitle="Sales commission tracking and payment management"
        breadcrumbs={[{ label: "Sales", href: "/sales" }, { label: "Commissions" }]}
      />

      <StatStrip
        items={[
          { label: "Total Commissions", value: summary.total_commissions, hint: "All commission records" },
          { label: "Total Amount", value: `Rs. ${summary.total_amount?.toLocaleString(undefined, { maximumFractionDigits: 0 }) || "0"}`, hint: "Total commission amount" },
          { label: "Paid Amount", value: `Rs. ${summary.paid_amount?.toLocaleString(undefined, { maximumFractionDigits: 0 }) || "0"}`, hint: "Amount paid out", tone: "success" },
        ]}
      />

      <DataTable
        title="Commissions"
        columns={[
          { key: "commission_period", label: "Period" },
          { key: "commission_type", label: "Type", format: (value: string) => value.replace(/_/g, " ") },
          { key: "employee_name", label: "Employee" },
          { key: "base_amount", label: "Base Amount", align: "right", format: (v: number) => `Rs. ${Number(v).toLocaleString(undefined, { maximumFractionDigits: 2 })}` },
          { key: "commission_rate", label: "Rate %", align: "right", format: (v: number) => `${Number(v)}%` },
          { key: "commission_amount", label: "Commission", align: "right", format: (v: number) => `Rs. ${Number(v).toLocaleString(undefined, { maximumFractionDigits: 2 })}` },
          { key: "status", label: "Status", format: (value: string) => (
            <Badge className={STATUS_BADGE[value] || "bg-slate-100 text-slate-600"}>
              {value.replace(/_/g, " ")}
            </Badge>
          )},
        ]}
        data={commissions}
        meta={meta}
        rowActions={[
          { label: "View", icon: <Truck size={14} />, variant: "ghost" },
          { label: "Edit", icon: <Shield size={14} />, variant: "outline" },
        ]}
      />
    </AppLayout>
  );
}
