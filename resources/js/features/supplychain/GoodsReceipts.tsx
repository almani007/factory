/**
 * Brixel FactoryOS — Goods Receipts
 * Receiving goods from suppliers against purchase orders.
 */

import { Head, Link, router, usePage } from "@inertiajs/react";
import { AppLayout } from "@/components/layout";
import { PageHeader } from "@/components/layout";
import { DataTable } from "@/components/DataTable";
import { StatStrip } from "@/components/StatStrip";
import { Plus, Eye, Edit, Truck } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";

interface GoodsReceipt {
  id: number;
  receipt_number: string;
  po_number: string;
  supplier_name: string;
  receipt_date: string;
  status: string;
  status_label: string;
}

const STATUS_BADGE: Record<string, string> = {
  received: "bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-200",
  pending: "bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-200",
  partial: "bg-sky-50 text-sky-700 ring-1 ring-inset ring-sky-200",
  cancelled: "bg-rose-50 text-rose-700 ring-1 ring-inset ring-rose-200",
};

export default function GoodsReceipts() {
  const { props: data } = usePage() as any;
  const receipts: GoodsReceipt[] = data?.goods_receipts?.data || [];
  const meta = data?.goods_receipts?.meta || {};
  const summary = data?.gr_summary || { total: 0, received: 0, pending: 0 };

  return (
    <AppLayout title="Goods Receipts">
      <PageHeader
        title="Goods Receipts"
        subtitle="Receiving goods from suppliers against purchase orders"
        breadcrumbs={[{ label: "Supply Chain", href: "/supply-chain/goods-receipts" }, { label: "Goods Receipts" }]}
      >
        <Button onClick={() => router.visit("/supply-chain/goods-receipts/create")}>
          <Plus size={16} /> New Goods Receipt
        </Button>
      </PageHeader>

      <StatStrip
        items={[
          { label: "Total Receipts", value: summary.total, hint: "All receipts" },
          { label: "Received", value: summary.received, hint: "Fully received", tone: "success" },
          { label: "Pending", value: summary.pending, hint: "Awaiting receipt", tone: summary.pending > 0 ? "warning" : "success" },
        ]}
      />

      <DataTable
        title="Goods Receipts"
        columns={[
          { key: "receipt_number", label: "Receipt #" },
          { key: "po_number", label: "PO #" },
          { key: "supplier_name", label: "Supplier" },
          { key: "receipt_date", label: "Receipt Date" },
          { key: "status", label: "Status", format: (value: string) => (
            <Badge className={STATUS_BADGE[value] || "bg-slate-100 text-slate-600"}>
              {value.replace(/_/g, " ")}
            </Badge>
          )},
        ]}
        data={receipts}
        meta={meta}
        rowActions={[
          { label: "View", icon: <Eye size={14} />, variant: "ghost" },
          { label: "Edit", icon: <Edit size={14} />, variant: "outline" },
        ]}
      />
    </AppLayout>
  );
}
