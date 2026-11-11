/**
 * Brixel FactoryOS — HR / Employees
 * Employee master data and workforce management.
 */

import { Head, Link, router, usePage } from "@inertiajs/react";
import { AppLayout } from "@/components/layout";
import { PageHeader } from "@/components/layout";
import { DataTable } from "@/components/DataTable";
import { StatStrip } from "@/components/StatStrip";
import { Plus, Eye, Edit, User } from "lucide-react";
import { Button } from "@/components/ui/button";

interface Employee {
  id: number;
  employee_code: string;
  name: string;
  email: string;
  phone: string;
  department: string;
  position: string;
  status: string;
}

export default function HR() {
  const { props: data } = usePage() as any;
  const employees: Employee[] = data?.employees?.data || [];
  const meta = data?.employees?.meta || {};
  const summary = data?.hr_summary || { total: 0, active: 0 };

  return (
    <AppLayout title="HR / Employees">
      <PageHeader
        title="HR / Employees"
        subtitle="Employee master data, departments, and workforce management"
        breadcrumbs={[{ label: "HR", href: "/hr" }]}
      >
        <Button onClick={() => router.visit("/hr/create")}>
          <Plus size={16} /> New Employee
        </Button>
      </PageHeader>

      <StatStrip
        items={[
          { label: "Total Employees", value: summary.total, hint: "All employees" },
          { label: "Active", value: summary.active, hint: "Currently employed", tone: "success" },
          { label: "Inactive", value: summary.total - summary.active, hint: "Not active", tone: "danger" },
        ]}
      />

      <DataTable
        title="Employees"
        columns={[
          { key: "employee_code", label: "Employee Code" },
          { key: "name", label: "Name" },
          { key: "position", label: "Position" },
          { key: "department", label: "Department" },
          { key: "email", label: "Email" },
          { key: "phone", label: "Phone" },
          { key: "status", label: "Status" },
        ]}
        data={employees}
        meta={meta}
        rowActions={[
          { label: "View", icon: <Eye size={14} />, variant: "ghost" },
          { label: "Edit", icon: <Edit size={14} />, variant: "outline" },
        ]}
      />
    </AppLayout>
  );
}
