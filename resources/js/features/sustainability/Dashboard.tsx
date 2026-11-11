/**
 * Brixel FactoryOS — Sustainability Dashboard
 * Sustainability and carbon tracking dashboard.
 */

import { Head, Link, usePage } from "@inertiajs/react";
import { AppLayout } from "@/components/layout";
import { PageHeader } from "@/components/layout";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { KPICard } from "@/components/KPICard";
import { TreePine, Trash2, Leaf } from "lucide-react";
import { DataTable } from "@/components/DataTable";
import { Badge } from "@/components/ui/badge";

interface Carbon {
  id: number;
  entry_date: string;
  scope: string;
  carbon_emissions_tons: number;
  source: string;
}

interface Waste {
  id: number;
  entry_date: string;
  waste_type: string;
  quantity_tons: number;
  disposal_method: string;
}

export default function SustainabilityDashboard() {
  const { props: data } = usePage() as any;
  const carbon = data?.carbon?.data || [];
  const waste = data?.waste?.data || [];
  const summary = data?.summary || {
    total_carbon_tons: 0,
    total_waste_tons: 0,
  };

  return (
    <AppLayout title="Sustainability">
      <PageHeader
        title="Sustainability"
        subtitle="Carbon tracking and waste management"
        breadcrumbs={[{ label: "Sustainability", href: "/sustainability" }, { label: "Dashboard" }]}
      />

      <div className="mb-8 grid grid-cols-2 gap-4 md:grid-cols-3 lg:grid-cols-4">
        <KPICard
          title="Total Carbon"
          value={`${summary.total_carbon_tons?.toLocaleString(undefined, { maximumFractionDigits: 2 }) || "0"} tons`}
          icon={<TreePine size={18} className="text-emerald-600" />}
          change="CO2 emissions"
          trend="neutral"
        />
        <KPICard
          title="Total Waste"
          value={`${summary.total_waste_tons?.toLocaleString(undefined, { maximumFractionDigits: 2 }) || "0"} tons`}
          icon={<Trash2 size={18} className="text-amber-600" />}
          change="Waste generated"
          trend="neutral"
        />
      </div>

      <div className="grid gap-6 lg:grid-cols-2">
        <Card className="lg:col-span-1">
          <CardHeader className="pb-4">
            <CardTitle className="text-base font-semibold text-slate-900">Carbon Tracking</CardTitle>
          </CardHeader>
          <CardContent className="p-0">
            <DataTable
              title=""
              columns={[
                { key: "entry_date", label: "Date" },
                { key: "scope", label: "Scope" },
                { key: "carbon_emissions_tons", label: "Tons CO2", align: "right", format: (v: number) => Number(v).toFixed(2) },
                { key: "source", label: "Source" },
              ]}
              data={carbon}
              emptyMessage="No carbon data found."
            />
          </CardContent>
        </Card>

        <Card className="lg:col-span-1">
          <CardHeader className="pb-4">
            <CardTitle className="text-base font-semibold text-slate-900">Waste Tracking</CardTitle>
          </CardHeader>
          <CardContent className="p-0">
            <DataTable
              title=""
              columns={[
                { key: "entry_date", label: "Date" },
                { key: "waste_type", label: "Type" },
                { key: "quantity_tons", label: "Tons", align: "right", format: (v: number) => Number(v).toFixed(2) },
                { key: "disposal_method", label: "Disposal Method" },
              ]}
              data={waste}
              emptyMessage="No waste data found."
            />
          </CardContent>
        </Card>
      </div>
    </AppLayout>
  );
}
