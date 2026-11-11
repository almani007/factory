/**
 * Brixel FactoryOS — Training Management
 * Training programs, employee enrollment, and certification tracking.
 */

import { Head, Link, router, usePage } from "@inertiajs/react";
import { AppLayout } from "@/components/layout";
import { PageHeader } from "@/components/layout";
import { DataTable } from "@/components/DataTable";
import { Plus, Shield, BookOpen } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";

interface TrainingProgram {
  id: number;
  program_name: string;
  training_type: string;
  start_date: string;
  end_date: string;
  status: string;
  cost: number;
  enrolled_count: number;
}

const STATUS_BADGE: Record<string, string> = {
  scheduled: "bg-sky-50 text-sky-700 ring-1 ring-inset ring-sky-200",
  in_progress: "bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-200",
  completed: "bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-200",
  cancelled: "bg-rose-50 text-rose-700 ring-1 ring-inset ring-rose-200",
};

export default function Training() {
  const { props: data } = usePage() as any;
  const programs: TrainingProgram[] = data?.training_programs?.data || [];

  return (
    <AppLayout title="Training Management">
      <PageHeader
        title="Training Management"
        subtitle="Training programs, employee enrollment, and certification tracking"
        breadcrumbs={[{ label: "HR", href: "/hr" }, { label: "Training" }]}
      >
        <Button onClick={() => router.visit("/hr/training/create")}>
          <Plus size={16} /> New Program
        </Button>
      </PageHeader>

      <DataTable
        title="Training Programs"
        columns={[
          { key: "program_name", label: "Program Name" },
          { key: "training_type", label: "Type", format: (value: string) => value.replace(/_/g, " ") },
          { key: "start_date", label: "Start Date" },
          { key: "end_date", label: "End Date" },
          { key: "cost", label: "Cost", align: "right", format: (v: number) => `Rs. ${Number(v).toLocaleString(undefined, { maximumFractionDigits: 2 })}` },
          { key: "enrolled_count", label: "Enrolled", align: "right" },
          { key: "status", label: "Status", format: (value: string) => (
            <Badge className={STATUS_BADGE[value] || "bg-slate-100 text-slate-600"}>
              {value.replace(/_/g, " ")}
            </Badge>
          )},
        ]}
        data={programs}
        rowActions={[
          { label: "View", icon: <BookOpen size={14} />, variant: "ghost" },
          { label: "Edit", icon: <Shield size={14} />, variant: "outline" },
        ]}
      />
    </AppLayout>
  );
}
