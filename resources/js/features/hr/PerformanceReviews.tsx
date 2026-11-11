/**
 * Brixel FactoryOS — Performance Reviews
 * Employee performance review and rating management.
 */

import { Head, Link, router, usePage } from "@inertiajs/react";
import { AppLayout } from "@/components/layout";
import { PageHeader } from "@/components/layout";
import { DataTable } from "@/components/DataTable";
import { StatStrip } from "@/components/StatStrip";
import { Plus, Shield, Users } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";

interface PerformanceReview {
  id: number;
  review_title: string;
  review_date: string;
  review_period: string;
  overall_rating: number;
  status: string;
  employee_name: string;
  employee_code: string;
}

const STATUS_BADGE: Record<string, string> = {
  draft: "bg-slate-100 text-slate-500 ring-1 ring-inset ring-slate-200",
  pending: "bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-200",
  in_progress: "bg-sky-50 text-sky-700 ring-1 ring-inset ring-sky-200",
  completed: "bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-200",
  approved: "bg-indigo-50 text-indigo-700 ring-1 ring-inset ring-indigo-200",
};

const RATING_BADGE: Record<string, string> = {
  "1": "bg-rose-100 text-rose-700",
  "2": "bg-amber-100 text-amber-700",
  "3": "bg-slate-100 text-slate-700",
  "4": "bg-blue-100 text-blue-700",
  "5": "bg-emerald-100 text-emerald-700",
};

export default function PerformanceReviews() {
  const { props: data } = usePage() as any;
  const reviews: PerformanceReview[] = data?.reviews?.data || [];
  const meta = data?.reviews?.meta || {};
  const summary = data?.summary || {
    total_reviews: 0,
    completed_reviews: 0,
    pending_reviews: 0,
  };

  return (
    <AppLayout title="Performance Reviews">
      <PageHeader
        title="Performance Reviews"
        subtitle="Employee performance review and rating management"
        breadcrumbs={[{ label: "HR", href: "/hr" }, { label: "Performance" }]}
      >
        <Button onClick={() => router.visit("/hr/performance/create")}>
          <Plus size={16} /> New Review
        </Button>
      </PageHeader>

      <StatStrip
        items={[
          { label: "Total Reviews", value: summary.total_reviews, hint: "All reviews" },
          { label: "Completed", value: summary.completed_reviews, hint: "Finished reviews", tone: "success" },
          { label: "Pending", value: summary.pending_reviews, hint: "Awaiting review", tone: summary.pending_reviews > 0 ? "warning" : "success" },
        ]}
      />

      <DataTable
        title="Performance Reviews"
        columns={[
          { key: "review_title", label: "Review Title" },
          { key: "employee_code", label: "Employee Code" },
          { key: "employee_name", label: "Employee Name" },
          { key: "review_period", label: "Review Period" },
          { key: "review_date", label: "Review Date" },
          { key: "overall_rating", label: "Rating", align: "center", format: (value: number) => (
            <Badge className={RATING_BADGE[String(value)] || "bg-slate-100 text-slate-600"}>
              {value} / 5
            </Badge>
          )},
          { key: "status", label: "Status", format: (value: string) => (
            <Badge className={STATUS_BADGE[value] || "bg-slate-100 text-slate-600"}>
              {value.replace(/_/g, " ")}
            </Badge>
          )},
        ]}
        data={reviews}
        meta={meta}
        rowActions={[
          { label: "View", icon: <Users size={14} />, variant: "ghost" },
          { label: "Edit", icon: <Shield size={14} />, variant: "outline" },
        ]}
      />
    </AppLayout>
  );
}
