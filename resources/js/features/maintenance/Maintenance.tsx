/**
 * Brixel FactoryOS — Maintenance
 * Machine and equipment maintenance management.
 */

import { Head, Link, router, usePage } from "@inertiajs/react";
import { AppLayout } from "@/components/layout";
import { PageHeader } from "@/components/layout";
import { DataTable } from "@/components/DataTable";
import { StatStrip } from "@/components/StatStrip";
import { Plus, Eye, Edit, Wrench } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";

interface Machine {
  id: number;
  machine_code: string;
  name: string;
  type: string;
  status: string;
  location: string;
  purchase_date: string;
  last_maintenance_date: string;
  status_label: string;
}

const STATUS_BADGE: Record<string, string> = {
  active: "bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-200",
  maintenance: "bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-200",
  inactive: "bg-slate-100 text-slate-500 ring-1 ring-inset ring-slate-200",
  broken: "bg-rose-50 text-rose-700 ring-1 ring-inset ring-rose-200",
};

export default function Maintenance() {
  const { props: data } = usePage() as any;
  const machines: Machine[] = data?.machines?.data || [];
  const meta = data?.machines?.meta || {};
  const summary = data?.maintenance_summary || { total: 0, active: 0, maintenance: 0 };

  return (
    <AppLayout title="Maintenance">
      <PageHeader
        title="Maintenance"
        subtitle="Machine and equipment maintenance tracking"
        breadcrumbs={[{ label: "Maintenance", href: "/maintenance" }]}
      >
        <Button onClick={() => router.visit("/maintenance/create")}>
          <Plus size={16} /> New Machine
        </Button>
      </PageHeader>

      <StatStrip
        items={[
          { label: "Total Machines", value: summary.total, hint: "All equipment" },
          { label: "Active", value: summary.active, hint: "Operational", tone: "success" },
          { label: "In Maintenance", value: summary.maintenance, hint: "Under service", tone: summary.maintenance > 0 ? "danger" : "success" },
        ]}
      />

      <DataTable
        title="Machines"
        columns={[
          { key: "machine_code", label: "Machine Code" },
          { key: "name", label: "Machine Name" },
          { key: "type", label: "Type" },
          { key: "location", label: "Location" },
          { key: "status", label: "Status", format: (value: string) => (
            <Badge className={STATUS_BADGE[value] || "bg-slate-100 text-slate-600"}>
              {value.replace(/_/g, " ")}
            </Badge>
          )},
          { key: "purchase_date", label: "Purchase Date" },
          { key: "last_maintenance_date", label: "Last Maintenance" },
        ]}
        data={machines}
        meta={meta}
        rowActions={[
          { label: "View", icon: <Eye size={14} />, variant: "ghost" },
          { label: "Edit", icon: <Edit size={14} />, variant: "outline" },
        ]}
      />
    </AppLayout>
  );
}
