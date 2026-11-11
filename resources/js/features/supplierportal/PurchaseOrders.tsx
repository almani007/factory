/**
 * Brixel FactoryOS — Supplier Portal Purchase Orders
 * Supplier PO listing page.
 */

import { Head, Link, usePage } from "@inertiajs/react";
import { AppLayout } from "@/components/layout";
import { PageHeader } from "@/components/layout";
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

const STATUS_BADGE: Record<string, string> = {
  draft: "bg-slate-100 text-slate-500",
  submitted: "bg-amber-50 text-amber-700",
  approved: "bg-emerald-50 text-emerald-700",
  received: "bg-indigo-50 text-indigo-700",
  completed: "bg-emerald-50 text-emerald-700",
  cancelled: "bg-rose-50 text-rose-700",
};

export default function SupplierPurchaseOrders() {
  const { props: data } = usePage() as any;
  const pos: PO[] = data?.pos?.data || [];
  const meta = data?.pos?.meta || {};

  return (
    <AppLayout title="Purchase Orders">
      <PageHeader
        title="Purchase Orders"
        subtitle="Track your purchase orders from customers"
        breadcrumbs={[{ label: "Supplier Portal", href: "/supplier/dashboard" }, { label: "Purchase Orders" }]}
      />

      <DataTable
        title="Purchase Orders"
        columns={[
          { key: "po_number", label: "PO #" },
          { key: "status", label: "Status", format: (value: string) => (
            <Badge className={STATUS_BADGE[value] || "bg-slate-100 text-slate-600"}>
              {value.replace(/_/g, " ")}
            </Badge>
          )},
          { key: "order_date", label: "Order Date" },
          { key: "expected_delivery_date", label: "Expected Delivery" },
          { key: "total_amount", label: "Total", align: "right", format: (v: number) => `Rs. ${Number(v).toLocaleString(undefined, { maximumFractionDigits: 2 })}` },
        ]}
        data={pos}
        meta={meta}
        rowActions={[
          { label: "View Details", icon: <Plus size={14} />, variant: "ghost", href: (row: any) => `/supplier/purchase-orders/${row.id}` },
        ]}
        emptyMessage="No purchase orders found."
      />
    </AppLayout>
  );
}
