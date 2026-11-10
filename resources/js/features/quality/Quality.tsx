/**
 * Brixel FactoryOS — Quality Page
 * Quality inspections and pass/fail tracking.
 */

import { Head, Link, usePage } from "@inertiajs/react";
import { AppLayout } from "@/components/layout";
import { PageHeader } from "@/components/layout";
import { DataTable } from "@/components/DataTable";
import { Plus } from "lucide-react";
import { Button } from "@/components/ui/button";

interface QualityInspection {
  id: number;
  reference: string;
  inspection_type: string;
  item_name: string;
  status: string;
  inspected_at: string;
}

export default function Quality() {
  const { props: data } = usePage() as any;
  const inspections: QualityInspection[] = data?.inspections?.data || [];
  const meta = data?.inspections?.meta || {};

  const columns = [
    { key: "reference", label: "Reference" },
    { key: "inspection_type", label: "Type" },
    { key: "item_name", label: "Item" },
    { key: "status", label: "Status" },
    { key: "inspected_at", label: "Inspected At" },
  ];

  return (
    <AppLayout title="Quality Control">
      <PageHeader
        title="Quality Control"
        subtitle="Incoming, in-process, and final product inspections"
      >
        <Button className="bg-factory-600 text-white hover:bg-factory-500">
          <Plus size={16} /> New Inspection
        </Button>
      </PageHeader>

      <DataTable
        title="Quality Inspections"
        columns={columns}
        data={inspections}
        createUrl="/quality/inspections/create"
        meta={meta}
      />
    </AppLayout>
  );
}