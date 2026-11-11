/**
 * Brixel FactoryOS — Customer Portal Invoices
 * Customer invoice listing page.
 */

import { Head, Link, usePage } from "@inertiajs/react";
import { AppLayout } from "@/components/layout";
import { PageHeader } from "@/components/layout";
import { DataTable } from "@/components/DataTable";
import { Badge } from "@/components/ui/badge";

interface Invoice {
  id: number;
  invoice_number: string;
  status: string;
  invoice_date: string;
  due_date: string;
  total_amount: number;
  total_amount_paid: number;
}

const STATUS_BADGE: Record<string, string> = {
  draft: "bg-slate-100 text-slate-500",
  sent: "bg-amber-50 text-amber-700",
  paid: "bg-emerald-50 text-emerald-700",
  overdue: "bg-rose-50 text-rose-700",
  cancelled: "bg-rose-50 text-rose-700",
};

export default function CustomerInvoices() {
  const { props: data } = usePage() as any;
  const invoices: Invoice[] = data?.invoices?.data || [];
  const meta = data?.invoices?.meta || {};

  return (
    <AppLayout title="Customer Invoices">
      <PageHeader
        title="My Invoices"
        subtitle="View and track your sales invoices"
        breadcrumbs={[{ label: "Customer Portal", href: "/customer/dashboard" }, { label: "My Invoices" }]}
      />

      <DataTable
        title="Invoices"
        columns={[
          { key: "invoice_number", label: "Invoice #" },
          { key: "status", label: "Status", format: (value: string) => (
            <Badge className={STATUS_BADGE[value] || "bg-slate-100 text-slate-600"}>
              {value.replace(/_/g, " ")}
            </Badge>
          )},
          { key: "invoice_date", label: "Invoice Date" },
          { key: "due_date", label: "Due Date" },
          { key: "total_amount", label: "Total", align: "right", format: (v: number) => `Rs. ${Number(v).toLocaleString(undefined, { maximumFractionDigits: 2 })}` },
        ]}
        data={invoices}
        meta={meta}
        emptyMessage="No invoices found."
      />
    </AppLayout>
  );
}
