/**
 * Brixel FactoryOS — Supplier Portal Dashboard
 * Supplier-facing dashboard for PO and invoice tracking.
 */

import { Head, Link, usePage } from "@inertiajs/react";
import { AppLayout } from "@/components/layout";
import { PageHeader } from "@/components/layout";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { KPICard } from "@/components/KPICard";
import { Truck, FileText, CreditCard } from "lucide-react";
import { DataTable } from "@/components/DataTable";
import { Badge } from "@/components/ui/badge";

interface PO {
  id: number;
  po_number: string;
  status: string;
  order_date: string;
  expected_delivery_date: string;
  total_amount: number;
}

interface Invoice {
  id: number;
  invoice_number: string;
  status: string;
  invoice_date: string;
  due_date: string;
  total_amount: number;
}

const PO_STATUS_BADGE: Record<string, string> = {
  draft: "bg-slate-100 text-slate-500",
  submitted: "bg-amber-50 text-amber-700",
  approved: "bg-emerald-50 text-emerald-700",
  received: "bg-indigo-50 text-indigo-700",
  completed: "bg-emerald-50 text-emerald-700",
  cancelled: "bg-rose-50 text-rose-700",
};

const INVOICE_STATUS_BADGE: Record<string, string> = {
  draft: "bg-slate-100 text-slate-500",
  sent: "bg-amber-50 text-amber-700",
  paid: "bg-emerald-50 text-emerald-700",
  overdue: "bg-rose-50 text-rose-700",
  cancelled: "bg-rose-50 text-rose-700",
};

export default function SupplierDashboard() {
  const { props: data } = usePage() as any;
  const pos: PO[] = data?.pos?.data || [];
  const invoices: Invoice[] = data?.invoices?.data || [];
  const summary = data?.summary || {
    total_pos: 0,
    open_pos: 0,
    total_invoices: 0,
    unpaid_invoices: 0,
  };

  return (
    <AppLayout title="Supplier Portal">
      <PageHeader
        title="Supplier Portal"
        subtitle="Track your purchase orders and invoices"
        breadcrumbs={[{ label: "Supplier Portal", href: "/supplier/dashboard" }]}
      />

      <div className="mb-8 grid grid-cols-2 gap-4 md:grid-cols-3 lg:grid-cols-5">
        <KPICard
          title="Total POs"
          value={summary.total_pos.toLocaleString?.() || "0"}
          icon={<Truck size={18} className="text-factory-600" />}
          change={`${summary.open_pos || 0} open`}
          trend={summary.open_pos > 0 ? "down" : "up"}
        />
        <KPICard
          title="Total Invoices"
          value={summary.total_invoices.toLocaleString?.() || "0"}
          icon={<FileText size={18} className="text-factory-600" />}
          change={`${summary.unpaid_invoices || 0} unpaid`}
          trend={summary.unpaid_invoices > 0 ? "down" : "up"}
        />
      </div>

      <div className="grid gap-6 lg:grid-cols-2">
        <Card className="lg:col-span-1">
          <CardHeader className="flex items-center justify-between pb-4">
            <CardTitle className="text-base font-semibold text-slate-900">Recent POs</CardTitle>
          </CardHeader>
          <CardContent className="p-0">
            <DataTable
              title=""
              columns={[
                { key: "po_number", label: "PO #" },
                { key: "status", label: "Status", format: (value: string) => (
                  <Badge className={PO_STATUS_BADGE[value] || "bg-slate-100 text-slate-600"}>
                    {value.replace(/_/g, " ")}
                  </Badge>
                )},
                { key: "order_date", label: "Order Date" },
                { key: "expected_delivery_date", label: "Expected Delivery" },
                { key: "total_amount", label: "Total", align: "right", format: (v: number) => `Rs. ${Number(v).toLocaleString(undefined, { maximumFractionDigits: 2 })}` },
              ]}
              data={pos}
              emptyMessage="No POs found."
            />
          </CardContent>
        </Card>

        <Card className="lg:col-span-1">
          <CardHeader className="pb-4">
            <CardTitle className="text-base font-semibold text-slate-900">Recent Invoices</CardTitle>
          </CardHeader>
          <CardContent className="p-0">
            <DataTable
              title=""
              columns={[
                { key: "invoice_number", label: "Invoice #" },
                { key: "status", label: "Status", format: (value: string) => (
                  <Badge className={INVOICE_STATUS_BADGE[value] || "bg-slate-100 text-slate-600"}>
                    {value.replace(/_/g, " ")}
                  </Badge>
                )},
                { key: "invoice_date", label: "Invoice Date" },
                { key: "due_date", label: "Due Date" },
                { key: "total_amount", label: "Total", align: "right", format: (v: number) => `Rs. ${Number(v).toLocaleString(undefined, { maximumFractionDigits: 2 })}` },
              ]}
              data={invoices}
              emptyMessage="No invoices found."
            />
          </CardContent>
        </Card>
      </div>
    </AppLayout>
  );
}
