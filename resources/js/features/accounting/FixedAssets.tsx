/**
 * Brixel FactoryOS — Fixed Assets
 * Fixed asset register with depreciation tracking.
 */

import { Head, Link, router, usePage } from "@inertiajs/react";
import { AppLayout } from "@/components/layout";
import { PageHeader } from "@/components/layout";
import { DataTable } from "@/components/DataTable";
import { StatStrip } from "@/components/StatStrip";
import { Plus, Building2, Eye, Edit } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";

interface FixedAsset {
  id: number;
  asset_code: string;
  asset_name: string;
  asset_category: string;
  account_name: string;
  purchase_date: string;
  purchase_cost: string;
  accumulated_depreciation: string;
  net_book_value: string;
  status: string;
  status_label: string;
  location: string;
}

const STATUS_BADGE: Record<string, string> = {
  active: "bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-200",
  disposed: "bg-slate-100 text-slate-500 ring-1 ring-inset ring-slate-200",
  retired: "bg-rose-50 text-rose-700 ring-1 ring-inset ring-rose-200",
};

export default function FixedAssets() {
  const { props: data } = usePage() as any;
  const assets: FixedAsset[] = data?.assets?.data || [];
  const meta = data?.assets?.meta || {};
  const summary = data?.asset_summary || { total: 0, total_cost: "Rs. 0", total_nbv: "Rs. 0" };

  return (
    <AppLayout title="Fixed Assets">
      <PageHeader
        title="Fixed Assets"
        subtitle="Fixed asset register with depreciation tracking"
        breadcrumbs={[{ label: "Accounting", href: "/accounting" }, { label: "Fixed Assets" }]}
      >
        <Button onClick={() => router.visit("/accounting/fixed-assets/create")}>
          <Plus size={16} /> New Asset
        </Button>
      </PageHeader>

      <StatStrip
        items={[
          { label: "Total Assets", value: summary.total, hint: "All fixed assets" },
          { label: "Total Cost", value: summary.total_cost, hint: "Original purchase cost" },
          { label: "Net Book Value", value: summary.total_nbv, hint: "Current book value" },
        ]}
      />

      <DataTable
        title="Fixed Assets"
        columns={[
          { key: "asset_code", label: "Asset Code" },
          { key: "asset_name", label: "Asset Name" },
          { key: "asset_category", label: "Category" },
          { key: "purchase_date", label: "Purchase Date" },
          { key: "purchase_cost", label: "Purchase Cost", align: "right" },
          { key: "accumulated_depreciation", label: "Accum. Depreciation", align: "right" },
          { key: "net_book_value", label: "Net Book Value", align: "right" },
          { key: "status", label: "Status", format: (value: string) => (
            <Badge className={STATUS_BADGE[value] || "bg-slate-100 text-slate-600"}>
              {value}
            </Badge>
          )},
        ]}
        data={assets}
        meta={meta}
        rowActions={[
          { label: "View", icon: <Eye size={14} />, variant: "ghost" },
          { label: "Edit", icon: <Edit size={14} />, variant: "outline" },
        ]}
      />
    </AppLayout>
  );
}
