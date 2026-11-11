/**
 * Brixel FactoryOS — Kitting
 * Kit assembly and component management for bundled products.
 */

import { Head, Link, router, usePage } from "@inertiajs/react";
import { AppLayout } from "@/components/layout";
import { PageHeader } from "@/components/layout";
import { DataTable } from "@/components/DataTable";
import { Plus, Shield, Package } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";

interface Kit {
  id: number;
  kit_number: string;
  item_name: string;
  quantity: number;
  status: string;
  created_at: string;
}

const STATUS_BADGE: Record<string, string> = {
  draft: "bg-slate-100 text-slate-500 ring-1 ring-inset ring-slate-200",
  picking: "bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-200",
  assembled: "bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-200",
  quality_check: "bg-sky-50 text-sky-700 ring-1 ring-inset ring-sky-200",
  completed: "bg-indigo-50 text-indigo-700 ring-1 ring-inset ring-indigo-200",
  cancelled: "bg-rose-50 text-rose-700 ring-1 ring-inset ring-rose-200",
};

export default function Kitting() {
  const { props: data } = usePage() as any;
  const kits = data?.kits?.data || [];

  return (
    <AppLayout title="Kitting">
      <PageHeader
        title="Kit Assembly"
        subtitle="Kit assembly and component management for bundled products"
        breadcrumbs={[{ label: "Inventory", href: "/inventory" }, { label: "Kitting" }]}
      >
        <Button onClick={() => router.visit("/inventory/create")}>
          <Plus size={16} /> New Kit
        </Button>
      </PageHeader>

      <DataTable
        title="Kit Assembly Orders"
        columns={[
          { key: "kit_number", label: "Kit #" },
          { key: "item_name", label: "Kit Item" },
          { key: "quantity", label: "Quantity", align: "right" },
          { key: "status", label: "Status", format: (value: string) => (
            <Badge className={STATUS_BADGE[value] || "bg-slate-100 text-slate-600"}>
              {value.replace(/_/g, " ")}
            </Badge>
          )},
          { key: "created_at", label: "Created" },
        ]}
        data={kits}
        rowActions={[
          { label: "View", icon: <Package size={14} />, variant: "ghost" },
          { label: "Edit", icon: <Shield size={14} />, variant: "outline" },
        ]}
      />
    </AppLayout>
  );
}
