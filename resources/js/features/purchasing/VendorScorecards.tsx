/**
 * Brixel FactoryOS — Vendor Scorecards
 * Supplier performance rating and scorecard tracking.
 */

import { Head, Link, router, usePage } from "@inertiajs/react";
import { AppLayout } from "@/components/layout";
import { PageHeader } from "@/components/layout";
import { DataTable } from "@/components/DataTable";
import { Award } from "lucide-react";

interface Scorecard {
  id: number;
  code: string;
  name: string;
  contact_person: string;
  email: string;
  po_count: number;
  on_time_rate: number;
  total_spent: string;
  quality_issues: number;
  score: string;
}

const SCORE_COLOR: Record<string, string> = {
  A: "text-emerald-600",
  B: "text-sky-600",
  C: "text-amber-600",
  D: "text-rose-600",
};

export default function VendorScorecards() {
  const { props: data } = usePage() as any;
  const scorecards: Scorecard[] = data?.scorecards || [];

  return (
    <AppLayout title="Vendor Scorecards">
      <PageHeader
        title="Vendor Scorecards"
        subtitle="Supplier performance rating and scorecard tracking"
        breadcrumbs={[{ label: "Purchasing", href: "/purchasing" }, { label: "Vendor Scorecards" }]}
        actions={
          <span className="text-xs text-slate-500">Supplier performance metrics</span>
        }
      />

      <div className="mb-6 grid grid-cols-1 md:grid-cols-4 gap-4">
        <div className="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
          <p className="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Total Vendors</p>
          <p className="mt-2 text-2xl font-semibold text-slate-900 tabular-nums">{scorecards.length}</p>
        </div>
        <div className="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
          <p className="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Avg On-Time Rate</p>
          <p className="mt-2 text-2xl font-semibold text-slate-900 tabular-nums">
            {scorecards.length > 0 ? Math.round(scorecards.reduce((sum, s) => sum + s.on_time_rate, 0) / scorecards.length) : 0}%
          </p>
        </div>
        <div className="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
          <p className="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Total POs</p>
          <p className="mt-2 text-2xl font-semibold text-slate-900 tabular-nums">{scorecards.reduce((sum, s) => sum + s.po_count, 0)}</p>
        </div>
        <div className="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
          <p className="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Quality Issues</p>
          <p className="mt-2 text-2xl font-semibold text-slate-900 tabular-nums">{scorecards.reduce((sum, s) => sum + s.quality_issues, 0)}</p>
        </div>
      </div>

      <DataTable
        title="Vendor Performance"
        columns={[
          { key: "code", label: "Vendor Code" },
          { key: "name", label: "Vendor Name" },
          { key: "contact_person", label: "Contact" },
          { key: "email", label: "Email" },
          { key: "po_count", label: "PO Count" },
          { key: "on_time_rate", label: "On-Time %", align: "right", format: (v: number) => `${v}%` },
          { key: "total_spent", label: "Total Spent", align: "right" },
          { key: "quality_issues", label: "Quality Issues" },
          { key: "score", label: "Score", format: (value: string) => (
            <span className={cn("font-semibold", SCORE_COLOR[value] || "text-slate-600")}>
              {value}
            </span>
          )},
        ]}
        data={scorecards}
        rowActions={[
          { label: "View", icon: <Award size={14} />, variant: "ghost" },
        ]}
      />
    </AppLayout>
  );
}
