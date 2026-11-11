/**
 * Brixel FactoryOS — Consignment
 * Consignment inventory management — supplier-owned stock at your location.
 */

import { Head, Link, router, usePage } from "@inertiajs/react";
import { AppLayout } from "@/components/layout";
import { PageHeader } from "@/components/layout";
import { DataTable } from "@/components/DataTable";
import { Plus, Shield, Truck } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";

interface Consignment {
  id: number;
  reference: string;
  supplier_name: string;
  item_name: string;
  quantity_on_hand: number;
  quantity_consumed: number;
  status: string;
  last_reconciled: string;
}

const STATUS_BADGE: Record<string, string> = {
  active: "bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-200",
  pending_reconciliation: "bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-200",
  reconciled: "bg-indigo-50 text-indigo-700 ring-1 ring-inset ring-indigo-200",
  closed: "bg-slate-100 text-slate-500 ring-1 ring-inset ring-slate-200",
};

export default function Consignment() {
  const { props: data } = usePage() as any;
  const consignments = data?.consignments?.data || [];

  return (
    <AppLayout title="Consignment Inventory">
      <PageHeader
        title="Consignment Inventory"
        subtitle="Supplier-owned stock held at your location"
        breadcrumbs={[{ label: "Inventory", href: "/inventory" }, { label: "Consignment" }]}
      >
        <Button onClick={() => router.visit("/inventory/create")}>
          <Plus size={16} /> New Consignment
        </Button>
      </PageHeader>

      <DataTable
        title="Consignment Inventory"
        columns={[
          { key: "reference", label: "Reference" },
          { key: "supplier_name", label: "Supplier" },
          { key: "item_name", label: "Item" },
          { key: "quantity_on_hand", label: "On Hand", align: "right" },
          { key: "quantity_consumed", label: "Consumed", align: "right" },
          { key: "last_reconciled", label: "Last Reconciled" },
          { key: "status", label: "Status", format: (value: string) => (
            <Badge className={STATUS_BADGE[value] || "bg-slate-100 text-slate-600"}>
              {value.replace(/_/g, " ")}
            </Badge>
          )},
        ]}
        data={consignments}
        rowActions={[
          { label: "View", icon: <Truck size={14} />, variant: "ghost" },
          { label: "Edit", icon: <Shield size={14} />, variant: "outline" },
        ]}
      />
    </AppLayout>
  );
}
