/**
 * Brixel FactoryOS — Email Templates
 * Email template management.
 */

import { Head, Link, router, usePage } from "@inertiajs/react";
import { AppLayout } from "@/components/layout";
import { PageHeader } from "@/components/layout";
import { DataTable } from "@/components/DataTable";
import { Plus, FileText, ArrowLeft } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";

interface Template {
  id: number;
  template_name: string;
  template_type: string;
  subject: string;
  status: string;
  created_at: string;
}

const STATUS_BADGE: Record<string, string> = {
  active: "bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-200",
  inactive: "bg-slate-100 text-slate-500 ring-1 ring-inset ring-slate-200",
};

export default function EmailTemplates() {
  const { props: data } = usePage() as any;
  const templates: Template[] = data?.templates?.data || [];
  const meta = data?.templates?.meta || {};

  return (
    <AppLayout title="Email Templates">
      <PageHeader
        title="Email Templates"
        subtitle="Email template management"
        breadcrumbs={[{ label: "Communications", href: "/communications/email-templates" }, { label: "Email Templates" }]}
        actions={
          <Link
            href="/communications/email-templates/create"
            className="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-factory-50 text-factory-600 font-medium text-sm hover:bg-factory-100 transition-colors"
          >
            <Plus size={14} /> New Template
          </Link>
        }
      />

      <DataTable
        title="Email Templates"
        columns={[
          { key: "template_name", label: "Template Name" },
          { key: "template_type", label: "Type", format: (value: string) => value.replace(/_/g, " ") },
          { key: "subject", label: "Subject" },
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
        emptyMessage="No email templates found."
      />
    </AppLayout>
  );
}
