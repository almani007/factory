/**
 * Brixel FactoryOS — Documents
 * Document management and control.
 */

import { Head, Link, router, usePage } from "@inertiajs/react";
import { AppLayout } from "@/components/layout";
import { PageHeader } from "@/components/layout";
import { DataTable } from "@/components/DataTable";
import { Plus, Copy, Eye, Edit } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";

interface Document {
  id: number;
  document_number: string;
  title: string;
  category: string;
  category_label: string;
  file_name: string;
  file_size: string;
  version: string;
  status: string;
  status_label: string;
  upload_date: string;
  expiry_date: string;
  uploaded_by: string;
}

const STATUS_BADGE: Record<string, string> = {
  draft: "bg-slate-100 text-slate-500 ring-1 ring-inset ring-slate-200",
  active: "bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-200",
  archived: "bg-slate-100 text-slate-500 ring-1 ring-inset ring-slate-200",
  expired: "bg-rose-50 text-rose-700 ring-1 ring-inset ring-rose-200",
};

export default function Documents() {
  const { props: data } = usePage() as any;
  const documents: Document[] = data?.documents?.data || [];
  const meta = data?.documents?.meta || {};

  return (
    <AppLayout title="Document Management">
      <PageHeader
        title="Document Management"
        subtitle="Document control and management system"
        breadcrumbs={[{ label: "Documents", href: "/documents" }]}
      >
        <Button onClick={() => router.visit("/documents/create")}>
          <Plus size={16} /> New Document
        </Button>
      </PageHeader>

      <DataTable
        title="Documents"
        columns={[
          { key: "document_number", label: "Document #" },
          { key: "title", label: "Title" },
          { key: "category", label: "Category", format: (value: string) => value.replace(/_/g, " ") },
          { key: "file_name", label: "File Name" },
          { key: "version", label: "Version" },
          { key: "upload_date", label: "Upload Date" },
          { key: "expiry_date", label: "Expiry Date" },
          { key: "status", label: "Status", format: (value: string) => (
            <Badge className={STATUS_BADGE[value] || "bg-slate-100 text-slate-600"}>
              {value.replace(/_/g, " ")}
            </Badge>
          )},
        ]}
        data={documents}
        meta={meta}
        rowActions={[
          { label: "View", icon: <Eye size={14} />, variant: "ghost" },
          { label: "Edit", icon: <Edit size={14} />, variant: "outline" },
        ]}
      />
    </AppLayout>
  );
}
