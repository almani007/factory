/**
 * Brixel FactoryOS — Fixed Asset Detail
 * Asset profile, values, and straight-line depreciation schedule.
 */

import { Link, usePage } from "@inertiajs/react";
import { AppLayout } from "@/components/layout";
import { PageHeader } from "@/components/layout";
import { DataTable } from "@/components/DataTable";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";
import { ArrowLeft, Building2, BookOpen } from "lucide-react";

function fmt(n: any) {
  return `Rs. ${Number(n || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
}

const STATUS_BADGE: Record<string, string> = {
  active: "bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-200",
  disposed: "bg-slate-100 text-slate-500 ring-1 ring-inset ring-slate-200",
  retired: "bg-rose-50 text-rose-700 ring-1 ring-inset ring-rose-200",
};

function InfoRow({ label, value }: { label: string; value: any }) {
  return (
    <div className="flex items-start justify-between gap-4 py-2">
      <span className="text-xs font-medium uppercase tracking-wider text-slate-500">{label}</span>
      <span className="text-right text-sm text-slate-800">{value || <span className="text-slate-400">—</span>}</span>
    </div>
  );
}

export default function FixedAssetShow() {
  const { props } = usePage() as any;
  const asset = props?.asset || {};
  const schedule = props?.schedule || [];

  return (
    <AppLayout title={`Fixed Asset ${asset.asset_code || ""}`}>
      <PageHeader
        title={`${asset.asset_code || ""} — ${asset.asset_name || ""}`.trim()}
        subtitle={asset.asset_category || "Fixed asset"}
        breadcrumbs={[{ label: "Accounting", href: "/accounting" }, { label: "Fixed Assets", href: "/accounting/fixed-assets" }, { label: asset.asset_code || "Asset" }]}
      >
        <div className="flex flex-wrap items-center gap-2">
          <Link href="/accounting/fixed-assets" className="inline-flex h-9 items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50"><ArrowLeft size={16} /> Back</Link>
          {asset.account_id && (
            <Link href={`/accounting/chart-of-accounts/${asset.account_id}/ledger`} className="inline-flex h-9 items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50"><BookOpen size={16} /> Account Ledger</Link>
          )}
        </div>
      </PageHeader>

      <div className="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <Card><CardContent className="p-5"><p className="text-xs font-medium text-slate-500">Purchase Cost</p><p className="mt-1 text-xl font-semibold tabular-nums text-slate-900">{fmt(asset.purchase_cost)}</p></CardContent></Card>
        <Card><CardContent className="p-5"><p className="text-xs font-medium text-slate-500">Accumulated Depreciation</p><p className="mt-1 text-xl font-semibold tabular-nums text-rose-700">{fmt(asset.accumulated_depreciation)}</p></CardContent></Card>
        <Card><CardContent className="p-5"><p className="text-xs font-medium text-slate-500">Net Book Value</p><p className="mt-1 text-xl font-semibold tabular-nums text-slate-900">{fmt(asset.net_book_value)}</p></CardContent></Card>
        <Card><CardContent className="p-5"><p className="text-xs font-medium text-slate-500">Status</p><div className="mt-2"><Badge className={STATUS_BADGE[asset.status] || STATUS_BADGE.active}>{asset.status || "—"}</Badge></div></CardContent></Card>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div className="space-y-6">
          <Card>
            <CardHeader><CardTitle className="text-sm font-semibold text-slate-900 flex items-center gap-2"><Building2 size={15} /> Asset Profile</CardTitle></CardHeader>
            <CardContent className="divide-y divide-slate-100 pt-0">
              <InfoRow label="Asset Code" value={<span className="font-mono">{asset.asset_code}</span>} />
              <InfoRow label="Name" value={asset.asset_name} />
              <InfoRow label="Category" value={asset.asset_category} />
              <InfoRow label="Purchase Date" value={asset.purchase_date} />
              <InfoRow label="Useful Life" value={asset.useful_life_years ? `${asset.useful_life_years} years` : null} />
              <InfoRow label="Salvage Value" value={fmt(asset.salvage_value)} />
              <InfoRow label="Location" value={asset.location} />
              <InfoRow label="GL Account" value={asset.account_name ? `${asset.account_code} — ${asset.account_name}` : null} />
            </CardContent>
          </Card>
        </div>

        <div className="lg:col-span-2">
          <DataTable
            title="Depreciation Schedule"
            columns={[
              { key: "period_start", label: "Period Start" },
              { key: "period_end", label: "Period End" },
              { key: "depreciation_amount", label: "Depreciation", align: "right", format: (v: number) => fmt(v) },
              { key: "accumulated_depreciation", label: "Accumulated", align: "right", format: (v: number) => fmt(v) },
              { key: "net_book_value", label: "Net Book Value", align: "right", format: (v: number) => fmt(v) },
            ]}
            data={schedule}
            emptyMessage="No depreciation schedule generated for this asset."
          />
        </div>
      </div>
    </AppLayout>
  );
}
