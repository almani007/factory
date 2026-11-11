/**
 * Brixel FactoryOS — Sustainability Carbon Tracking
 * Carbon emissions tracking table.
 */

import { Head, Link, router, usePage } from "@inertiajs/react";
import { AppLayout } from "@/components/layout";
import { PageHeader } from "@/components/layout";
import { DataTable } from "@/components/DataTable";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import { TreePine, Plus } from "lucide-react";

interface Carbon {
  id: number;
  entry_date: string;
  scope: string;
  carbon_emissions_tons: number;
  source: string;
}

export default function CarbonTracking() {
  const { props: data } = usePage() as any;
  const carbon: Carbon[] = data?.carbon?.data || [];
  const meta = data?.carbon?.meta || {};

  return (
    <AppLayout title="Carbon Tracking">
      <PageHeader
        title="Carbon Tracking"
        subtitle="Track carbon emissions by scope"
        breadcrumbs={[{ label: "Sustainability", href: "/sustainability" }, { label: "Carbon Tracking" }]}
        actions={
          <Link
            href="/sustainability/carbon/create"
            className="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-factory-50 text-factory-600 font-medium text-sm hover:bg-factory-100 transition-colors"
          >
            <Plus size={14} /> Add Carbon Entry
          </Link>
        }
      />

      <DataTable
        title="Carbon Emissions"
        columns={[
          { key: "entry_date", label: "Date" },
          { key: "scope", label: "Scope" },
          { key: "carbon_emissions_tons", label: "CO2 (tons)", align: "right", format: (v: number) => Number(v).toFixed(2) },
          { key: "source", label: "Source" },
        ]}
        data={carbon}
        meta={meta}
        rowActions={[
          { label: "View", icon: <TreePine size={14} />, variant: "ghost" },
        ]}
        emptyMessage="No carbon data found."
      />
    </AppLayout>
  );
}
