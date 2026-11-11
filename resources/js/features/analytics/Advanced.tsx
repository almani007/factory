/**
 * Brixel FactoryOS — Analytics Advanced
 * Advanced analytics dashboard with charts and insights.
 */

import { Head, Link, usePage } from "@inertiajs/react";
import { AppLayout } from "@/components/layout";
import { PageHeader } from "@/components/layout";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { KPICard } from "@/components/KPICard";
import { BarChart, Bar, Line, LineChart, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer, PieChart as PieChartComp, Pie, Cell } from "recharts";
import { Activity, Banknote, Shield, Package, Truck } from "lucide-react";
import { cn } from "@/lib/utils";

interface MonthlyProduction {
  month: string;
  qty: number;
}

interface RevenueByMonth {
  month: string;
  revenue: number;
}

interface QualityByMonth {
  month: string;
  passed: number;
  failed: number;
}

export default function AdvancedAnalytics() {
  const { props: data } = usePage() as any;
  const productionByMonth: MonthlyProduction[] = data?.production_by_month?.data || [];
  const revenueByMonth: RevenueByMonth[] = data?.revenue_by_month?.data || [];
  const qualityByMonth: QualityByMonth[] = data?.quality_by_month?.data || [];

  return (
    <AppLayout title="Advanced Analytics">
      <PageHeader
        title="Advanced Analytics"
        subtitle="Advanced analytics with charts and trends"
        breadcrumbs={[{ label: "Analytics", href: "/analytics" }, { label: "Advanced" }]}
      />

      <div className="mb-8 grid grid-cols-2 gap-4 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5">
        <KPICard
          title="Production Trend"
          icon={<Activity size={18} className="text-factory-600" />}
          change="Monthly production"
          trend="neutral"
        />
        <KPICard
          title="Revenue Trend"
          icon={<Banknote size={18} className="text-factory-600" />}
          change="Monthly revenue"
          trend="neutral"
        />
        <KPICard
          title="Quality Trend"
          icon={<Shield size={18} className="text-factory-600" />}
          change="Quality pass rate"
          trend="neutral"
        />
      </div>

      <div className="grid gap-6 lg:grid-cols-2">
        <Card>
          <CardHeader>
            <CardTitle className="text-base font-semibold text-slate-900">Production Trend</CardTitle>
          </CardHeader>
          <CardContent className="h-[350px]">
            <ResponsiveContainer width="100%" height="100%">
              <LineChart data={productionByMonth}>
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
            <CardTitle className="text-base font-semibold text-slate-900">Revenue Trend</CardTitle>
          </CardHeader>
          <CardContent className="h-[350px]">
            <ResponsiveContainer width="100%" height="100%">
              <LineChart data={revenueByMonth}>
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
          <CardHeader>
            <CardTitle className="text-base font-semibold text-slate-900">Quality Trend</CardTitle>
          </CardHeader>
          <CardContent className="h-[350px]">
            <ResponsiveContainer width="100%" height="100%">
              <LineChart data={qualityByMonth}>
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
    </AppLayout>
  );
}
