/**
 * Brixel FactoryOS — Analytics
 * Advanced analytics dashboard with KPIs, trends, and production insights.
 */

import { Head, Link, usePage } from "@inertiajs/react";
import { AppLayout } from "@/components/layout";
import { PageHeader } from "@/components/layout";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { KPICard } from "@/components/KPICard";
import { BarChart, Bar, Line, LineChart, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer, PieChart as PieChartComp, Pie, Cell } from "recharts";
import { TrendingUp, TrendingDown, Package, Banknote, Clock, ShieldAlert } from "lucide-react";
import { cn } from "@/lib/utils";

interface MonthlyProduction {
  month: string;
  qty: number;
}

interface ProductionByStatus {
  status: string;
  count: number;
}

const STATUS_COLORS: Record<string, string> = {
  draft: "#94a3b8",
  pending_approval: "#eab308",
  approved: "#3b82f6",
  material_reserved: "#3b82f6",
  in_production: "#0ea5e9",
  quality_inspection: "#6366f1",
  completed: "#22c55e",
  closed: "#22c55e",
  cancelled: "#ef4444",
};

export default function Analytics() {
  const { props: data } = usePage() as any;

  const productionToday: number = data?.production_today || 0;
  const revenueMonth: number = data?.revenue_month || 0;
  const ordersDelayed: number = data?.orders_delayed || 0;
  const stockValue: number = data?.stock_value || 0;
  const qualityRejectionRate: number = data?.quality_rejection_rate || 0;

  const monthlyProduction: MonthlyProduction[] = data?.monthly_production || [];
  const productionByStatus: ProductionByStatus[] = data?.production_by_status || [];

  return (
    <AppLayout title="Analytics">
      <PageHeader
        title="Analytics Dashboard"
        subtitle="Advanced analytics, KPIs, trends, and production insights"
        breadcrumbs={[{ label: "Reporting", href: "/reports" }, { label: "Analytics" }]}
      />

      {/* KPI Row */}
      <div className="mb-8 grid grid-cols-2 gap-4 md:grid-cols-3 lg:grid-cols-5">
        <KPICard
          title="Production Today"
          value={`${productionToday.toLocaleString()} units`}
          icon={<Package size={18} />}
          change="+8.2% vs yesterday"
          trend="up"
        />
        <KPICard
          title="Revenue This Month"
          value={`Rs. ${revenueMonth.toLocaleString(undefined, { maximumFractionDigits: 0 })}`}
          icon={<Banknote size={18} />}
          change="+12.5% vs last month"
          trend="up"
        />
        <KPICard
          title="Orders Delayed"
          value={ordersDelayed.toLocaleString()}
          icon={<Clock size={18} />}
          change={ordersDelayed > 0 ? "Needs attention" : "No delays"}
          trend={ordersDelayed > 0 ? "down" : "up"}
        />
        <KPICard
          title="Stock Value"
          value={`Rs. ${stockValue.toLocaleString(undefined, { maximumFractionDigits: 0 })}`}
          icon={<TrendingUp size={18} />}
          change="Inventory valuation"
          trend="neutral"
        />
        <KPICard
          title="Quality Rejection Rate"
          value={`${qualityRejectionRate}%`}
          icon={<ShieldAlert size={18} />}
          change={qualityRejectionRate <= 3 ? "Within tolerance" : "Above 3% target"}
          trend={qualityRejectionRate <= 3 ? "up" : "down"}
        />
      </div>

      {/* Charts Row */}
      <div className="grid gap-6 lg:grid-cols-2">
        <Card>
          <CardHeader>
            <CardTitle className="text-base font-semibold text-slate-900">Monthly Production Trend</CardTitle>
          </CardHeader>
          <CardContent className="h-[350px]">
            <ResponsiveContainer width="100%" height="100%">
              <LineChart data={monthlyProduction}>
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
          <CardHeader>
            <CardTitle className="text-base font-semibold text-slate-900">Production by Status</CardTitle>
          </CardHeader>
          <CardContent className="h-[350px]">
            <ResponsiveContainer width="100%" height="100%">
              <PieChart>
                <Pie
                  data={productionByStatus}
                  dataKey="count"
                  nameKey="status"
                  cx="50%"
                  cy="50%"
                  outerRadius={80}
                  fill="#8884d8"
                  label
                >
                  {productionByStatus.map((entry, index) => (
                    <Cell key={`cell-${index}`} fill={STATUS_COLORS[entry.status] || "#94a3b8"} />
                  ))}
                </Pie>
                <Tooltip />
              </PieChart>
            </ResponsiveContainer>
          </CardContent>
        </Card>
      </div>

      {/* Insights */}
      <div className="mt-8 grid gap-6 md:grid-cols-3">
        <Card>
          <CardHeader>
            <CardTitle className="text-base font-semibold text-slate-900">Production Efficiency</CardTitle>
          </CardHeader>
          <CardContent className="p-6">
            <p className="text-2xl font-semibold text-slate-900">
              {monthlyProduction.length > 0
                ? Math.round(
                    (monthlyProduction[monthlyProduction.length - 1]?.qty || 0) /
                      Math.max(1, monthlyProduction[monthlyProduction.length - 2]?.qty || 1) *
                      100
                  ) + "%"
                : "0%"}
            </p>
            <p className="text-xs text-slate-500 mt-2">
              Month-over-month production change based on completed orders.
            </p>
          </CardContent>
        </Card>
        <Card>
          <CardHeader>
            <CardTitle className="text-base font-semibold text-slate-900">Delivery Performance</CardTitle>
          </CardHeader>
          <CardContent className="p-6">
            <p className="text-2xl font-semibold text-slate-900">
              {(100 - qualityRejectionRate).toFixed(1)}%
            </p>
            <p className="text-xs text-slate-500 mt-2">
              Orders delivered on time vs delayed.
            </p>
          </CardContent>
        </Card>
        <Card>
          <CardHeader>
            <CardTitle className="text-base font-semibold text-slate-900">Inventory Health</CardTitle>
          </CardHeader>
          <CardContent className="p-6">
            <p className="text-2xl font-semibold text-slate-900">
              Rs. {stockValue.toLocaleString(undefined, { maximumFractionDigits: 0 })}
            </p>
            <p className="text-xs text-slate-500 mt-2">
              Total inventory value across all warehouses.
            </p>
          </CardContent>
        </Card>
      </div>
    </AppLayout>
  );
}
