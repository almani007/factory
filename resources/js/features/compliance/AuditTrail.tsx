/**
 * Brixel FactoryOS — Audit Trail
 * Audit trail and compliance tracking for all system changes.
 */

import { Head, Link, router, usePage } from "@inertiajs/react";
import { AppLayout } from "@/components/layout";
import { PageHeader } from "@/components/layout";
import { DataTable } from "@/components/DataTable";
import { Shield, Users, Activity } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";

interface AuditLog {
  id: number;
  event_type: string;
  table_name: string;
  record_id: number;
  old_values: string;
  new_values: string;
  created_at: string;
  user_name: string;
}

const EVENT_BADGE: Record<string, string> = {
  create: "bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-200",
  update: "bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-200",
  delete: "bg-rose-50 text-rose-700 ring-1 ring-inset ring-rose-200",
  login: "bg-indigo-50 text-indigo-700 ring-1 ring-inset ring-indigo-200",
  logout: "bg-slate-50 text-slate-700 ring-1 ring-inset ring-slate-200",
  approve: "bg-purple-50 text-purple-700 ring-1 ring-inset ring-purple-200",
  reject: "bg-rose-50 text-rose-700 ring-1 ring-inset ring-rose-200",
};

export default function AuditTrail() {
  const { props: data } = usePage() as any;
  const auditLogs = data?.audit_logs?.data || [];
  const meta = data?.audit_logs?.meta || {};

  return (
    <AppLayout title="Audit Trail">
      <PageHeader
        title="Audit Trail"
        subtitle="System change tracking and compliance audit log"
        breadcrumbs={[{ label: "Compliance", href: "/compliance/audit-trail" }, { label: "Audit Trail" }]}
      />

      <DataTable
        title="Audit Log"
        columns={[
          { key: "event_type", label: "Event Type", format: (value: string) => (
            <Badge className={EVENT_BADGE[value] || "bg-slate-100 text-slate-600"}>
              {value.replace(/_/g, " ")}
            </Badge>
          )},
          { key: "table_name", label: "Table" },
          { key: "record_id", label: "Record ID", align: "right" },
          { key: "user_name", label: "User" },
          { key: "created_at", label: "Timestamp" },
        ]}
        data={auditLogs}
        meta={meta}
        rowActions={[
          { label: "View", icon: <Shield size={14} />, variant: "ghost" },
        ]}
      />
    </AppLayout>
  );
}
