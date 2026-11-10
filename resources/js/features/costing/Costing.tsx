/**
 * Brixel FactoryOS — Costing Page
 * Production costing and variance analysis.
 */

import { Head, Link, usePage } from "@inertiajs/react";
import { AppLayout } from "@/components/layout";
import { PageHeader } from "@/components/layout";
import { KPICard } from "@/components/KPICard";
import { DataTable } from "@/components/DataTable";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";

interface CostVariance {
  id: number;
  production_order_id: string;
  item_name: string;
  standard_cost: number;
  actual_cost: number;
  variance_amount: number;
  variance_percentage: number;
}

export default function Costing() {
  const { props: data } = usePage() as any;
  const variances: CostVariance[] = data?.variances?.data || [];
  const meta = data?.variances?.meta || {};

  return (
    <AppLayout title="Production Costing">
      <PageHeader
        title="Production Costing"
        subtitle="Standard vs actual cost analysis and variance tracking"
      />

      {/* Cost Summary Cards */}
      <div className="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
        <KPICard
          title="Total Material Cost"
          value="Rs. 90,000"
          change="+12.5%"
          trend="up"
        />
        <KPICard
          title="Total Labour Cost"
          value="Rs. 27,000"
          change="+8.0%"
          trend="up"
        />
        <KPICard
          title="Machine & Overhead"
          value="Rs. 32,000"
          change="within tolerance"
          trend="neutral"
        />
        <KPICard
          title="Avg Unit Cost"
          value="Rs. 149"
          change="+10.4%"
          trend="up"
        />
      </div>

      <DataTable
        title="Cost Variances"
        columns={[
          { key: "production_order_id", label: "Order" },
          { key: "item_name", label: "Product" },
          { key: "standard_cost", label: "Standard Cost" },
          { key: "actual_cost", label: "Actual Cost" },
          { key: "variance_amount", label: "Variance" },
          { key: "variance_percentage", label: "Var %" },
        ]}
        data={variances}
        meta={meta}
      />
    </AppLayout>
  );
}