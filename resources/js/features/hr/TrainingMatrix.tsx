/**
 * Brixel FactoryOS — HR Training Matrix
 * Employee training matrix and skill tracking.
 */

import { Head, Link, router, usePage } from "@inertiajs/react";
import { AppLayout } from "@/components/layout";
import { PageHeader } from "@/components/layout";
import { DataTable } from "@/components/DataTable";
import { Plus, Users, Clock } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";

interface TrainingMatrix {
  id: number;
  employee_code: string;
  employee_name: string;
  skill_name: string;
  skill_level: string;
  certification_date: string;
  expiry_date: string;
  status: string;
}

const STATUS_BADGE: Record<string, string> = {
  active: "bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-200",
  expired: "bg-rose-50 text-rose-700 ring-1 ring-inset ring-rose-200",
  expiring_soon: "bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-200",
  not_started: "bg-slate-100 text-slate-500 ring-1 ring-inset ring-slate-200",
};

export default function TrainingMatrix() {
  const { props: data } = usePage() as any;
  const matrix: TrainingMatrix[] = data?.matrix?.data || [];
  const meta = data?.matrix?.meta || {};

  const totalEntries = matrix.length;
  const activeCertifications = matrix.filter((m: TrainingMatrix) => m.status === "active").length;
  const expiringSoon = matrix.filter((m: TrainingMatrix) => m.status === "expiring_soon").length;

  const totalEntriesStr = String(totalEntries);
  const activeCertificationsStr = String(activeCertifications);
  const expiringSoonStr = String(expiringSoon);

  return (
    <AppLayout title="Training Matrix">
      <PageHeader
        title="Training Matrix"
        subtitle="Employee training matrix and skill tracking"
        breadcrumbs={[{ label: "HR", href: "/hr" }, { label: "Training Matrix" }]}
        actions={
          <Link
            href="/hr/training-matrix/create"
            className="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-factory-50 text-factory-600 font-medium text-sm hover:bg-factory-100 transition-colors"
          >
            <Plus size={14} /> New Entry
          </Link>
        }
      />

      <div className="mb-8 grid grid-cols-2 gap-4 md:grid-cols-4 lg:grid-cols-4">
        <Card>
          <CardHeader>
            <CardTitle className="text-base font-semibold text-slate-900">Total Entries</CardTitle>
          </CardHeader>
          <CardContent className="p-5 pt-0">
            <p className="text-2xl font-semibold text-slate-900">{totalEntriesStr}</p>
          </CardContent>
        </Card>
        <Card>
          <CardHeader>
            <CardTitle className="text-base font-semibold text-slate-900">Active Certifications</CardTitle>
          </CardHeader>
          <CardContent className="p-5 pt-0">
            <p className="text-2xl font-semibold text-slate-900">{activeCertificationsStr}</p>
          </CardContent>
        </Card>
        <Card>
          <CardHeader>
            <CardTitle className="text-base font-semibold text-slate-900">Expiring Soon</CardTitle>
          </CardHeader>
          <CardContent className="p-5 pt-0">
            <p className="text-2xl font-semibold text-slate-900">{expiringSoonStr}</p>
          </CardContent>
        </Card>
      </div>

      <DataTable
        title="Training Matrix"
        columns={[
          { key: "employee_code", label: "Employee Code" },
          { key: "employee_name", label: "Employee Name" },
          { key: "skill_name", label: "Skill Name" },
          { key: "skill_level", label: "Skill Level" },
          { key: "certification_date", label: "Certification Date" },
          { key: "expiry_date", label: "Expiry Date" },
          { key: "status", label: "Status", format: (value: string) => (
            <Badge className={STATUS_BADGE[value] || "bg-slate-100 text-slate-600"}>
              {value.replace(/_/g, " ")}
            </Badge>
          )},
        ]}
        data={matrix}
        meta={meta}
        rowActions={[
          { label: "View", icon: <Users size={14} />, variant: "ghost" },
          { label: "Edit", icon: <Clock size={14} />, variant: "outline" },
        ]}
        emptyMessage="No training matrix entries found."
      />
    </AppLayout>
  );
}
