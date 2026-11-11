/**
 * Brixel FactoryOS — MRP Planning
 * Material Requirements Planning — identify reorder needs and run MRP.
 */

import { Head, Link, router, usePage } from "@inertiajs/react";
import { AppLayout } from "@/components/layout";
import { PageHeader } from "@/components/layout";
import { DataTable } from "@/components/DataTable";
import { StatStrip } from "@/components/StatStrip";
import { Plus, Activity, Play } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";

interface MRPItem {
  sku: string;
  name: string;
  category: string;
  on_hand: number;
  reorder_level: number;
  needs_reorder: boolean;
  shortfall: number;
  priority: string;
}

const PRIORITY_BADGE: Record<string, string> = {
  critical: "bg-rose-50 text-rose-700 ring-1 ring-inset ring-rose-200",
  high: "bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-200",
  normal: "bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-200",
};

export default function MRP() {
  const { props: data } = usePage() as any;
  const items: MRPItem[] = data?.items || [];
  const summary = data?.summary || { total_items: 0, needs_reorder: 0, critical: 0 };

  return (
    <AppLayout title="MRP Planning">
      <PageHeader
        title="MRP Planning"
        subtitle="Material Requirements Planning — identify reorder needs and run MRP"
        breadcrumbs={[{ label: "Operations", href: "/mrp" }, { label: "MRP Planning" }]}
        actions={
          <>
            <Button onClick={() => router.visit("/mrp/run")} variant="outline">
              <Play size={16} /> Run MRP
            </Button>
          </>
        }
      />

      <StatStrip
        items={[
          { label: "Total Items", value: summary.total_items, hint: "All SKUs tracked" },
          { label: "Needs Reorder", value: summary.needs_reorder, hint: "Below reorder level", tone: summary.needs_reorder > 0 ? "warning" : "success" },
          { label: "Critical", value: summary.critical, hint: "Severe shortage", tone: summary.critical > 0 ? "danger" : "success" },
        ]}
      />

      <DataTable
        title="MRP Items"
        columns={[
          { key: "sku", label: "SKU" },
          { key: "name", label: "Item Name" },
          { key: "category", label: "Category" },
          { key: "on_hand", label: "On Hand", align: "right" },
          { key: "reorder_level", label: "Reorder Level", align: "right" },
          { key: "shortfall", label: "Shortfall", align: "right" },
          { key: "priority", label: "Priority", format: (value: string) => (
            <Badge className={PRIORITY_BADGE[value] || "bg-slate-100 text-slate-600"}>
              {value}
            </Badge>
          )},
        ]}
        data={items.filter((i: MRPItem) => i.needs_reorder)}
        emptyMessage="All items are at or above reorder levels. No action needed."
      />
    </AppLayout>
  );
}
