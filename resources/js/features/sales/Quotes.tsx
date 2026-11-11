/**
 * Brixel FactoryOS — Sales Quotes
 * Customer quotes and quotation management.
 */

import { Head, Link, router, usePage } from "@inertiajs/react";
import { AppLayout } from "@/components/layout";
import { PageHeader } from "@/components/layout";
import { DataTable } from "@/components/DataTable";
import { StatStrip } from "@/components/StatStrip";
import { Plus, Eye, Edit, FileText } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";

interface Quote {
  id: number;
  quote_number: string;
  customer_name: string;
  quote_date: string;
  valid_until: string;
  status: string;
  status_label: string;
  total_amount: number;
}

const STATUS_BADGE: Record<string, string> = {
  sent: "bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-200",
  accepted: "bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-200",
  rejected: "bg-rose-50 text-rose-700 ring-1 ring-inset ring-rose-200",
  expired: "bg-slate-100 text-slate-500 ring-1 ring-inset ring-slate-200",
};

export default function SalesQuotes() {
  const { props: data } = usePage() as any;
  const quotes: Quote[] = data?.quotes?.data || [];
  const meta = data?.quotes?.meta || {};
  const summary = data?.quote_summary || { total: 0, sent: 0, accepted: 0 };

  return (
    <AppLayout title="Sales Quotes">
      <PageHeader
        title="Sales Quotes"
        subtitle="Customer quotes and quotation management"
        breadcrumbs={[{ label: "Sales", href: "/sales" }, { label: "Quotes" }]}
      >
        <Button onClick={() => router.visit("/sales/quotes/create")}>
          <Plus size={16} /> New Quote
        </Button>
      </PageHeader>

      <StatStrip
        items={[
          { label: "Total Quotes", value: summary.total, hint: "All quotes" },
          { label: "Sent", value: summary.sent, hint: "Sent to customers", tone: "warning" },
          { label: "Accepted", value: summary.accepted, hint: "Accepted by customers", tone: "success" },
        ]}
      />

      <DataTable
        title="Quotes"
        columns={[
          { key: "quote_number", label: "Quote #" },
          { key: "customer_name", label: "Customer" },
          { key: "quote_date", label: "Quote Date" },
          { key: "valid_until", label: "Valid Until" },
          { key: "status", label: "Status", format: (value: string) => (
            <Badge className={STATUS_BADGE[value] || "bg-slate-100 text-slate-600"}>
              {value.replace(/_/g, " ")}
            </Badge>
          )},
          { key: "total_amount", label: "Total Amount", align: "right", format: (v: number) => `Rs. ${Number(v).toLocaleString(undefined, { maximumFractionDigits: 2 })}` },
        ]}
        data={quotes}
        meta={meta}
        rowActions={[
          { label: "View", icon: <Eye size={14} />, variant: "ghost" },
          { label: "Edit", icon: <Edit size={14} />, variant: "outline" },
        ]}
      />
    </AppLayout>
  );
}
