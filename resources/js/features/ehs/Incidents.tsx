/**
 * Brixel FactoryOS — EHS Incidents
 * EHS incident listing page.
 */

import { Head, Link, router, usePage } from "@inertiajs/react";
import { AppLayout } from "@/components/layout";
import { PageHeader } from "@/components/layout";
import { DataTable } from "@/components/DataTable";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import { AlertTriangle, Plus } from "lucide-react";

interface Incident {
  id: number;
  incident_number: string;
  incident_type: string;
  status: string;
  incident_date: string;
  severity: string;
  description: string;
}

const STATUS_BADGE: Record<string, string> = {
  open: "bg-rose-50 text-rose-700 ring-1 ring-inset ring-rose-200",
  in_progress: "bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-200",
  closed: "bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-200",
  resolved: "bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-200",
};

const SEVERITY_BADGE: Record<string, string> = {
  low: "bg-emerald-50 text-emerald-700",
  medium: "bg-amber-50 text-amber-700",
  high: "bg-rose-50 text-rose-700",
  critical: "bg-rose-50 text-rose-700",
};

export default function EHSIncidents() {
  const { props: data } = usePage() as any;
  const incidents: Incident[] = data?.incidents?.data || [];
  const meta = data?.incidents?.meta || {};

  return (
    <AppLayout title="Incident Management">
      <PageHeader
        title="Incident Management"
        subtitle="Track and manage EHS incidents"
        breadcrumbs={[{ label: "EHS", href: "/ehs" }, { label: "Incidents" }]}
        actions={
          <Link
            href="/ehs/incidents/create"
            className="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-rose-50 text-rose-600 font-medium text-sm hover:bg-rose-100 transition-colors"
          >
            <Plus size={14} /> Report Incident
          </Link>
        }
      />

      <DataTable
        title="EHS Incidents"
        columns={[
          { key: "incident_number", label: "Incident #" },
          { key: "incident_type", label: "Type" },
          { key: "incident_date", label: "Date" },
          { key: "severity", label: "Severity", format: (value: string) => (
            <Badge className={SEVERITY_BADGE[value] || "bg-slate-100 text-slate-600"}>
              {value}
            </Badge>
          )},
          { key: "status", label: "Status", format: (value: string) => (
            <Badge className={STATUS_BADGE[value] || "bg-slate-100 text-slate-600"}>
              {value.replace(/_/g, " ")}
            </Badge>
          )},
          { key: "description", label: "Description" },
        ]}
        data={incidents}
        meta={meta}
        rowActions={[
          { label: "View", icon: <AlertTriangle size={14} />, variant: "ghost" },
        ]}
        emptyMessage="No incidents found."
      />
    </AppLayout>
  );
}
