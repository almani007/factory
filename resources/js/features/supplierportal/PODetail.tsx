/**
 * Brixel FactoryOS — Supplier Portal PO Detail
 * View detailed PO information and line items.
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

interface Line {
  id: number;
  sku: string;
  item_name: string;
  quantity: number;
  unit_price: number;
  total_price: number;
}

const STATUS_BADGE: Record<string, string> = {
  draft: "bg-slate-100 text-slate-500",
  submitted: "bg-amber-50 text-amber-700",
  approved: "bg-emerald-50 text-emerald-700",
  received: "bg-indigo-50 text-indigo-700",
  completed: "bg-emerald-50 text-emerald-700",
  cancelled: "bg-rose-50 text-rose-700",
};

export default function SupplierPODetail() {
  const { props: data } = usePage() as any;
  const po: PO = data?.po;
  const lines: Line[] = data?.lines?.data || [];

  return (
    <AppLayout title="PO Detail">
      <PageHeader
        title="PO #{po?.po_number}"
        subtitle="Purchase order details and line items"
        breadcrumbs={[
          { label: "Supplier Portal", href: "/supplier/dashboard" },
          { label: "Purchase Orders", href: "/supplier/purchase-orders" },
          { label: `PO #${po?.po_number}` },
        ]}
        actions={
          <Link
            href="/supplier/purchase-orders"
            className="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-slate-200 bg-white px-3 text-sm font-medium text-slate-700 shadow-sm transition-colors hover:bg-slate-50"
          >
            <ArrowLeft size={14} /> Back to POs
          </Link>
        }
      />

      <div className="mb-8 grid grid-cols-1 md:grid-cols-3 gap-6">
        <Card>
          <CardHeader>
            <CardTitle className="text-base font-semibold text-slate-900">PO Summary</CardTitle>
          </CardHeader>
          <CardContent className="p-5 pt-0 space-y-3">
            <div className="flex items-center justify-between">
              <span className="text-sm font-medium text-slate-700">PO Number</span>
              <span className="text-sm font-medium text-slate-900">{po?.po_number}</span>
            </div>
            <div className="flex items-center justify-between">
              <span className="text-sm font-medium text-slate-600">Status</span>
              <Badge className={STATUS_BADGE[po?.status || ""] || "bg-slate-100 text-slate-600"}>
                {po?.status?.replace(/_/g, " ")}
              </Badge>
            </div>
            <div className="flex items-center justify-between">
              <span className="text-sm font-medium text-slate-600">Order Date</span>
              <span className="text-sm font-medium text-slate-900">{po?.order_date}</span>
            </div>
            <div className="flex items-center justify-between">
              <span className="text-sm font-medium text-slate-600">Expected Delivery</span>
              <span className="text-sm font-medium text-slate-900">{po?.expected_delivery_date}</span>
            </div>
            <div className="flex items-center justify-between border-t border-slate-100 pt-3">
              <span className="text-sm font-semibold text-slate-900">Total Amount</span>
              <span className="text-sm font-semibold text-slate-900">
                Rs. {Number(po?.total_amount || 0).toLocaleString(undefined, { maximumFractionDigits: 2 })}
              </span>
            </div>
          </CardContent>
        </Card>
      </div>

      <Card>
        <CardHeader>
          <CardTitle className="text-base font-semibold text-slate-900">PO Line Items</CardTitle>
        </CardHeader>
        <CardContent className="p-0">
          <DataTable
            title=""
            columns={[
              { key: "sku", label: "SKU" },
              { key: "item_name", label: "Item Name" },
              { key: "quantity", label: "Quantity", align: "right" },
              { key: "unit_price", label: "Unit Price", align: "right", format: (v: number) => `Rs. ${Number(v).toLocaleString(undefined, { maximumFractionDigits: 2 })}` },
              { key: "total_price", label: "Total Price", align: "right", format: (v: number) => `Rs. ${Number(v).toLocaleString(undefined, { maximumFractionDigits: 2 })}` },
            ]}
            data={lines}
            emptyMessage="No line items found."
          />
        </CardContent>
      </Card>
    </AppLayout>
  );
}
