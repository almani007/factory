/**
 * Brixel FactoryOS — HR Leave Management
 * Employee leave request tracking and approval.
 */

import { Head, Link, router, usePage } from "@inertiajs/react";
import { AppLayout } from "@/components/layout";
import { PageHeader } from "@/components/layout";
import { DataTable } from "@/components/DataTable";
import { StatStrip } from "@/components/StatStrip";
import { Plus, Eye, Edit } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";

interface LeaveRequest {
  id: number;
  leave_number: string;
  employee_code: string;
  employee_name: string;
  leave_type: string;
  leave_type_label: string;
  start_date: string;
  end_date: string;
  status: string;
  status_label: string;
}

const STATUS_BADGE: Record<string, string> = {
  pending: "bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-200",
  approved: "bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-200",
  rejected: "bg-rose-50 text-rose-700 ring-1 ring-inset ring-rose-200",
  cancelled: "bg-slate-100 text-slate-500 ring-1 ring-inset ring-slate-200",
};

export default function LeaveManagement() {
  const { props: data } = usePage() as any;
  const leaveRequests: LeaveRequest[] = data?.leave_requests?.data || [];
  const meta = data?.leave_requests?.meta || {};
  const summary = data?.leave_summary || { total: 0, pending: 0, approved: 0 };

  const totalRequests = String(summary.total || 0);
  const pendingRequests = String(summary.pending || 0);
  const approvedRequests = String(summary.approved || 0);

  return (
    <AppLayout title="Leave Management">
      <PageHeader
        title="Leave Management"
        subtitle="Employee leave request tracking and approval"
        breadcrumbs={[{ label: "HR", href: "/hr" }, { label: "Leave Management" }]}
      >
        <Button onClick={() => router.visit("/hr/leave/create")}>
          <Plus size={16} /> New Leave Request
        </Button>
      </PageHeader>

      <StatStrip
        items={[
          { label: "Total Requests", value: totalRequests, hint: "All leave requests" },
          { label: "Pending", value: pendingRequests, hint: "Awaiting approval", tone: summary.pending > 0 ? "warning" : "success" },
          { label: "Approved", value: approvedRequests, hint: "Approved", tone: "success" },
        ]}
      />

      <DataTable
        title="Leave Requests"
        columns={[
          { key: "leave_number", label: "Leave #" },
          { key: "employee_code", label: "Employee Code" },
          { key: "employee_name", label: "Employee Name" },
          { key: "leave_type", label: "Leave Type", format: (value: string) => value.replace(/_/g, " ") },
          { key: "start_date", label: "Start Date" },
          { key: "end_date", label: "End Date" },
          { key: "status", label: "Status", format: (value: string) => (
            <Badge className={STATUS_BADGE[value] || "bg-slate-100 text-slate-600"}>
              {value.replace(/_/g, " ")}
            </Badge>
          )},
        ]}
        data={leaveRequests}
        meta={meta}
        rowActions={[
          { label: "View", icon: <Eye size={14} />, variant: "ghost" },
          { label: "Edit", icon: <Edit size={14} />, variant: "outline" },
        ]}
        emptyMessage="No leave requests found."
      />
    </AppLayout>
  );
}
