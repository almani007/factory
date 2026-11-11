/**
 * Brixel FactoryOS — Energy Consumption
 * Energy consumption tracking table.
 */

import { Head, Link, router, usePage } from "@inertiajs/react";
import { AppLayout } from "@/components/layout";
import { PageHeader } from "@/components/layout";
import { DataTable } from "@/components/DataTable";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import { Activity, Plus } from "lucide-react";

interface Consumption {
  id: number;
  meter_name: string;
  consumption_date: string;
  consumption_value: number;
  unit: string;
  cost: number;
}

export default function EnergyConsumption() {
  const { props: data } = usePage() as any;
  const consumption: Consumption[] = data?.consumption?.data || [];
  const meta = data?.consumption?.meta || {};

  return (
    <AppLayout title="Energy Consumption">
      <PageHeader
        title="Energy Consumption"
        subtitle="Track energy consumption by meter"
        breadcrumbs={[{ label: "Energy", href: "/energy" }, { label: "Consumption" }]}
        actions={
          <Link
            href="/energy/consumption/create"
            className="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-factory-50 text-factory-600 font-medium text-sm hover:bg-factory-100 transition-colors"
          >
            <Plus size={14} /> Add Consumption
          </Link>
        }
      />

      <DataTable
        title="Energy Consumption"
        columns={[
          { key: "meter_name", label: "Meter Name" },
          { key: "consumption_date", label: "Date" },
          { key: "consumption_value", label: "Value", align: "right", format: (v: number) => Number(v).toFixed(2) },
          { key: "unit", label: "Unit" },
          { key: "cost", label: "Cost", align: "right", format: (v: number) => `Rs. ${Number(v).toLocaleString(undefined, { maximumFractionDigits: 2 })}` },
        ]}
        data={consumption}
        meta={meta}
        rowActions={[
          { label: "View", icon: <Activity size={14} />, variant: "ghost" },
        ]}
        emptyMessage="No consumption data found."
      />
    </AppLayout>
  );
}
