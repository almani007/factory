/**
 * Brixel FactoryOS — RFQ Management
 * Request for Quotation management and supplier bidding.
 */

import { Head, Link, router, usePage } from "@inertiajs/react";
import { AppLayout } from "@/components/layout";
import { PageHeader } from "@/components/layout";
import { DataTable } from "@/components/DataTable";
import { StatStrip } from "@/components/StatStrip";
import { Plus, Shield, Users } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";

interface RFQ {
  id: number;
  rfq_number: string;
  title: string;
  supplier_name: string;
  status: string;
  request_date: string;
  response_due_date: string;
  total_amount: number;
}

const STATUS_BADGE: Record<string, string> = {
  draft: "bg-slate-100 text-slate-500 ring-1 ring-inset ring-slate-200",
  open: "bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-200",
  submitted: "bg-sky-50 text-sky-700 ring-1 ring-inset ring-sky-200",
  awarded: "bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-200",
  rejected: "bg-rose-50 text-rose-700 ring-1 ring-inset ring-rose-200",
  closed: "bg-slate-100 text-slate-500 ring-1 ring-inset ring-slate-200",
};

export default function RFQ() {
  const { props: data } = usePage() as any;
  const rfqs: RFQ[] = data?.rfqs?.data || [];
  const meta = data?.rfqs?.meta || {};
  const summary = data?.summary || {
    total_rfqs: 0,
    open_rfqs: 0,
    awarded_rfqs: 0,
  };

  return (
    <AppLayout title="RFQ Management">
      <PageHeader
        title="Request for Quotation"
        subtitle="RFQ management and supplier bidding process"
        breadcrumbs={[{ label: "Purchasing", href: "/purchasing" }, { label: "RFQs" }]}
      >
        <Button onClick={() => router.visit("/purchasing/rfq/create")}>
          <Plus size={16} /> New RFQ
        </Button>
      </PageHeader>

      <StatStrip
        items={[
          { label: "Total RFQs", value: summary.total_rfqs, hint: "All RFQs" },
          { label: "Open", value: summary.open_rfqs, hint: "Awaiting responses", tone: "warning" },
          { label: "Awarded", value: summary.awarded_rfqs, hint: "Awarded to suppliers", tone: "success" },
        ]}
      />

      <DataTable
        title="RFQs"
        columns={[
          { key: "rfq_number", label: "RFQ #" },
          { key: "title", label: "Title" },
          { key: "supplier_name", label: "Supplier" },
          { key: "request_date", label: "Request Date" },
          { key: "response_due_date", label: "Due Date" },
          { key: "total_amount", label: "Amount", align: "right", format: (v: number) => `Rs. ${Number(v).toLocaleString(undefined, { maximumFractionDigits: 2 })}` },
          { key: "status", label: "Status", format: (value: string) => (
            <Badge className={STATUS_BADGE[value] || "bg-slate-100 text-slate-600"}>
              {value.replace(/_/g, " ")}
            </Badge>
          )},
        ]}
        data={rfqs}
        meta={meta}
        rowActions={[
          { label: "View", icon: <Users size={14} />, variant: "ghost" },
          { label: "Edit", icon: <Shield size={14} />, variant: "outline" },
        ]}
      />
    </AppLayout>
  );
}
