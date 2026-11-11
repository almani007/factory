/**
 * Brixel FactoryOS — EHS Dashboard
 * Environment, Health & Safety dashboard.
 */

import { Head, Link, usePage } from "@inertiajs/react";
import { AppLayout } from "@/components/layout";
import { PageHeader } from "@/components/layout";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { KPICard } from "@/components/KPICard";
import { AlertTriangle, Shield, ClipboardList } from "lucide-react";
import { DataTable } from "@/components/DataTable";
import { Badge } from "@/components/ui/badge";

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

export default function EHSDashboard() {
  const { props: data } = usePage() as any;
  const incidents: Incident[] = data?.incidents?.data || [];
  const summary = data?.summary || {
    total_incidents: 0,
    open_incidents: 0,
    critical_incidents: 0,
  };

  return (
    <AppLayout title="EHS Dashboard">
      <PageHeader
        title="EHS Dashboard"
        subtitle="Environment, Health & Safety management"
        breadcrumbs={[{ label: "EHS", href: "/ehs" }, { label: "Dashboard" }]}
      />

      <div className="mb-8 grid grid-cols-2 gap-4 md:grid-cols-3 lg:grid-cols-4">
        <KPICard
          title="Total Incidents"
          value={summary.total_incidents.toLocaleString?.() || "0"}
          icon={<AlertTriangle size={18} className="text-rose-600" />}
          change={`${summary.open_incidents || 0} open`}
          trend={summary.open_incidents > 0 ? "down" : "up"}
        />
        <KPICard
          title="Open Incidents"
          value={summary.open_incidents.toLocaleString?.() || "0"}
          icon={<AlertTriangle size={18} className="text-amber-600" />}
          change="Needs attention"
          trend={summary.open_incidents > 0 ? "down" : "up"}
        />
        <KPICard
          title="Critical Incidents"
          value={summary.critical_incidents.toLocaleString?.() || "0"}
          icon={<Shield size={18} className="text-rose-600" />}
          change="Critical"
          trend={summary.critical_incidents > 0 ? "down" : "up"}
        />
      </div>

      <div className="grid gap-6 lg:grid-cols-2">
        <Card className="lg:col-span-1">
          <CardHeader className="flex items-center justify-between pb-4">
            <CardTitle className="text-base font-semibold text-slate-900">Recent Incidents</CardTitle>
          </CardHeader>
          <CardContent className="p-0">
            <DataTable
              title=""
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
              ]}
              data={incidents}
              emptyMessage="No incidents found."
            />
          </CardContent>
        </Card>

        <Card className="lg:col-span-1">
          <CardHeader className="pb-4">
            <CardTitle className="text-base font-semibold text-slate-900">Quick Actions</CardTitle>
          </CardHeader>
          <CardContent className="p-0 space-y-3">
            <Link
              href="/ehs/incidents/create"
              className="w-full inline-flex items-center gap-2 px-4 py-3 rounded-lg border border-slate-200 bg-white text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50 transition-colors"
            >
              <AlertTriangle size={16} className="text-rose-600" />
              Report Incident
            </Link>
            <Link
              href="/ehs/near-misses/create"
              className="w-full inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-slate-200 bg-white text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50 transition-colors"
            >
              <AlertTriangle size={16} className="text-amber-600" />
              Report Near Miss
            </Link>
            <Link
              href="/ehs/safety-audits/create"
              className="w-full inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-slate-200 bg-white text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50 transition-colors"
            >
              <ClipboardList size={16} className="text-indigo-600" />
              Schedule Safety Audit
            </Link>
          </CardContent>
        </Card>
      </div>
    </AppLayout>
  );
}
