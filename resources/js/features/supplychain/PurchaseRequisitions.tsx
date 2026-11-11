/**
 * Brixel FactoryOS — Purchase Requisitions
 * Internal purchase requests routed for approval and sourcing.
 */

import { Head, Link, router, usePage } from "@inertiajs/react";
import { AppLayout } from "@/components/layout";
import { PageHeader } from "@/components/layout";
import { DataTable } from "@/components/DataTable";
import { StatStrip } from "@/components/StatStrip";
import { Plus, Eye, Edit, ClipboardList } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";

interface Requisition {
  id: number;
  requisition_number: string;
  requested_by: string;
  required_date: string;
  status: string;
  status_label: string;
  total_amount: number;
}

const STATUS_BADGE: Record<string, string> = {
  approved: "bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-200",
  pending: "bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-200",
  rejected: "bg-rose-50 text-rose-700 ring-1 ring-inset ring-rose-200",
  completed: "bg-indigo-50 text-indigo-700 ring-1 ring-inset ring-indigo-200",
};

export default function PurchaseRequisitions() {
  const { props: data } = usePage() as any;
  const requisitions: Requisition[] = data?.requisitions?.data || [];
  const meta = data?.requisitions?.meta || {};
  const summary = data?.pr_summary || { total: 0, approved: 0, pending: 0 };

  return (
    <AppLayout title="Purchase Requisitions">
      <PageHeader
        title="Purchase Requisitions"
        subtitle="Internal purchase requests routed for approval and sourcing"
        breadcrumbs={[{ label: "Supply Chain", href: "/supply-chain/purchase-requisitions" }, { label: "Requisitions" }]}
      >
        <Button onClick={() => router.visit("/supply-chain/purchase-requisitions/create")}>
          <Plus size={16} /> New Requisition
        </Button>
      </PageHeader>

      <StatStrip
        items={[
          { label: "Total Requisitions", value: summary.total, hint: "All requests" },
          { label: "Approved", value: summary.approved, hint: "Approved for purchasing", tone: "success" },
          { label: "Pending", value: summary.pending, hint: "Awaiting approval", tone: summary.pending > 0 ? "warning" : "success" },
        ]}
      />

      <DataTable
        title="Purchase Requisitions"
        columns={[
          { key: "requisition_number", label: "Requisition #" },
          { key: "requested_by", label: "Requested By" },
          { key: "required_date", label: "Required Date" },
          { key: "status", label: "Status", format: (value: string) => (
            <Badge className={STATUS_BADGE[value] || "bg-slate-100 text-slate-600"}>
              {value.replace(/_/g, " ")}
            </Badge>
          )},
          { key: "total_amount", label: "Total Amount", align: "right", format: (v: number) => `Rs. ${Number(v).toLocaleString(undefined, { maximumFractionDigits: 2 })}` },
        ]}
        data={requisitions}
        meta={meta}
        rowActions={[
          { label: "View", icon: <Eye size={14} />, variant: "ghost" },
          { label: "Edit", icon: <Edit size={14} />, variant: "outline" },
        ]}
      />
    </AppLayout>
  );
}
