/**
 * Brixel FactoryOS — Dashboard Page
 * Real-time overview of manufacturing operations.
 */

import { Link, usePage } from "@inertiajs/react";
import { AppLayout, PageHeader } from "@/components/layout";
import { KPICard } from "@/components/KPICard";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import {
  Factory,
  Truck,
  Shield,
  CreditCard,
  Clock,
  AlertCircle,
  ArrowUpRight,
  ClipboardList,
  ShoppingCart,
  BookOpen,
  Activity,
} from "lucide-react";

export default function Dashboard() {
  const { props: data } = usePage() as any;
  const stats = data?.stats || {
    production_today: 0,
    raw_material_value: 0,
    orders_delayed: 0,
    finished_goods: 0,
    quality_rejection_rate: 0,
    actual_unit_cost: 0,
  };
  const activity: { title: string; detail: string; time: string }[] =
    data?.activity || [];

  const formats = {
    production: stats.production_today ? `${stats.production_today.toLocaleString()} units` : "0 units",
    material: stats.raw_material_value ? `Rs. ${stats.raw_material_value.toLocaleString()}` : "Rs. 0",
    delayed: stats.orders_delayed ? String(stats.orders_delayed) : "0",
    finished: stats.finished_goods ? `${stats.finished_goods.toLocaleString()} units` : "0 units",
    rejection: stats.quality_rejection_rate ? `${stats.quality_rejection_rate}%` : "0%",
    unitCost: stats.actual_unit_cost ? `Rs. ${Number(stats.actual_unit_cost).toLocaleString(undefined, { maximumFractionDigits: 0 })}` : "Rs. 0",
  };

  return (
    <AppLayout title="Dashboard">
      <PageHeader
        title="Factory Dashboard"
        subtitle="Real-time overview of manufacturing operations"
      >
        <Link
          href="/production"
          className="inline-flex h-9 items-center gap-1.5 rounded-lg bg-sky-600 px-3.5 text-sm font-medium text-white shadow-sm transition-colors hover:bg-sky-700"
        >
          <Factory size={16} /> New production order
        </Link>
      </PageHeader>

      {/* KPI grid */}
      <div className="mb-6 grid grid-cols-2 gap-4 lg:grid-cols-3 xl:grid-cols-6">
        <KPICard title="Production Today" value={formats.production} icon={<Factory size={18} />} change="+8.2% vs yesterday" trend="up" />
        <KPICard title="Raw Material Value" value={formats.material} icon={<Truck size={18} />} change="2.4% of stock value" trend="neutral" />
        <KPICard title="Orders Delayed" value={formats.delayed} icon={<AlertCircle size={18} />} change={stats.orders_delayed > 0 ? "Needs attention" : "No backlog"} trend={stats.orders_delayed > 0 ? "down" : "up"} />
        <KPICard title="Finished Goods" value={formats.finished} icon={<Clock size={18} />} change="+3.1% this week" trend="up" />
        <KPICard title="Quality Rejections" value={formats.rejection} icon={<Shield size={18} />} change={stats.quality_rejection_rate <= 3 ? "Within 3% tolerance" : "Above 3% target"} trend={stats.quality_rejection_rate <= 3 ? "up" : "down"} />
        <KPICard title="Actual Unit Cost" value={formats.unitCost} icon={<CreditCard size={18} />} change="-Rs. 4 vs standard" trend="up" />
      </div>

      <div className="grid grid-cols-1 gap-6 lg:grid-cols-3">
        {/* Quick actions */}
        <Card>
          <CardHeader className="pb-4">
            <CardTitle className="text-base font-semibold">Quick Actions</CardTitle>
          </CardHeader>
          <CardContent className="space-y-2">
            <QuickAction href="/production" icon={<ClipboardList size={16} />} label="New production order" primary />
            <QuickAction href="/purchasing" icon={<ShoppingCart size={16} />} label="Create purchase order" />
            <QuickAction href="/quality" icon={<Shield size={16} />} label="Record quality inspection" />
            <QuickAction href="/accounting" icon={<BookOpen size={16} />} label="Post journal entry" />
          </CardContent>
        </Card>

        {/* Recent activity */}
        <Card className="lg:col-span-2">
          <CardHeader className="flex-row items-center justify-between pb-4">
            <CardTitle className="text-base font-semibold">Recent Activity</CardTitle>
            <span className="inline-flex items-center gap-1.5 text-xs font-medium text-slate-500">
              <span className="h-1.5 w-1.5 rounded-full bg-emerald-500" /> Live
            </span>
          </CardHeader>
          <CardContent className="p-5 pt-0">
            <ul className="divide-y divide-slate-100">
              {activity.length === 0 ? (
                <li className="py-8 text-center text-sm text-slate-500">
                  No recent activity yet.
                </li>
              ) : (
                activity.map((item, index) => (
                  <ActivityItem
                    key={`${item.title}-${index}`}
                    tone={
                      index === 0 ? "emerald" : index === 1 ? "sky" : index === 2 ? "amber" : "slate"
                    }
                    title={item.title}
                    detail={item.detail}
                    time={item.time}
                  />
                ))
              )}
            </ul>
          </CardContent>
        </Card>
      </div>
    </AppLayout>
  );
}

function QuickAction({
  href,
  icon,
  label,
  primary = false,
}: {
  href: string;
  icon: React.ReactNode;
  label: string;
  primary?: boolean;
}) {
  return (
    <Link
      href={href}
      className={
        primary
          ? "flex items-center justify-between rounded-lg bg-sky-50 px-3.5 py-2.5 text-sm font-medium text-sky-700 ring-1 ring-inset ring-sky-100 transition-colors hover:bg-sky-100"
          : "flex items-center justify-between rounded-lg px-3.5 py-2.5 text-sm font-medium text-slate-700 ring-1 ring-inset ring-slate-200 transition-colors hover:bg-slate-50"
      }
    >
      <span className="flex items-center gap-2.5">
        <span className={primary ? "text-sky-600" : "text-slate-400"}>{icon}</span>
        {label}
      </span>
      <ArrowUpRight size={15} className={primary ? "text-sky-500" : "text-slate-400"} />
    </Link>
  );
}

function ActivityItem({
  tone,
  title,
  detail,
  time,
}: {
  tone: "emerald" | "sky" | "amber" | "slate";
  title: string;
  detail: string;
  time: string;
}) {
  const dot = {
    emerald: "bg-emerald-500",
    sky: "bg-sky-500",
    amber: "bg-amber-500",
    slate: "bg-slate-400",
  }[tone];

  return (
    <li className="flex items-start gap-3 py-3 first:pt-0 last:pb-0">
      <span className={`mt-1.5 h-2 w-2 shrink-0 rounded-full ${dot}`} />
      <div className="min-w-0 flex-1">
        <p className="text-sm font-medium text-slate-800">{title}</p>
        <p className="truncate text-xs text-slate-500">{detail}</p>
      </div>
      <span className="whitespace-nowrap text-xs text-slate-400">{time}</span>
    </li>
  );
}
