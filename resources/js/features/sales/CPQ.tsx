/**
 * Brixel FactoryOS — CPQ (Configure, Price, Quote)
 * Sales quote configuration and pricing management.
 */

import { Head, Link, router, usePage } from "@inertiajs/react";
import { AppLayout } from "@/components/layout";
import { PageHeader } from "@/components/layout";
import { DataTable } from "@/components/DataTable";
import { StatStrip } from "@/components/StatStrip";
import { Plus, Shield, Truck } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";

interface CPQQuote {
  id: number;
  quote_number: string;
  customer_name: string;
  status: string;
  quote_date: string;
  valid_until: string;
  total_amount: number;
  approval_status: string;
}

const STATUS_BADGE: Record<string, string> = {
  draft: "bg-slate-100 text-slate-500 ring-1 ring-inset ring-slate-200",
  sent: "bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-200",
  accepted: "bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-200",
  rejected: "bg-rose-50 text-rose-700 ring-1 ring-inset ring-rose-200",
  expired: "bg-slate-100 text-slate-500 ring-1 ring-inset ring-slate-200",
};

const APPROVAL_BADGE: Record<string, string> = {
  pending: "bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-200",
  approved: "bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-200",
  rejected: "bg-rose-50 text-rose-700 ring-1 ring-inset ring-rose-200",
  not_required: "bg-slate-100 text-slate-500 ring-1 ring-inset ring-slate-200",
};

export default function CPQ() {
  const { props: data } = usePage() as any;
  const quotes: CPQQuote[] = data?.quotes?.data || [];
  const meta = data?.quotes?.meta || {};
  const summary = data?.summary || {
    total_quotes: 0,
    draft_quotes: 0,
    approved_quotes: 0,
  };

  return (
    <AppLayout title="CPQ">
      <PageHeader
        title="CPQ — Configure, Price, Quote"
        subtitle="Sales quote configuration, pricing, and approval workflow"
        breadcrumbs={[{ label: "Sales", href: "/sales" }, { label: "CPQ" }]}
      >
        <Button onClick={() => router.visit("/sales/cpq/create")}>
          <Plus size={16} /> New Quote
        </Button>
      </PageHeader>

      <StatStrip
        items={[
          { label: "Total Quotes", value: summary.total_quotes, hint: "All quotes" },
          { label: "Draft", value: summary.draft_quotes, hint: "In draft status", tone: "warning" },
          { label: "Approved", value: summary.approved_quotes, hint: "Approved quotes", tone: "success" },
        ]}
      />

      <DataTable
        title="CPQ Quotes"
        columns={[
          { key: "quote_number", label: "Quote #" },
          { key: "customer_name", label: "Customer" },
          { key: "quote_date", label: "Quote Date" },
          { key: "valid_until", label: "Valid Until" },
          { key: "total_amount", label: "Amount", align: "right", format: (v: number) => `Rs. ${Number(v).toLocaleString(undefined, { maximumFractionDigits: 2 })}` },
          { key: "status", label: "Status", format: (value: string) => (
            <Badge className={STATUS_BADGE[value] || "bg-slate-100 text-slate-600"}>
              {value.replace(/_/g, " ")}
            </Badge>
          )},
          { key: "approval_status", label: "Approval", format: (value: string) => (
            <Badge className={APPROVAL_BADGE[value] || "bg-slate-100 text-slate-600"}>
              {value.replace(/_/g, " ")}
            </Badge>
          )},
        ]}
        data={quotes}
        meta={meta}
        rowActions={[
          { label: "View", icon: <Truck size={14} />, variant: "ghost" },
          { label: "Edit", icon: <Shield size={14} />, variant: "outline" },
        ]}
      />
    </AppLayout>
  );
}
