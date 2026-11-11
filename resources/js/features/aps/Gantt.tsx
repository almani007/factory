/**
 * Brixel FactoryOS — APS Gantt
 * Gantt chart view for production scheduling visualization.
 */

import { Head, Link, router, usePage } from "@inertiajs/react";
import { AppLayout } from "@/components/layout";
import { PageHeader } from "@/components/layout";
import { GanttChart, ArrowLeft, Activity, Clock, Plus } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";
import { cn } from "@/lib/utils";

interface GanttOrder {
  id: number;
  work_order_number: string;
  production_order: string;
  item_name: string;
  status: string;
  planned_start_date: string;
  planned_end_date: string;
  actual_start_date: string;
  actual_end_date: string;
  quantity: number;
  completed_quantity: number;
}

const STATUS_BADGE: Record<string, string> = {
  draft: "bg-slate-100 text-slate-500",
  released: "bg-indigo-50 text-indigo-700",
  in_progress: "bg-sky-50 text-sky-700",
  completed: "bg-emerald-50 text-emerald-700",
  closed: "bg-emerald-50 text-emerald-700",
  delayed: "bg-rose-50 text-rose-700",
  cancelled: "bg-slate-100 text-slate-500",
};

export default function APSGantt() {
  const { props: data } = usePage() as any;
  const orders: GanttOrder[] = data?.gantt_data?.data || [];

  // Group orders by week for Gantt view
  const weeks = [
    { label: "Week 1", start: "2024-01-01", end: "2024-01-07" },
    { label: "Week 2", start: "2024-01-08", end: "2024-01-14" },
    { label: "Week 3", start: "2024-01-15", end: "2024-01-21" },
    { label: "Week 4", start: "2024-01-22", end: "2024-01-28" },
  ];

  return (
    <AppLayout title="APS Gantt Chart">
      <PageHeader
        title="Production Gantt Chart"
        subtitle="Visual production scheduling with timeline view"
        breadcrumbs={[{ label: "Operations", href: "/aps" }, { label: "APS Planning" }, { label: "Gantt Chart" }]}
        actions={
          <Link href="/aps" className="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-slate-200 bg-white px-3 text-sm font-medium text-slate-700 shadow-sm transition-colors hover:bg-slate-50">
            <ArrowLeft size={14} /> Back to Planning
          </Link>
        }
      />

      <div className="mb-6 grid grid-cols-2 gap-4">
        <Card>
          <CardHeader>
            <CardTitle className="text-base font-semibold text-slate-900">Schedule Overview</CardTitle>
          </CardHeader>
          <CardContent className="p-5 pt-0">
            <div className="space-y-3">
              <div className="flex items-center justify-between">
                <span className="text-sm text-slate-600">Total Orders</span>
                <span className="text-sm font-semibold text-slate-900">{orders.length}</span>
              </div>
              <div className="flex items-center justify-between">
                <span className="text-sm text-slate-600">In Progress</span>
                <span className="text-sm font-semibold text-sky-600">
                  {orders.filter((o) => o.status === "in_progress").length}
                </span>
              </div>
              <div className="flex items-center justify-between">
                <span className="text-sm text-slate-600">Delayed</span>
                <span className="text-sm font-semibold text-rose-600">
                  {orders.filter((o) => o.status === "delayed").length}
                </span>
              </div>
              <div className="flex items-center justify-between">
                <span className="text-sm text-slate-600">Completion Rate</span>
                <span className="text-sm font-semibold text-emerald-600">
                  {orders.length > 0 ? Math.round((orders.filter((o) => o.status === "completed").length / orders.length) * 100) : 0}%
                </span>
              </div>
            </div>
          </CardContent>
        </Card>
        <Card>
          <CardHeader>
            <CardTitle className="text-base font-semibold text-slate-900">Quick Actions</CardTitle>
          </CardHeader>
          <CardContent className="p-5 pt-0 space-y-3">
            <Button
              asChild
              href="/production/work-orders/create"
              className="w-full justify-start text-left"
            >
              <Plus size={14} /> New Work Order
            </Button>
            <Button
              asChild
              href="/production"
              variant="outline"
              className="w-full justify-start text-left"
            >
              <Clock size={14} /> View All Orders
            </Button>
          </CardContent>
        </Card>
      </div>

      <div className="rounded-xl border border-slate-200 bg-white shadow-sm overflow-hidden">
        <div className="bg-slate-50 px-6 py-4 border-b border-slate-200">
          <div className="flex items-center gap-2 mb-2">
            <Activity size={16} className="text-factory-600" />
            <span className="text-sm font-semibold text-slate-900">Production Timeline</span>
          </div>
          <div className="flex items-end gap-1 text-[10px] font-medium text-slate-500">
            {weeks.map((week) => (
              <div key={week.label} className="w-full text-center">
                {week.label}
              </div>
            ))}
          </div>
        </div>
        <div className="relative h-64 overflow-x-auto">
          {/* Timeline grid */}
          <div className="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAiIGhlaWdodD0iNDAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGNpcmNsZSBjeD0iMiIgY3k9IjIiIHI9IjEiIGZpbGw9IiNzbGF0ZS0yMDAiLz48L3N2Zz4=')] bg-[length:40px_40px] opacity-30" />
          {orders.map((order, index) => (
            <div
              key={order.id}
              className="absolute left-1/4 top-[calc(5%_+_5%_*_var(--i))] w-1/2 h-[30px] rounded-md bg-factory-50 border border-factory-200 flex items-center justify-center text-[10px] font-medium text-factory-700 shadow-sm cursor-pointer hover:shadow-md transition-shadow"
              style={{ "--i": index }}
              title={order.work_order_number}
            >
              {order.work_order_number}
              <Badge
                className={cn(
                  "ml-1 px-1.5 py-0.5 text-[9px]",
                  STATUS_BADGE[order.status] || "bg-slate-100 text-slate-600"
                )}
              >
                {order.status.replace(/_/g, " ")}
              </Badge>
            </div>
          ))}
        </div>
        <div className="px-6 py-4 border-t border-slate-200 bg-slate-50">
          <div className="flex items-center justify-between">
            <span className="text-xs text-slate-500">Planned</span>
            <span className="text-xs text-slate-500">Actual</span>
            <span className="text-xs text-slate-500">Delayed</span>
          </div>
        </div>
      </div>
    </AppLayout>
  );
}
