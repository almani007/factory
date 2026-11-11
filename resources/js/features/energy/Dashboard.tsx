/**
 * Brixel FactoryOS — Energy Dashboard
 * Energy consumption tracking and cost analysis.
 */

import { Head, Link, usePage } from "@inertiajs/react";
import { AppLayout } from "@/components/layout";
import { PageHeader } from "@/components/layout";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { KPICard } from "@/components/KPICard";
import { Activity, PlugZap, CreditCard } from "lucide-react";
import { DataTable } from "@/components/DataTable";

interface Consumption {
  id: number;
  meter_name: string;
  consumption_date: string;
  consumption_value: number;
  unit: string;
  cost: number;
}

export default function EnergyDashboard() {
  const { props: data } = usePage() as any;
  const consumption = data?.consumption?.data || [];
  const summary = data?.summary || {
    total_consumption: 0,
    total_cost: 0,
    active_meters: 0,
  };

  return (
    <AppLayout title="Energy Management">
      <PageHeader
        title="Energy Management"
        subtitle="Track energy consumption and costs"
        breadcrumbs={[{ label: "Energy", href: "/energy" }, { label: "Dashboard" }]}
      />

      <div className="mb-8 grid grid-cols-2 gap-4 md:grid-cols-3 lg:grid-cols-4">
        <KPICard
          title="Total Consumption"
          value={summary.total_consumption?.toLocaleString(undefined, { maximumFractionDigits: 2 }) || "0"}
          icon={<Activity size={18} className="text-factory-600" />}
          change="Units consumed"
          trend="neutral"
        />
        <KPICard
          title="Total Cost"
          value={`Rs. ${summary.total_cost?.toLocaleString(undefined, { maximumFractionDigits: 2 }) || "0"}`}
          icon={<CreditCard size={18} className="text-factory-600" />}
          change="Energy costs"
          trend="neutral"
        />
        <KPICard
          title="Active Meters"
          value={summary.active_meters.toLocaleString?.() || "0"}
          icon={<PlugZap size={18} className="text-factory-600" />}
          change="Monitoring meters"
          trend="neutral"
        />
      </div>

      <Card>
        <CardHeader>
          <CardTitle className="text-base font-semibold text-slate-900">Recent Consumption</CardTitle>
        </CardHeader>
        <CardContent className="p-0">
          <DataTable
            title=""
            columns={[
              { key: "meter_name", label: "Meter Name" },
              { key: "consumption_date", label: "Date" },
              { key: "consumption_value", label: "Value", align: "right", format: (v: number) => Number(v).toFixed(2) },
              { key: "unit", label: "Unit" },
              { key: "cost", label: "Cost", align: "right", format: (v: number) => `Rs. ${Number(v).toLocaleString(undefined, { maximumFractionDigits: 2 })}` },
            ]}
            data={consumption}
            emptyMessage="No consumption data found."
          />
        </CardContent>
      </Card>
    </AppLayout>
  );
}
