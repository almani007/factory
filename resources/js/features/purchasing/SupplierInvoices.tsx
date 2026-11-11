/**
 * Brixel FactoryOS — Supplier Invoices
 * Supplier bills and accounts payable.
 */

import { Head, Link, router, usePage } from "@inertiajs/react";
import { AppLayout } from "@/components/layout";
import { PageHeader } from "@/components/layout";
import { DataTable } from "@/components/DataTable";
import { StatStrip } from "@/components/StatStrip";
import { Plus, Eye, Edit, Banknote } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";

interface Invoice {
  id: number;
  invoice_number: string;
  supplier_name: string;
  invoice_date: string;
  due_date: string;
  status: string;
  status_label: string;
  total_amount: number;
}

const STATUS_BADGE: Record<string, string> = {
  paid: "bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-200",
  unpaid: "bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-200",
  overdue: "bg-rose-50 text-rose-700 ring-1 ring-inset ring-rose-200",
  draft: "bg-slate-100 text-slate-500 ring-1 ring-inset ring-slate-200",
};

export default function SupplierInvoices() {
  const { props: data } = usePage() as any;
  const invoices: Invoice[] = data?.invoices?.data || [];
  const meta = data?.invoices?.meta || {};
  const summary = data?.supplier_invoice_summary || { total: 0, paid: 0, unpaid: 0 };

  return (
    <AppLayout title="Supplier Invoices">
      <PageHeader
        title="Supplier Invoices"
        subtitle="Supplier bills and accounts payable"
        breadcrumbs={[{ label: "Purchasing", href: "/purchasing" }, { label: "Supplier Invoices" }]}
      >
        <Button onClick={() => router.visit("/purchasing/supplier-invoices/create")}>
          <Plus size={16} /> New Invoice
        </Button>
      </PageHeader>

      <StatStrip
        items={[
          { label: "Total Invoices", value: summary.total, hint: "All invoices" },
          { label: "Paid", value: summary.paid, hint: "Fully paid", tone: "success" },
          { label: "Unpaid", value: summary.unpaid, hint: "Awaiting payment", tone: summary.unpaid > 0 ? "warning" : "success" },
        ]}
      />

      <DataTable
        title="Supplier Invoices"
        columns={[
          { key: "invoice_number", label: "Invoice #" },
          { key: "supplier_name", label: "Supplier" },
          { key: "invoice_date", label: "Invoice Date" },
          { key: "due_date", label: "Due Date" },
          { key: "status", label: "Status", format: (value: string) => (
            <Badge className={STATUS_BADGE[value] || "bg-slate-100 text-slate-600"}>
              {value.replace(/_/g, " ")}
            </Badge>
          )},
          { key: "total_amount", label: "Total Amount", align: "right", format: (v: number) => `Rs. ${Number(v).toLocaleString(undefined, { maximumFractionDigits: 2 })}` },
        ]}
        data={invoices}
        meta={meta}
        rowActions={[
          { label: "View", icon: <Eye size={14} />, variant: "ghost" },
          { label: "Edit", icon: <Edit size={14} />, variant: "outline" },
        ]}
      />
    </AppLayout>
  );
}
