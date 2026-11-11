/**
 * Brixel FactoryOS — Sustainability Waste Tracking
 * Waste tracking table.
 */

import { Head, Link, router, usePage } from "@inertiajs/react";
import { AppLayout } from "@/components/layout";
import { PageHeader } from "@/components/layout";
import { DataTable } from "@/components/DataTable";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import { Trash2, Plus } from "lucide-react";

interface Waste {
  id: number;
  entry_date: string;
  waste_type: string;
  quantity_tons: number;
  disposal_method: string;
}

export default function WasteTracking() {
  const { props: data } = usePage() as any;
  const waste: Waste[] = data?.waste?.data || [];
  const meta = data?.waste?.meta || {};

  return (
    <AppLayout title="Waste Tracking">
      <PageHeader
        title="Waste Tracking"
        subtitle="Track waste generation and disposal"
        breadcrumbs={[{ label: "Sustainability", href: "/sustainability" }, { label: "Waste Tracking" }]}
        actions={
          <Link
            href="/sustainability/waste/create"
            className="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-factory-50 text-factory-600 font-medium text-sm hover:bg-factory-100 transition-colors"
          >
            <Plus size={14} /> Add Waste Entry
          </Link>
        }
      />

      <DataTable
        title="Waste Tracking"
        columns={[
          { key: "entry_date", label: "Date" },
          { key: "waste_type", label: "Type" },
          { key: "quantity_tons", label: "Tons", align: "right", format: (v: number) => Number(v).toFixed(2) },
          { key: "disposal_method", label: "Disposal Method" },
        ]}
        data={waste}
        meta={meta}
        rowActions={[
          { label: "View", icon: <Trash2 size={14} />, variant: "ghost" },
        ]}
        emptyMessage="No waste data found."
      />
    </AppLayout>
  );
}
