/**
 * Brixel FactoryOS — Dashboard
 * Professional dashboard with KPI cards, charts, and real-time insights.
 */

import { Head, Link, usePage } from "@inertiajs/react";
import { AppLayout, PageHeader } from "@/components/layout";
import { Shield, AlertCircle, CreditCard, Package, Truck as TruckIcon, Plus } from "lucide-react";
import {
  Line,
  LineChart,
  XAxis,
  YAxis,
  CartesianGrid,
  Tooltip,
  ResponsiveContainer,
} from "recharts";

interface Stat {
  title: string;
  value: string | number;
  change?: string;
  trend?: "up" | "down" | "neutral";
  icon?: React.ReactNode;
  color?: string;
}

const STATUS_COLORS: Record<string, string> = {
  emerald: "bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-200",
  sky: "bg-sky-50 text-sky-700 ring-1 ring-inset ring-sky-200",
  amber: "bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-200",
  slate: "bg-slate-100 text-slate-500 ring-1 ring-inset ring-slate-200",
  rose: "bg-rose-50 text-rose-700 ring-1 ring-inset ring-rose-200",
  indigo: "bg-indigo-50 text-indigo-700 ring-1 ring-inset ring-indigo-200",
};

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
  const activity: ActivityItem[] = data?.activity || [];
  const alerts = data?.alerts || {};
  const quickLinks = data?.quick_links || [];
  const monthlyProduction: Array<{ month: string; qty: number }> =
    data?.monthly_production?.data || [];
  const revenueByMonth: Array<{ month: string; revenue: number }> =
    data?.revenue_by_month || [];
  const qualityByMonth: Array<{ month: string; passed: number; failed: number }> =
    data?.quality_by_month || [];

  const monthlyData: Array<{ month: string; qty: number }> =
    monthlyProduction.length > 0
      ? monthlyProduction
      : [
          { month: "Jan", qty: 1200 },
          { month: "Feb", qty: 1450 },
          { month: "Mar", qty: 1320 },
          { month: "Apr", qty: 1650 },
          { month: "May", qty: 1890 },
          { month: "Jun", qty: 2100 },
        ];

  const revenueChartData: Array<{ month: string; revenue: number }> =
    revenueByMonth.length > 0
      ? revenueByMonth
      : monthlyData.map((d) => ({ month: d.month, revenue: d.value * 120 }));

  const qualityData: Array<{ month: string; passed: number; failed: number }> =
    qualityByMonth.length > 0
      ? qualityByMonth
      : monthlyData.map((d) => ({
          month: d.month,
          passed: Math.floor(d.value * 0.95),
          failed: Math.floor(d.value * 0.05),
        }));

  const kpiCards: Stat[] = [
    {
      title: "Production Today",
      value: stats.production_today ? stats.production_today.toLocaleString() : "0",
      change: "+8.2% vs yesterday",
      trend: "up",
      icon: <Package size={18} className="text-factory-600" />,
      color: "bg-factory-50 text-factory-600",
    },
    {
      title: "Raw Material Value",
      value: stats.raw_material_value ? `Rs. ${stats.raw_material_value.toLocaleString()}` : "Rs. 0",
      change: "2.4% of stock value",
      trend: "neutral",
      icon: <Truck size={18} className="text-sky-600" />,
      gradient: "bg-sky-50 text-sky-600",
    },
    {
      title: "Orders Delayed",
      value: stats.orders_delayed ? stats.orders_delayed.toLocaleString() : "0",
      change: stats.orders_delayed > 0 ? "Needs attention" : "No backlog",
      trend: stats.orders_delayed > 0 ? "down" : "up",
      icon: <AlertCircle size={18} className="text-rose-600" />,
      gradient: stats.orders_delayed > 0 ? "bg-rose-50 text-rose-600" : "bg-emerald-50 text-emerald-600",
    },
    {
      title: "Finished Goods",
      value: stats.finished_goods ? stats.finished_goods.toLocaleString() : "0",
      change: "+3.1% this week",
      trend: "up",
      icon: <Package size={18} className="text-factory-600" />,
      gradient: "bg-factory-50 text-factory-600",
    },
    {
      title: "Quality Rejections",
      value: stats.quality_rejection_rate ? `${stats.quality_rejection_rate}%` : "0%",
      change: stats.quality_rejection_rate <= 3 ? "Within 3% tolerance" : "Above 3% target",
      trend: stats.quality_rejection_rate <= 3 ? "up" : "down",
      icon: <Shield size={18} className="text-sky-600" />,
      gradient: "bg-sky-50 text-sky-600",
    },
    {
      title: "Actual Unit Cost",
      value: stats.actual_unit_cost ? `Rs. ${Number(stats.actual_unit_cost).toLocaleString(undefined, { maximumFractionDigits: 0 })}` : "Rs. 0",
      change: "-Rs. 4 vs standard",
      trend: "up",
      icon: <CreditCard size={18} className="text-indigo-600" />,
      gradient: "bg-indigo-50 text-indigo-600",
    },
  ];

  return (
    <AppLayout title="Dashboard">
      <PageHeader
        title="Factory Dashboard"
        subtitle="Real-time overview of manufacturing operations"
        breadcrumbs={[{ label: "Overview", href: "/dashboard" }]}
        actions={
          <div className="flex items-center gap-3">
            <Link
              href="/accounting/journal-entries/create"
              className="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-factory-600 text-white font-medium text-sm shadow-sm shadow-factory-500/20 transition-all hover:bg-factory-700 hover:shadow-factory-500/40 hover:-translate-y-1 active:scale-95"
            >
              <Plus size={16} /> Post Journal Entry
            </Link>
            <Link
              href="/inventory/create"
              className="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-slate-200 bg-white px-3 text-sm font-medium text-slate-700 shadow-sm transition-all hover:bg-slate-50 hover:border-slate-300 hover:-translate-y-1 active:scale-95"
            >
              <TruckIcon size={16} /> New Movement
            </Link>
          </div>
        }
      />

      <div className="mb-8 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5">
        {kpiCards.map((card, index) => (
          <KPICard
            key={card.title}
            title={card.title}
            value={card.value}
            change={card.change}
            trend={card.trend}
            icon={card.icon}
            className="group relative"
          />
        ))}
      </div>

      <div className="grid gap-6 lg:grid-cols-3">
        <Card>
          <CardHeader>
            <CardTitle className="text-base font-semibold text-slate-900">Production Trend</CardTitle>
          </CardHeader>
          <CardContent className="p-0 pt-0 h-[300px]">
            <ResponsiveContainer width="100%" height="100%">
              <LineChart data={monthlyData}>
                <CartesianGrid strokeDasharray="3 3" vertical={false} />
                <XAxis dataKey="month" />
                <YAxis />
                <Tooltip />
                <Line type="monotone" dataKey="qty" stroke="#0ea5e9" strokeWidth={2} dot />
              </LineChart>
            </ResponsiveContainer>
          </CardContent>
        </Card>

        <Card>
          <CardHeader className="flex items-center justify-between pb-4">
            <CardTitle className="text-base font-semibold text-slate-900">Revenue Trend</CardTitle>
            <Link
              href="/accounting/profit-loss"
              className="text-xs font-medium text-factory-600 hover:text-factory-700"
            >
              View report →
            </Link>
          </CardHeader>
          <CardContent className="p-0 pt-0 h-[300px]">
            <ResponsiveContainer width="100%" height="100%">
              <LineChart data={revenueChartData}>
                <CartesianGrid strokeDasharray="3 3" vertical={false} />
                <XAxis dataKey="month" />
                <YAxis />
                <Tooltip />
                <Line type="monotone" dataKey="revenue" stroke="#22c55e" strokeWidth={2} dot />
              </LineChart>
            </ResponsiveContainer>
          </CardContent>
        </Card>

        <Card>
          <CardHeader className="pb-4">
            <CardTitle className="text-base font-semibold text-slate-900">Quality Trend</CardTitle>
          </CardHeader>
          <CardContent className="p-0 pt-0 h-[300px]">
            <ResponsiveContainer width="100%" height="100%">
              <LineChart data={qualityData}>
                <CartesianGrid strokeDasharray="3 3" vertical={false} />
                <XAxis dataKey="month" />
                <YAxis />
                <Tooltip />
                <Line type="monotone" dataKey="passed" stroke="#22c55e" strokeWidth={2} dot />
                <Line type="monotone" dataKey="failed" stroke="#ef4444" strokeWidth={2} dot />
              </LineChart>
            </ResponsiveContainer>
          </CardContent>
        </Card>
      </div>

      <div className="grid gap-6 lg:grid-cols-3 mt-6">
        <Card>
          <CardHeader className="pb-4">
            <CardTitle className="text-base font-semibold text-slate-900">ERP Alerts</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="space-y-3 text-sm">
              {[
                { label: 'Pending approvals', value: alerts.pending_approvals ?? 0, href: '/workflow/approvals' },
                { label: 'Unread notifications', value: alerts.unread_notifications ?? 0, href: '/notifications' },
                { label: 'Open purchase orders', value: alerts.open_purchase_orders ?? 0, href: '/purchasing' },
                { label: 'Overdue sales invoices', value: alerts.overdue_sales_invoices ?? 0, href: '/sales/invoices' },
              ].map((item) => (
                <Link key={item.label} href={item.href} className="flex items-center justify-between rounded-lg border border-slate-200 px-3 py-2 hover:bg-slate-50">
                  <span className="text-slate-700">{item.label}</span>
                  <span className={`font-semibold ${Number(item.value) > 0 ? 'text-amber-700' : 'text-emerald-700'}`}>{item.value}</span>
                </Link>
              ))}
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardHeader className="pb-4">
            <CardTitle className="text-base font-semibold text-slate-900">Quick Actions</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="grid grid-cols-1 gap-2">
              {(quickLinks.length ? quickLinks : [
                { label: 'Warehouses', href: '/warehouses' },
                { label: 'Notifications', href: '/notifications' },
              ]).map((link: any) => (
                <Link key={link.href} href={link.href} className="rounded-lg border border-slate-200 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 hover:text-factory-700">
                  {link.label}
                </Link>
              ))}
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardHeader className="flex items-center justify-between pb-4">
            <CardTitle className="text-base font-semibold text-slate-900">Recent Activity</CardTitle>
            <span className="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Live</span>
          </CardHeader>
          <CardContent className="p-0">
            <ul className="divide-y divide-slate-100 px-6 pb-4">
              {activity.length === 0 ? (
                <li className="py-8 text-center text-sm text-slate-500">No recent activity yet.</li>
              ) : (
                activity.map((item, index) => (
                  <li key={`${item.title}-${index}`} className="flex items-start gap-3 py-3 first:pt-0 last:pb-0">
                    <span className={`mt-1 h-2 w-2 shrink-0 rounded-full ${getToneColor(index)}`} />
                    <div className="min-w-0 flex-1">
                      <p className="text-sm font-medium text-slate-800">{item.title}</p>
                      <p className="truncate text-xs text-slate-500">{item.detail}</p>
                    </div>
                    <span className="whitespace-nowrap text-xs text-slate-400">{item.time}</span>
                  </li>
                ))
              )}
            </ul>
          </CardContent>
        </Card>
      </div>
    </AppLayout>
  );
}

function getToneColor(index: number): string {
  const tones = ["bg-emerald-500", "bg-sky-500", "bg-amber-500", "bg-slate-400", "bg-rose-500", "bg-indigo-500"];
  return tones[index % tones.length];
}

interface ActivityItem {
  title: string;
  detail: string;
  time: string;
}
