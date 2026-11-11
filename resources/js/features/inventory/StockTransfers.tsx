/**
 * Brixel FactoryOS — Inventory — Stock Transfers
 * Transfer stock between warehouses.
 */

import { Head, Link, router, usePage } from "@inertiajs/react";
import { AppLayout } from "@/components/layout";
import { PageHeader } from "@/components/layout";
import { DataTable } from "@/components/DataTable";
import { StatStrip } from "@/components/StatStrip";
import { Plus, Shirt, Eye, Edit } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";

interface StockTransfer {
  id: number;
  transfer_number: string;
  from_warehouse: string;
  to_warehouse: string;
  transfer_date: string;
  status: string;
  status_label: string;
}

const STATUS_BADGE: Record<string, string> = {
  completed: "bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-200",
  pending: "bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-200",
  in_transit: "bg-sky-50 text-sky-700 ring-1 ring-inset ring-sky-200",
  cancelled: "bg-rose-50 text-rose-700 ring-1 ring-inset ring-rose-200",
};

export default function StockTransfers() {
  const { props: data } = usePage() as any;
  const transfers: StockTransfer[] = data?.transfers?.data || [];
  const meta = data?.transfers?.meta || {};
  const summary = data?.transfer_summary || { total: 0, completed: 0, pending: 0 };

  const totalTransfersStr = String(summary.total || 0);
  const completedTransfersStr = String(summary.completed || 0);
  const pendingTransfersStr = String(summary.pending || 0);

  return (
    <AppLayout title="Stock Transfers">
      <PageHeader
        title="Stock Transfers"
        subtitle="Transfer inventory between warehouses"
        breadcrumbs={[{ label: "Inventory", href: "/inventory" }, { label: "Stock Transfers" }]}
      >
        <Button onClick={() => router.visit("/inventory/transfers/create")}>
          <Plus size={16} /> New Transfer
        </Button>
      </PageHeader>

      <StatStrip
        items={[
          { label: "Total Transfers", value: totalTransfersStr, hint: "All transfers" },
          { label: "Completed", value: completedTransfersStr, hint: "Finished", tone: "success" },
          { label: "Pending", value: pendingTransfersStr, hint: "Awaiting processing", tone: summary.pending > 0 ? "warning" : "success" },
        ]}
      />

      <DataTable
        title="Stock Transfers"
        columns={[
          { key: "transfer_number", label: "Transfer #" },
          { key: "from_warehouse", label: "From Warehouse" },
          { key: "to_warehouse", label: "To Warehouse" },
          { key: "transfer_date", label: "Date" },
          { key: "status", label: "Status", format: (value: string) => (
            <Badge className={STATUS_BADGE[value] || "bg-slate-100 text-slate-600"}>
              {value.replace(/_/g, " ")}
            </Badge>
          )},
        ]}
        data={transfers}
        meta={meta}
        rowActions={[
          { label: "View", icon: <Eye size={14} />, variant: "ghost" },
          { label: "Edit", icon: <Edit size={14} />, variant: "outline" },
        ]}
        emptyMessage="No stock transfers found."
      />
    </AppLayout>
  );
}
