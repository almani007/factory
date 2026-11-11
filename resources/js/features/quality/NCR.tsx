/**
 * Brixel FactoryOS — Non-Conformance Reports
 * Quality non-conformance tracking and management.
 */

import { Head, Link, router, usePage } from "@inertiajs/react";
import { AppLayout } from "@/components/layout";
import { PageHeader } from "@/components/layout";
import { DataTable } from "@/components/DataTable";
import { StatStrip } from "@/components/StatStrip";
import { Plus, AlertCircle, Eye, Edit } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";

interface NCR {
  id: number;
  ncr_number: string;
  title: string;
  item_name: string;
  reported_by: string;
  reported_date: string;
  due_date: string;
  closed_date: string;
  severity: string;
  severity_label: string;
  status: string;
  status_label: string;
}

const SEVERITY_BADGE: Record<string, string> = {
  critical: "bg-rose-50 text-rose-700 ring-1 ring-inset ring-rose-200",
  major: "bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-200",
  minor: "bg-sky-50 text-sky-700 ring-1 ring-inset ring-sky-200",
};

const STATUS_BADGE: Record<string, string> = {
  open: "bg-rose-50 text-rose-700 ring-1 ring-inset ring-rose-200",
  in_review: "bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-200",
  closed: "bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-200",
};

export default function NCR() {
  const { props: data } = usePage() as any;
  const ncrReports: NCR[] = data?.ncr_reports?.data || [];
  const meta = data?.ncr_reports?.meta || {};
  const summary = data?.ncr_summary || { total: 0, open: 0, closed: 0, critical: 0 };

  return (
    <AppLayout title="Non-Conformance Reports">
      <PageHeader
        title="Non-Conformance Reports (NCR)"
        subtitle="Quality non-conformance tracking and management"
        breadcrumbs={[{ label: "Quality", href: "/quality" }, { label: "NCR Reports" }]}
      >
        <Button onClick={() => router.visit("/quality/non-conformance/create")}>
          <Plus size={16} /> New NCR
        </Button>
      </PageHeader>

      <StatStrip
        items={[
          { label: "Total NCRs", value: summary.total, hint: "All reports" },
          { label: "Open", value: summary.open, hint: "Awaiting resolution", tone: summary.open > 0 ? "warning" : "success" },
          { label: "Critical", value: summary.critical, hint: "Critical severity", tone: summary.critical > 0 ? "danger" : "success" },
        ]}
      />

      <DataTable
        title="Non-Conformance Reports"
        columns={[
          { key: "ncr_number", label: "NCR #" },
          { key: "title", label: "Title" },
          { key: "item_name", label: "Item" },
          { key: "reported_by", label: "Reported By" },
          { key: "reported_date", label: "Reported Date" },
          { key: "due_date", label: "Due Date" },
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
        data={ncrReports}
        meta={meta}
        rowActions={[
          { label: "View", icon: <Eye size={14} />, variant: "ghost" },
          { label: "Edit", icon: <Edit size={14} />, variant: "outline" },
        ]}
      />
    </AppLayout>
  );
}
