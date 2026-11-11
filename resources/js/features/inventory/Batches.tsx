/**
 * Brixel FactoryOS — Batches / Lot Tracking
 * Track inventory by batch or lot numbers for traceability.
 */

import { Head, Link, router, usePage } from "@inertiajs/react";
import { AppLayout } from "@/components/layout";
import { PageHeader } from "@/components/layout";
import { DataTable } from "@/components/DataTable";
import { Plus, Eye, Edit, Bot } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";

interface Batch {
  id: number;
  batch_number: string;
  item_name: string;
  quantity: number;
  expiry_date: string;
  manufacturing_date: string;
  status: string;
  status_label: string;
}

const STATUS_BADGE: Record<string, string> = {
  active: "bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-200",
  expired: "bg-rose-50 text-rose-700 ring-1 ring-inset ring-rose-200",
  consumed: "bg-slate-100 text-slate-500 ring-1 ring-inset ring-slate-200",
  quarantined: "bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-200",
};

export default function Batches() {
  const { props: data } = usePage() as any;
  const batches: Batch[] = data?.batches?.data || [];
  const meta = data?.batches?.meta || {};

  return (
    <AppLayout title="Batches / Lot Tracking">
      <PageHeader
        title="Batches / Lot Tracking"
        subtitle="Track inventory by batch or lot numbers for traceability"
        breadcrumbs={[{ label: "Inventory", href: "/inventory" }, { label: "Batches / Lots" }]}
      >
        <Button onClick={() => router.visit("/inventory/batches/create")}>
          <Plus size={16} /> New Batch
        </Button>
      </PageHeader>

      <DataTable
        title="Batches"
        columns={[
          { key: "batch_number", label: "Batch #" },
          { key: "item_name", label: "Item" },
          { key: "quantity", label: "Quantity" },
          { key: "manufacturing_date", label: "Mfg Date" },
          { key: "expiry_date", label: "Expiry Date" },
          { key: "status", label: "Status", format: (value: string) => (
            <Badge className={STATUS_BADGE[value] || "bg-slate-100 text-slate-600"}>
              {value}
            </Badge>
          )},
        ]}
        data={batches}
        meta={meta}
        rowActions={[
          { label: "View", icon: <Eye size={14} />, variant: "ghost" },
          { label: "Edit", icon: <Edit size={14} />, variant: "outline" },
        ]}
      />
    </AppLayout>
  );
}
