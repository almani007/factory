/**
 * Brixel FactoryOS — HR Shift Management
 * Employee shift scheduling and management.
 */

import { Head, Link, router, usePage } from "@inertiajs/react";
import { AppLayout } from "@/components/layout";
import { PageHeader } from "@/components/layout";
import { DataTable } from "@/components/DataTable";
import { Plus, Clock, Eye, Edit } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";

interface Shift {
  id: number;
  shift_name: string;
  shift_date: string;
  start_time: string;
  end_time: string;
  employee_name: string;
  status: string;
  status_label: string;
  location: string;
}

const STATUS_BADGE: Record<string, string> = {
  scheduled: "bg-sky-50 text-sky-700 ring-1 ring-inset ring-sky-200",
  completed: "bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-200",
  missed: "bg-rose-50 text-rose-700 ring-1 ring-inset ring-rose-200",
  cancelled: "bg-slate-100 text-slate-500 ring-1 ring-inset ring-slate-200",
};

export default function ShiftManagement() {
  const { props: data } = usePage() as any;
  const shifts: Shift[] = data?.shifts?.data || [];
  const meta = data?.shifts?.meta || {};

  return (
    <AppLayout title="Shift Management">
      <PageHeader
        title="Shift Management"
        subtitle="Employee shift scheduling and management"
        breadcrumbs={[{ label: "HR", href: "/hr" }, { label: "Shift Management" }]}
      >
        <Button onClick={() => router.visit("/hr/shifts/create")}>
          <Plus size={16} /> New Shift
        </Button>
      </PageHeader>

      <DataTable
        title="Shifts"
        columns={[
          { key: "shift_name", label: "Shift Name" },
          { key: "shift_date", label: "Shift Date" },
          { key: "start_time", label: "Start Time" },
          { key: "end_time", label: "End Time" },
          { key: "employee_name", label: "Employee" },
          { key: "location", label: "Location" },
          { key: "status", label: "Status", format: (value: string) => (
            <Badge className={STATUS_BADGE[value] || "bg-slate-100 text-slate-600"}>
              {value.replace(/_/g, " ")}
            </Badge>
          )},
        ]}
        data={shifts}
        meta={meta}
        rowActions={[
          { label: "View", icon: <Eye size={14} />, variant: "ghost" },
          { label: "Edit", icon: <Edit size={14} />, variant: "outline" },
        ]}
        emptyMessage="No shifts found."
      />
    </AppLayout>
  );
}
