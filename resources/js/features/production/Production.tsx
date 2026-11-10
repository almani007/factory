/**
 * Brixel FactoryOS — Production Page
 * Production order management and work orders.
 */

import { Head, Link, usePage } from "@inertiajs/react";
import { AppLayout } from "@/components/layout";
import { PageHeader } from "@/components/layout";
import { DataTable } from "@/components/DataTable";
import { Plus } from "lucide-react";
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
  const { data } = usePage() as any;
  const orders: ProductionOrder[] = data?.production_orders?.data || [];
  const meta = data?.production_orders?.meta || {};

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
        <Button className="bg-factory-600 text-white hover:bg-factory-500">
          <Plus size={16} /> New Production Order
        </Button>
      </PageHeader>

      <DataTable
        title="Production Orders"
        columns={columns}
        data={orders}
        createUrl="/production-orders/create"
        meta={meta}
      />
    </AppLayout>
  );
}