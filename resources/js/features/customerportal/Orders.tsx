/**
 * Brixel FactoryOS — Customer Portal Orders
 * Customer order listing page.
 */

import { Head, Link, usePage } from "@inertiajs/react";
import { AppLayout } from "@/components/layout";
import { PageHeader } from "@/components/layout";
import { DataTable } from "@/components/DataTable";
import { Badge } from "@/components/ui/badge";

interface Order {
  id: number;
  so_number: string;
  status: string;
  order_date: string;
  delivery_date: string;
  total_amount: number;
}

const STATUS_BADGE: Record<string, string> = {
  draft: "bg-slate-100 text-slate-500",
  confirmed: "bg-amber-50 text-amber-700",
  in_production: "bg-sky-50 text-sky-700",
  completed: "bg-emerald-50 text-emerald-700",
  dispatched: "bg-indigo-50 text-indigo-700",
  delivered: "bg-emerald-50 text-emerald-700",
  cancelled: "bg-rose-50 text-rose-700",
};

export default function CustomerOrders() {
  const { props: data } = usePage() as any;
  const orders: Order[] = data?.orders?.data || [];
  const meta = data?.orders?.meta || {};

  return (
    <AppLayout title="Customer Orders">
      <PageHeader
        title="My Orders"
        subtitle="Track your sales orders and delivery status"
        breadcrumbs={[{ label: "Customer Portal", href: "/customer/dashboard" }, { label: "My Orders" }]}
      />

      <DataTable
        title="Orders"
        columns={[
          { key: "so_number", label: "Order #" },
          { key: "status", label: "Status", format: (value: string) => (
            <Badge className={STATUS_BADGE[value] || "bg-slate-100 text-slate-600"}>
              {value.replace(/_/g, " ")}
            </Badge>
          )},
          { key: "order_date", label: "Order Date" },
          { key: "delivery_date", label: "Delivery Date" },
          { key: "total_amount", label: "Total", align: "right", format: (v: number) => `Rs. ${Number(v).toLocaleString(undefined, { maximumFractionDigits: 2 })}` },
        ]}
        data={orders}
        meta={meta}
        rowActions={[
          { label: "View Details", icon: <Plus size={14} />, variant: "ghost", href: (row: any) => `/customer/orders/${row.id}` },
        ]}
        emptyMessage="No orders found."
      />
    </AppLayout>
  );
}
