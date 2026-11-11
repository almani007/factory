/**
 * Brixel FactoryOS — Cross-docking
 * Cross-docking operations for direct transfer from receiving to shipping.
 */

import { Head, Link, router, usePage } from "@inertiajs/react";
import { AppLayout } from "@/components/layout";
import { PageHeader } from "@/components/layout";
import { DataTable } from "@/components/DataTable";
import { Plus, Truck, Shield } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";

interface CrossDock {
  id: number;
  reference: string;
  item_name: string;
  quantity: number;
  received_date: string;
  ship_date: string;
  status: string;
}

const STATUS_BADGE: Record<string, string> = {
  received: "bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-200",
  in_transit: "bg-sky-50 text-sky-700 ring-1 ring-inset ring-sky-200",
  shipped: "bg-indigo-50 text-indigo-700 ring-1 ring-inset ring-indigo-200",
  completed: "bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-200",
  cancelled: "bg-rose-50 text-rose-700 ring-1 ring-inset ring-rose-200",
};

export default function CrossDocking() {
  const { props: data } = usePage() as any;
  const crossDocks = data?.cross_docks?.data || [];

  return (
    <AppLayout title="Cross-docking">
      <PageHeader
        title="Cross-docking Operations"
        subtitle="Direct transfer from receiving to shipping without storage"
        breadcrumbs={[{ label: "Inventory", href: "/inventory" }, { label: "Cross-docking" }]}
      >
        <Button onClick={() => router.visit("/inventory/create")}>
          <Plus size={16} /> New Cross-dock
        </Button>
      </PageHeader>

      <DataTable
        title="Cross-dock Operations"
        columns={[
          { key: "reference", label: "Reference" },
          { key: "item_name", label: "Item" },
          { key: "quantity", label: "Quantity", align: "right" },
          { key: "received_date", label: "Received Date" },
          { key: "ship_date", label: "Ship Date" },
          { key: "status", label: "Status", format: (value: string) => (
            <Badge className={STATUS_BADGE[value] || "bg-slate-100 text-slate-600"}>
              {value.replace(/_/g, " ")}
            </Badge>
          )},
        ]}
        data={crossDocks}
        rowActions={[
          { label: "View", icon: <Truck size={14} />, variant: "ghost" },
          { label: "Edit", icon: <Shield size={14} />, variant: "outline" },
        ]}
      />
    </AppLayout>
  );
}
