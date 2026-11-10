/**
 * Brixel FactoryOS — Dispatch Page
 * Dispatch orders and delivery tracking.
 */

import { Head, Link, usePage } from "@inertiajs/react";
import { AppLayout } from "@/components/layout";
import { PageHeader } from "@/components/layout";
import { DataTable } from "@/components/DataTable";
import { Plus } from "lucide-react";
import { Button } from "@/components/ui/button";

interface Dispatch {
  id: number;
  dispatch_number: string;
  sales_order_id: string;
  driver_name: string;
  dispatch_date: string;
  status: string;
}

export default function Dispatch() {
  const { data } = usePage() as any;
  const dispatches: Dispatch[] = data?.dispatches?.data || [];
  const meta = data?.dispatches?.meta || {};

  return (
    <AppLayout title="Dispatch & Logistics">
      <PageHeader
        title="Dispatch & Logistics"
        subtitle="Delivery planning, dispatch orders, and proof of delivery"
      >
        <Button className="bg-factory-600 text-white hover:bg-factory-500">
          <Plus size={16} /> New Dispatch
        </Button>
      </PageHeader>

      <DataTable
        title="Dispatches"
        columns={[
          { key: "dispatch_number", label: "Dispatch #" },
          { key: "sales_order_id", label: "SO #" },
          { key: "driver_name", label: "Driver" },
          { key: "dispatch_date", label: "Date" },
          { key: "status", label: "Status" },
        ]}
        data={dispatches}
        createUrl="/dispatches/create"
        meta={meta}
      />
    </AppLayout>
  );
}