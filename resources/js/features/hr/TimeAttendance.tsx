/**
 * Brixel FactoryOS — Time & Attendance
 * Employee time tracking and attendance management.
 */

import { Head, Link, router, usePage } from "@inertiajs/react";
import { AppLayout } from "@/components/layout";
import { PageHeader } from "@/components/layout";
import { DataTable } from "@/components/DataTable";
import { Plus, Clock } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";

interface TimeEntry {
  id: number;
  entry_date: string;
  employee_code: string;
  name: string;
  clock_in: string;
  clock_out: string;
  hours_worked: number;
  status: string;
  status_label: string;
}

const STATUS_BADGE: Record<string, string> = {
  present: "bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-200",
  absent: "bg-rose-50 text-rose-700 ring-1 ring-inset ring-rose-200",
  late: "bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-200",
  half_day: "bg-slate-100 text-slate-500 ring-1 ring-inset ring-slate-200",
};

export default function TimeAttendance() {
  const { props: data } = usePage() as any;
  const entries: TimeEntry[] = data?.entries?.data || [];
  const meta = data?.entries?.meta || {};

  return (
    <AppLayout title="Time & Attendance">
      <PageHeader
        title="Time & Attendance"
        subtitle="Employee time tracking and attendance management"
        breadcrumbs={[{ label: "HR", href: "/hr" }, { label: "Time & Attendance" }]}
      >
        <Button onClick={() => router.visit("/hr/time-attendance/create")}>
          <Plus size={16} /> New Time Entry
        </Button>
      </PageHeader>

      <DataTable
        title="Time Entries"
        columns={[
          { key: "entry_date", label: "Date" },
          { key: "employee_code", label: "Employee Code" },
          { key: "name", label: "Employee Name" },
          { key: "clock_in", label: "Clock In" },
          { key: "clock_out", label: "Clock Out" },
          { key: "hours_worked", label: "Hours Worked" },
          { key: "status", label: "Status", format: (value: string) => (
            <Badge className={STATUS_BADGE[value] || "bg-slate-100 text-slate-600"}>
              {value.replace(/_/g, " ")}
            </Badge>
          )},
        ]}
        data={entries}
        meta={meta}
        rowActions={[
          { label: "View", icon: <Plus size={14} />, variant: "ghost" },
          { label: "Edit", icon: <Plus size={14} />, variant: "outline" },
        ]}
      />
    </AppLayout>
  );
}
