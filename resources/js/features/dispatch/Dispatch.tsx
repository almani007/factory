/**
 * Brixel FactoryOS — Dispatch Page
 * Dispatch orders and delivery tracking.
 */

import { Head, Link, usePage } from "@inertiajs/react";
import { AppLayout } from "@/components/layout";
import { PageHeader } from "@/components/layout";
import { DataTable } from "@/components/DataTable";
import { StatStrip } from "@/components/StatStrip";
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
  const { props: data } = usePage() as any;
  const dispatches: Dispatch[] = data?.dispatches?.data || [];
  const meta = data?.dispatches?.meta || {};
  const summary = data?.dispatch_summary || {
    total: 0,
    in_transit: 0,
    waiting: 0,
  };

  return (
    <AppLayout title="Dispatch & Logistics">
      <PageHeader
        title="Dispatch & Logistics"
        subtitle="Delivery planning, dispatch orders, and proof of delivery"
      >
        <Button>
          <Plus size={16} /> New Dispatch
        </Button>
      </PageHeader>

      <StatStrip
        items={[
          { label: "Dispatches", value: summary.total, hint: "All time" },
          { label: "In transit", value: summary.in_transit, hint: "On the road", tone: "warning" },
          { label: "Awaiting dispatch", value: summary.waiting, hint: "Packed, not loaded", tone: "danger" },
        ]}
      />

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
        meta={meta}
      />
    </AppLayout>
  );
}