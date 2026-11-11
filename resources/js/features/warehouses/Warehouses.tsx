/**
 * Brixel FactoryOS — Warehouses
 * Warehouse master data, locations, and storage management.
 */

import { Head, Link, router, usePage } from "@inertiajs/react";
import { AppLayout } from "@/components/layout";
import { PageHeader } from "@/components/layout";
import { DataTable } from "@/components/DataTable";
import { StatStrip } from "@/components/StatStrip";
import { Plus, Eye, Edit, Building } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";

interface Warehouse {
  id: number;
  code: string;
  name: string;
  type: string;
  location: string;
  status: string;
}

const TYPE_BADGE: Record<string, string> = {
  raw_material: "bg-blue-50 text-blue-700 ring-1 ring-inset ring-blue-200",
  finished_goods: "bg-indigo-50 text-indigo-700 ring-1 ring-inset ring-indigo-200",
  packaging: "bg-purple-50 text-purple-700 ring-1 ring-inset ring-purple-200",
  quarantine: "bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-200",
};

export default function Warehouses() {
  const { props: data } = usePage() as any;
  const warehouses: Warehouse[] = data?.warehouses?.data || [];
  const meta = data?.warehouses?.meta || {};
  const summary = data?.warehouse_summary || { total: 0, active: 0 };

  return (
    <AppLayout title="Warehouses">
      <PageHeader
        title="Warehouses"
        subtitle="Warehouse master data, locations, and storage management"
        breadcrumbs={[{ label: "Warehouses", href: "/warehouses" }]}
      >
        <Button onClick={() => router.visit("/warehouses/create")}>
          <Plus size={16} /> New Warehouse
        </Button>
      </PageHeader>

      <StatStrip
        items={[
          { label: "Total Warehouses", value: summary.total, hint: "All locations" },
          { label: "Active", value: summary.active, hint: "Operational", tone: "success" },
        ]}
      />

      <DataTable
        title="Warehouses"
        columns={[
          { key: "code", label: "Warehouse Code" },
          { key: "name", label: "Warehouse Name" },
          { key: "type", label: "Type", format: (value: string) => (
            <Badge className={TYPE_BADGE[value] || "bg-slate-100 text-slate-600"}>
              {value.replace(/_/g, " ")}
            </Badge>
          )},
          { key: "location", label: "Location" },
          { key: "status", label: "Status", format: (value: string) => (
            <Badge className={value === "active" ? "bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-200" : "bg-slate-100 text-slate-500 ring-1 ring-inset ring-slate-200"}>
              {value}
            </Badge>
          )},
        ]}
        data={warehouses}
        meta={meta}
        rowActions={[
          { label: "View", icon: <Eye size={14} />, variant: "ghost" },
          { label: "Edit", icon: <Edit size={14} />, variant: "outline" },
        ]}
      />
    </AppLayout>
  );
}
