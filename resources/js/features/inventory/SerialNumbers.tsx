/**
 * Brixel FactoryOS — Serial Numbers
 * Track individual serialized items for warranty and traceability.
 */

import { Head, Link, router, usePage } from "@inertiajs/react";
import { AppLayout } from "@/components/layout";
import { PageHeader } from "@/components/layout";
import { DataTable } from "@/components/DataTable";
import { Plus, Eye, Edit, Target } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";

interface Serial {
  id: number;
  serial_number: string;
  item_name: string;
  status: string;
  status_label: string;
  warranty_expiry: string;
  current_location: string;
}

const STATUS_BADGE: Record<string, string> = {
  in_stock: "bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-200",
  sold: "bg-slate-100 text-slate-500 ring-1 ring-inset ring-slate-200",
  in_repair: "bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-200",
  scrapped: "bg-rose-50 text-rose-700 ring-1 ring-inset ring-rose-200",
};

export default function SerialNumbers() {
  const { props: data } = usePage() as any;
  const serials: Serial[] = data?.serials?.data || [];
  const meta = data?.serials?.meta || {};

  return (
    <AppLayout title="Serial Numbers">
      <PageHeader
        title="Serial Numbers"
        subtitle="Track individual serialized items for warranty and traceability"
        breadcrumbs={[{ label: "Inventory", href: "/inventory" }, { label: "Serial Numbers" }]}
      >
        <Button onClick={() => router.visit("/inventory/serials/create")}>
          <Plus size={16} /> New Serial
        </Button>
      </PageHeader>

      <DataTable
        title="Serial Numbers"
        columns={[
          { key: "serial_number", label: "Serial #" },
          { key: "item_name", label: "Item" },
          { key: "current_location", label: "Location" },
          { key: "warranty_expiry", label: "Warranty Expiry" },
          { key: "status", label: "Status", format: (value: string) => (
            <Badge className={STATUS_BADGE[value] || "bg-slate-100 text-slate-600"}>
              {value.replace(/_/g, " ")}
            </Badge>
          )},
        ]}
        data={serials}
        meta={meta}
        rowActions={[
          { label: "View", icon: <Eye size={14} />, variant: "ghost" },
          { label: "Edit", icon: <Edit size={14} />, variant: "outline" },
        ]}
      />
    </AppLayout>
  );
}
