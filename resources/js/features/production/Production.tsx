/**
 * Brixel FactoryOS — Production Page
 * Production order management and work orders.
 */

import { Head, Link, router, usePage } from "@inertiajs/react";
import { AppLayout } from "@/components/layout";
import { PageHeader } from "@/components/layout";
import { DataTable } from "@/components/DataTable";
import { StatStrip } from "@/components/StatStrip";
import { Plus, Eye, Edit } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";

interface ProductionOrder {
  id: number;
  order_number: string;
  item_name: string;
  planned_quantity: number;
  status: string;
  planned_start_date: string;
}

const STATUS_CONFIG: Record<string, { label: string; variant: string }> = {
  draft: { label: "Draft", variant: "default" },
  pending_approval: { label: "Pending Approval", variant: "warning" },
  approved: { label: "Approved", variant: "info" },
  material_reserved: { label: "Material Reserved", variant: "info" },
  in_production: { label: "In Production", variant: "default" },
  quality_inspection: { label: "Quality Inspection", variant: "warning" },
  completed: { label: "Completed", variant: "success" },
  closed: { label: "Closed", variant: "default" },
  cancelled: { label: "Cancelled", variant: "error" },
};

export default function Production() {
  const { props: data } = usePage() as any;
  const orders: ProductionOrder[] = data?.production_orders?.data || [];
  const meta = data?.production_orders?.meta || {};
  const summary = data?.production_summary || {
    total: 0,
    active: 0,
    completed: 0,
    late: 0,
  };

  const columns = [
    { key: "order_number", label: "Order #" },
    { key: "item_name", label: "Product" },
    { key: "planned_quantity", label: "Planned Qty" },
    { key: "status", label: "Status" },
    { key: "planned_start_date", label: "Start Date" },
  ];

  return (
    <AppLayout title="Production Orders">
      <PageHeader
        title="Production Orders"
        subtitle="Work orders, material issues, and production tracking"
      >
        <Button onClick={() => router.visit("/production/create")}>
          <Plus size={16} /> New Production Order
        </Button>
      </PageHeader>

      <StatStrip
        items={[
          { label: "Work orders", value: summary.total, hint: "All time" },
          { label: "In production", value: summary.active, hint: "On the floor now", tone: "warning" },
          { label: "Late orders", value: summary.late, hint: "Past planned end date", tone: summary.late > 0 ? "danger" : "success" },
        ]}
      />

      <DataTable
        title="Production schedule"
        columns={columns}
        data={orders}
        meta={meta}
        rowActions={[
          { label: "View", icon: <Eye size={14} />, variant: "ghost" },
          { label: "Edit", icon: <Edit size={14} />, variant: "outline" },
        ]}
      />
    </AppLayout>
  );
}