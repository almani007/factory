/**
 * Brixel FactoryOS — Quality Page
 * Quality inspections and pass/fail tracking.
 */

import { Head, Link, router, usePage } from "@inertiajs/react";
import { AppLayout } from "@/components/layout";
import { PageHeader } from "@/components/layout";
import { DataTable } from "@/components/DataTable";
import { StatStrip } from "@/components/StatStrip";
import { Plus, Eye, Edit } from "lucide-react";
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
  const summary = data?.quality_summary || {
    total: 0,
    passed: 0,
    pending: 0,
    failed: 0,
  };

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
        <Button onClick={() => router.visit("/quality/create")}>
          <Plus size={16} /> New Inspection
        </Button>
      </PageHeader>

      <StatStrip
        items={[
          { label: "Inspections", value: summary.total, hint: "Recorded to date" },
          { label: "Passed", value: summary.passed, hint: "Released to next stage", tone: "success" },
          { label: "Pending / failed", value: `${summary.pending} / ${summary.failed}`, hint: "Awaiting disposition", tone: summary.failed > 0 ? "danger" : "warning" },
        ]}
      />

      <DataTable
        title="Quality Inspections"
        columns={columns}
        data={inspections}
        meta={meta}
        rowActions={[
          { label: "View", icon: <Eye size={14} />, variant: "ghost" },
          { label: "Edit", icon: <Edit size={14} />, variant: "outline" },
        ]}
      />
    </AppLayout>
  );
}