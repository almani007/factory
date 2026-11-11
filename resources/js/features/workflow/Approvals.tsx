/**
 * Brixel FactoryOS — Workflow Approvals
 * Approval workflow management and tracking.
 */

import { Head, Link, router, usePage } from "@inertiajs/react";
import { AppLayout } from "@/components/layout";
import { PageHeader } from "@/components/layout";
import { DataTable } from "@/components/DataTable";
import { StatStrip } from "@/components/StatStrip";
import { Plus, Shield, Activity } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";

interface Approval {
  id: number;
  workflow_type: string;
  reference_number: string;
  status: string;
  requested_at: string;
  approved_at: string;
  comments: string;
  approver_name: string;
}

const STATUS_BADGE: Record<string, string> = {
  pending: "bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-200",
  approved: "bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-200",
  rejected: "bg-rose-50 text-rose-700 ring-1 ring-inset ring-rose-200",
  cancelled: "bg-slate-100 text-slate-500 ring-1 ring-inset ring-slate-200",
};

export default function Approvals() {
  const { props: data } = usePage() as any;
  const approvals = data?.approvals?.data || [];
  const summary = data?.summary || {
    total_approvals: 0,
    pending_approvals: 0,
    approved: 0,
    rejected: 0,
  };

  return (
    <AppLayout title="Workflow Approvals">
      <PageHeader
        title="Workflow Approvals"
        subtitle="Approval workflow management and tracking"
        breadcrumbs={[{ label: "Workflow", href: "/workflow/approvals" }, { label: "Approvals" }]}
      >
        <Button onClick={() => router.visit("/workflow/rules")}>
          <Plus size={16} /> New Approval Rule
        </Button>
      </PageHeader>

      <StatStrip
        items={[
          { label: "Total Approvals", value: summary.total_approvals, hint: "All approval requests" },
          { label: "Pending", value: summary.pending_approvals, hint: "Awaiting approval", tone: summary.pending_approvals > 0 ? "warning" : "success" },
          { label: "Approved", value: summary.approved, hint: "Approved requests", tone: "success" },
          { label: "Rejected", value: summary.rejected, hint: "Rejected requests", tone: "danger" },
        ]}
      />

      <DataTable
        title="Approval Requests"
        columns={[
          { key: "workflow_type", label: "Workflow Type", format: (value: string) => value.replace(/_/g, " ") },
          { key: "reference_number", label: "Reference #" },
          { key: "status", label: "Status", format: (value: string) => (
            <Badge className={STATUS_BADGE[value] || "bg-slate-100 text-slate-600"}>
              {value.replace(/_/g, " ")}
            </Badge>
          )},
          { key: "requested_at", label: "Requested At" },
          { key: "approved_at", label: "Approved At" },
          { key: "approver_name", label: "Approver" },
          { key: "comments", label: "Comments" },
        ]}
        data={approvals}
        rowActions={[
          { label: "View", icon: <Shield size={14} />, variant: "ghost" },
          { label: "Edit", icon: <Activity size={14} />, variant: "outline" },
        ]}
      />
    </AppLayout>
  );
}
