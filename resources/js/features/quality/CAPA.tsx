/**
 * Brixel FactoryOS — CAPA (Corrective & Preventive Action)
 * Corrective and preventive action tracking.
 */

import { Head, Link, router, usePage } from "@inertiajs/react";
import { AppLayout } from "@/components/layout";
import { PageHeader } from "@/components/layout";
import { DataTable } from "@/components/DataTable";
import { StatStrip } from "@/components/StatStrip";
import { Plus, ShieldAlert, Eye, Edit } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";

interface CAPA {
  id: number;
  capa_number: string;
  title: string;
  ncr_number: string;
  type: string;
  type_label: string;
  status: string;
  status_label: string;
  due_date: string;
  closed_date: string;
  assigned_to: string;
}

const TYPE_BADGE: Record<string, string> = {
  corrective: "bg-sky-50 text-sky-700 ring-1 ring-inset ring-sky-200",
  preventive: "bg-indigo-50 text-indigo-700 ring-1 ring-inset ring-indigo-200",
};

const STATUS_BADGE: Record<string, string> = {
  open: "bg-rose-50 text-rose-700 ring-1 ring-inset ring-rose-200",
  in_progress: "bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-200",
  closed: "bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-200",
};

export default function CAPA() {
  const { props: data } = usePage() as any;
  const capaReports: CAPA[] = data?.capa_reports?.data || [];
  const meta = data?.capa_reports?.meta || {};
  const summary = data?.capa_summary || { total: 0, open: 0, closed: 0 };

  return (
    <AppLayout title="CAPA">
      <PageHeader
        title="CAPA"
        subtitle="Corrective and preventive action tracking"
        breadcrumbs={[{ label: "Quality", href: "/quality" }, { label: "CAPA" }]}
      >
        <Button onClick={() => router.visit("/quality/capa/create")}>
          <Plus size={16} /> New CAPA
        </Button>
      </PageHeader>

      <StatStrip
        items={[
          { label: "Total CAPAs", value: summary.total, hint: "All CAPAs" },
          { label: "Open", value: summary.open, hint: "In progress", tone: summary.open > 0 ? "warning" : "success" },
          { label: "Closed", value: summary.closed, hint: "Completed", tone: "success" },
        ]}
      />

      <DataTable
        title="CAPA Reports"
        columns={[
          { key: "capa_number", label: "CAPA #" },
          { key: "title", label: "Title" },
          { key: "ncr_number", label: "Related NCR" },
          { key: "type", label: "Type", format: (value: string) => (
            <Badge className={TYPE_BADGE[value] || "bg-slate-100 text-slate-600"}>
              {value.replace(/_/g, " ")}
            </Badge>
          )},
          { key: "assigned_to", label: "Assigned To" },
          { key: "due_date", label: "Due Date" },
          { key: "status", label: "Status", format: (value: string) => (
            <Badge className={STATUS_BADGE[value] || "bg-slate-100 text-slate-600"}>
              {value.replace(/_/g, " ")}
            </Badge>
          )},
        ]}
        data={capaReports}
        meta={meta}
        rowActions={[
          { label: "View", icon: <Eye size={14} />, variant: "ghost" },
          { label: "Edit", icon: <Edit size={14} />, variant: "outline" },
        ]}
      />
    </AppLayout>
  );
}
