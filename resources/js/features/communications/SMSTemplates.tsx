/**
 * Brixel FactoryOS — SMS Templates
 * SMS template management.
 */

import { Head, Link, router, usePage } from "@inertiajs/react";
import { AppLayout } from "@/components/layout";
import { PageHeader } from "@/components/layout";
import { DataTable } from "@/components/DataTable";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import { FileText, Plus } from "lucide-react";

interface Template {
  id: number;
  template_name: string;
  message: string;
  status: string;
  created_at: string;
}

const STATUS_BADGE: Record<string, string> = {
  active: "bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-200",
  inactive: "bg-slate-100 text-slate-500 ring-1 ring-inset ring-slate-200",
};

export default function SMSTemplates() {
  const { props: data } = usePage() as any;
  const templates: Template[] = data?.templates?.data || [];
  const meta = data?.templates?.meta || {};

  return (
    <AppLayout title="SMS Templates">
      <PageHeader
        title="SMS Templates"
        subtitle="SMS template management"
        breadcrumbs={[{ label: "Communications", href: "/communications/sms-templates" }, { label: "SMS Templates" }]}
        actions={
          <Link
            href="/communications/sms-templates/create"
            className="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-factory-50 text-factory-600 font-medium text-sm hover:bg-factory-100 transition-colors"
          >
            <Plus size={14} /> New Template
          </Link>
        }
      />

      <DataTable
        title="SMS Templates"
        columns={[
          { key: "template_name", label: "Template Name" },
          { key: "message", label: "Message" },
          { key: "status", label: "Status", format: (value: string) => (
            <Badge className={STATUS_BADGE[value] || "bg-slate-100 text-slate-600"}>
              {value.replace(/_/g, " ")}
            </Badge>
          )},
          { key: "created_at", label: "Created" },
        ]}
        data={templates}
        meta={meta}
        rowActions={[
          { label: "View", icon: <FileText size={14} />, variant: "ghost" },
          { label: "Edit", icon: <Plus size={14} />, variant: "outline" },
        ]}
        emptyMessage="No SMS templates found."
      />
    </AppLayout>
  );
}
