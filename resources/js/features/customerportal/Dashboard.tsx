/**
 * Brixel FactoryOS — Customer Portal Dashboard
 * Customer-facing dashboard for order and invoice tracking.
 */

import { Head, Link, usePage } from "@inertiajs/react";
import { AppLayout } from "@/components/layout";
import { PageHeader } from "@/components/layout";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { KPICard } from "@/components/KPICard";
import { Truck, FileText, CreditCard, FileText as FileTextIcon } from "lucide-react";
import { DataTable } from "@/components/DataTable";
import { Badge } from "@/components/ui/badge";

interface Order {
  id: number;
  so_number: string;
  status: string;
  order_date: string;
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

interface Quote {
  id: number;
  quote_number: string;
  status: string;
  quote_date: string;
  valid_until: string;
  total_amount: number;
}

const ORDER_STATUS_BADGE: Record<string, string> = {
  draft: "bg-slate-100 text-slate-500",
  confirmed: "bg-amber-50 text-amber-700",
  in_production: "bg-sky-50 text-sky-700",
  completed: "bg-emerald-50 text-emerald-700",
  dispatched: "bg-indigo-50 text-indigo-700",
  delivered: "bg-emerald-50 text-emerald-700",
  cancelled: "bg-rose-50 text-rose-700",
};

const INVOICE_STATUS_BADGE: Record<string, string> = {
  draft: "bg-slate-100 text-slate-500",
  sent: "bg-amber-50 text-amber-700",
  paid: "bg-emerald-50 text-emerald-700",
  overdue: "bg-rose-50 text-rose-700",
  cancelled: "bg-rose-50 text-rose-700",
};

const QUOTE_STATUS_BADGE: Record<string, string> = {
  draft: "bg-slate-100 text-slate-500",
  sent: "bg-amber-50 text-amber-700",
  accepted: "bg-emerald-50 text-emerald-700",
  rejected: "bg-rose-50 text-rose-700",
  expired: "bg-slate-100 text-slate-500",
};

export default function CustomerDashboard() {
  const { props: data } = usePage() as any;
  const orders: Order[] = data?.orders?.data || [];
  const invoices: Invoice[] = data?.invoices?.data || [];
  const quotes: Quote[] = data?.quotes?.data || [];
  const summary = data?.summary || {
    total_orders: 0,
    open_orders: 0,
    total_invoices: 0,
    unpaid_invoices: 0,
    open_quotes: 0,
  };

  return (
    <AppLayout title="Customer Portal">
      <PageHeader
        title="Customer Portal"
        subtitle="Track your orders, invoices, and quotes"
        breadcrumbs={[{ label: "Customer Portal", href: "/customer/dashboard" }]}
      />

      <div className="mb-8 grid grid-cols-2 gap-4 md:grid-cols-3 lg:grid-cols-5">
        <KPICard
          title="Total Orders"
          value={summary.total_orders.toLocaleString?.() || "0"}
          icon={<Truck size={18} className="text-factory-600" />}
          change={`${summary.open_orders || 0} open`}
          trend="neutral"
        />
        <KPICard
          title="Total Invoices"
          value={summary.total_invoices.toLocaleString?.() || "0"}
          icon={<FileText size={18} className="text-factory-600" />}
          change={`${summary.unpaid_invoices || 0} unpaid`}
          trend={summary.unpaid_invoices > 0 ? "down" : "up"}
        />
        <KPICard
          title="Open Quotes"
          value={summary.open_quotes.toLocaleString?.() || "0"}
          icon={<FileTextIcon size={18} className="text-factory-600" />}
          change="Pending review"
          trend="neutral"
        />
      </div>

      <div className="grid gap-6 lg:grid-cols-3">
        <Card className="lg:col-span-2">
          <CardHeader className="flex items-center justify-between pb-4">
            <CardTitle className="text-base font-semibold text-slate-900">Recent Orders</CardTitle>
            <Link href="/customer/orders" className="text-sm font-medium text-factory-600 hover:text-factory-700">
              View all →
            </Link>
          </CardHeader>
          <CardContent className="p-0">
            <DataTable
              title=""
              columns={[
                { key: "so_number", label: "Order #" },
                { key: "status", label: "Status", format: (value: string) => (
                  <Badge className={ORDER_STATUS_BADGE[value] || "bg-slate-100 text-slate-600"}>
                    {value.replace(/_/g, " ")}
                  </Badge>
                )},
                { key: "order_date", label: "Order Date" },
                { key: "total_amount", label: "Total", align: "right", format: (v: number) => `Rs. ${Number(v).toLocaleString(undefined, { maximumFractionDigits: 2 })}` },
              ]}
              data={orders}
              emptyMessage="No orders found."
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
                { key: "due_date", label: "Due Date" },
                { key: "total_amount", label: "Total", align: "right", format: (v: number) => `Rs. ${Number(v).toLocaleString(undefined, { maximumFractionDigits: 2 })}` },
              ]}
              data={invoices}
              emptyMessage="No invoices found."
            />
          </CardContent>
        </Card>

        <Card className="lg:col-span-1">
          <CardHeader className="pb-4">
            <CardTitle className="text-base font-semibold text-slate-900">Recent Quotes</CardTitle>
          </CardHeader>
          <CardContent className="p-0">
            <DataTable
              title=""
              columns={[
                { key: "quote_number", label: "Quote #" },
                { key: "status", label: "Status", format: (value: string) => (
                  <Badge className={QUOTE_STATUS_BADGE[value] || "bg-slate-100 text-slate-600"}>
                    {value.replace(/_/g, " ")}
                  </Badge>
                )},
                { key: "valid_until", label: "Valid Until" },
                { key: "total_amount", label: "Total", align: "right", format: (v: number) => `Rs. ${Number(v).toLocaleString(undefined, { maximumFractionDigits: 2 })}` },
              ]}
              data={quotes}
              emptyMessage="No quotes found."
            />
          </CardContent>
        </Card>
      </div>
    </AppLayout>
  );
}
