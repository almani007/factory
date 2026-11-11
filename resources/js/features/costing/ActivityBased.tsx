/**
 * Brixel FactoryOS — Activity-Based Costing
 * Activity-based costing for accurate product cost allocation.
 */

import { Head, Link, router, usePage } from "@inertiajs/react";
import { AppLayout } from "@/components/layout";
import { PageHeader } from "@/components/layout";
import { DataTable } from "@/components/DataTable";
import { StatStrip } from "@/components/StatStrip";
import { Plus, Shield, Activity } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";

interface Activity {
  id: number;
  activity_name: string;
  activity_type: string;
  total_cost: number;
  allocation_base: string;
  allocation_rate: number;
  status: string;
  created_at: string;
}

const STATUS_BADGE: Record<string, string> = {
  active: "bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-200",
  inactive: "bg-slate-100 text-slate-500 ring-1 ring-inset ring-slate-200",
  under_review: "bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-200",
};

export default function ActivityBasedCosting() {
  const { props: data } = usePage() as any;
  const activities = data?.activities?.data || [];
  const summary = data?.summary || {
    total_activities: 0,
    total_cost: 0,
  };

  return (
    <AppLayout title="Activity-Based Costing">
      <PageHeader
        title="Activity-Based Costing"
        subtitle="Activity-based costing for accurate product cost allocation"
        breadcrumbs={[{ label: "Costing", href: "/costing" }, { label: "Activity-Based" }]}
      >
        <Button onClick={() => router.visit("/costing/create")}>
          <Plus size={16} /> New Activity Center
        </Button>
      </PageHeader>

      <StatStrip
        items={[
          { label: "Total Activities", value: summary.total_activities, hint: "All activity centers" },
          { label: "Total Cost", value: `Rs. ${summary.total_cost?.toLocaleString(undefined, { maximumFractionDigits: 0 }) || "0"}`, hint: "Total activity cost" },
        ]}
      />

      <DataTable
        title="Activity Centers"
        columns={[
          { key: "activity_name", label: "Activity Name" },
          { key: "activity_type", label: "Type", format: (value: string) => value.replace(/_/g, " ") },
          { key: "total_cost", label: "Total Cost", align: "right", format: (v: number) => `Rs. ${Number(v).toLocaleString(undefined, { maximumFractionDigits: 2 })}` },
          { key: "allocation_base", label: "Allocation Base" },
          { key: "allocation_rate", label: "Allocation Rate", align: "right", format: (v: number) => `${Number(v).toFixed(2)}` },
          { key: "status", label: "Status", format: (value: string) => (
            <Badge className={STATUS_BADGE[value] || "bg-slate-100 text-slate-600"}>
              {value.replace(/_/g, " ")}
            </Badge>
          )},
        ]}
        data={activities}
        rowActions={[
          { label: "View", icon: <Activity size={14} />, variant: "ghost" },
          { label: "Edit", icon: <Shield size={14} />, variant: "outline" },
        ]}
      />
    </AppLayout>
  );
}
