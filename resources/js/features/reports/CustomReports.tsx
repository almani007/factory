/**
 * Brixel FactoryOS — Custom Reports
 * Custom report builder and scheduled report management.
 */

import { Head, Link, router, usePage } from "@inertiajs/react";
import { AppLayout } from "@/components/layout";
import { PageHeader } from "@/components/layout";
import { DataTable } from "@/components/DataTable";
import { Plus, FileType, Eye, Edit } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";

interface CustomReport {
  id: number;
  report_name: string;
  report_type: string;
  report_type_label: string;
  status: string;
  status_label: string;
  frequency: string;
  frequency_label: string;
  last_run: string;
  next_run: string;
  created_by: string;
}

const STATUS_BADGE: Record<string, string> = {
  active: "bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-200",
  inactive: "bg-slate-100 text-slate-500 ring-1 ring-inset ring-slate-200",
};

export default function CustomReports() {
  const { props: data } = usePage() as any;
  const reports: CustomReport[] = data?.reports?.data || [];
  const meta = data?.reports?.meta || {};

  return (
    <AppLayout title="Custom Reports">
      <PageHeader
        title="Custom Reports"
        subtitle="Custom report builder and scheduled report management"
        breadcrumbs={[{ label: "Reporting", href: "/reports/custom" }, { label: "Custom Reports" }]}
      >
        <Button onClick={() => router.visit("/reports/custom/create")}>
          <Plus size={16} /> New Report
        </Button>
      </PageHeader>

      <DataTable
        title="Custom Reports"
        columns={[
          { key: "report_name", label: "Report Name" },
          { key: "report_type", label: "Type", format: (value: string) => value.replace(/_/g, " ") },
          { key: "frequency", label: "Frequency", format: (value: string) => value.replace(/_/g, " ") },
          { key: "status", label: "Status", format: (value: string) => (
            <Badge className={STATUS_BADGE[value] || "bg-slate-100 text-slate-600"}>
              {value.replace(/_/g, " ")}
            </Badge>
          )},
          { key: "last_run", label: "Last Run" },
          { key: "next_run", label: "Next Run" },
          { key: "created_by", label: "Created By" },
        ]}
        data={reports}
        meta={meta}
        rowActions={[
          { label: "View", icon: <Eye size={14} />, variant: "ghost" },
          { label: "Edit", icon: <Edit size={14} />, variant: "outline" },
        ]}
      />
    </AppLayout>
  );
}
