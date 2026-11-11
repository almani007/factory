/**
 * Brixel FactoryOS — Run MRP
 * Execute Material Requirements Planning to generate purchase recommendations.
 */

import { Head, Link, usePage, router } from "@inertiajs/react";
import { AppLayout } from "@/components/layout";
import { PageHeader } from "@/components/layout";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Activity, Play, ArrowLeft, RefreshCw } from "lucide-react";
import { cn } from "@/lib/utils";

export default function RunMRP() {
  const { props: data } = usePage() as any;
  const isRunning = data?.is_running || false;
  const lastRun = data?.last_run || null;
  const recommendations = data?.recommendations || [];

  return (
    <AppLayout title="Run MRP">
      <PageHeader
        title="Run MRP"
        subtitle="Execute Material Requirements Planning to generate purchase recommendations"
        breadcrumbs={[{ label: "Operations", href: "/mrp" }, { label: "MRP Planning" }, { label: "Run MRP" }]}
        actions={
          <Link href="/mrp" className="inline-flex h-9 items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 text-sm font-medium text-slate-700 shadow-sm transition-colors hover:bg-slate-50">
            <ArrowLeft size={16} /> Back to MRP
          </Link>
        }
      />

      <div className="mb-8 grid grid-cols-1 md:grid-cols-3 gap-6">
        <Card>
          <CardHeader>
            <CardTitle className="text-base font-semibold text-slate-900">Last MRP Run</CardTitle>
          </CardHeader>
          <CardContent className="p-5 pt-0">
            <p className="text-2xl font-semibold text-slate-900">
              {lastRun ? new Date(lastRun).toLocaleDateString() : "Never"}
            </p>
            <p className="text-xs text-slate-500 mt-1">MRP was last executed</p>
          </CardContent>
        </Card>
        <Card>
          <CardHeader>
            <CardTitle className="text-base font-semibold text-slate-900">Items Analyzed</CardTitle>
          </CardHeader>
          <CardContent className="p-5 pt-0">
            <p className="text-2xl font-semibold text-slate-900">{data?.items_analyzed || 0}</p>
            <p className="text-xs text-slate-500 mt-1">SKUs evaluated</p>
          </CardContent>
        </Card>
        <Card>
          <CardHeader>
            <CardTitle className="text-base font-semibold text-slate-900">Purchase Recommendations</CardTitle>
          </CardHeader>
          <CardContent className="p-5 pt-0">
            <p className="text-2xl font-semibold text-slate-900">{recommendations.length}</p>
            <p className="text-xs text-slate-500 mt-1">Recommended purchase orders</p>
          </CardContent>
        </Card>
      </div>

      <div className="flex flex-col gap-3">
        <Button
          onClick={() => router.post("/mrp/run", {}, { onSuccess: () => router.visit("/mrp") })}
          disabled={isRunning}
          className={cn("w-full justify-center", isRunning ? "opacity-70" : "")}
        >
          {isRunning ? (
            <>
              <RefreshCw className="animate-spin" size={16} /> Running MRP…
            </>
          ) : (
            <>
              <Play size={16} /> Run MRP Now
            </>
          )}
        </Button>
        <p className="text-xs text-slate-500">
          Running MRP will analyze all SKUs against current stock levels, open
          sales orders, and production requirements to generate purchase
          recommendations.
        </p>
      </div>

      {recommendations.length > 0 && (
        <div className="mt-8">
          <h2 className="text-lg font-semibold text-slate-900 mb-4">Purchase Recommendations</h2>
          <Card>
            <CardContent className="p-0">
              <div className="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                <table className="w-full text-sm">
                  <thead>
                    <tr className="border-b border-slate-200 bg-slate-50/80">
                      {[
                        { key: "sku", label: "SKU" },
                        { key: "item_name", label: "Item" },
                        { key: "recommended_qty", label: "Rec. Qty" },
                        { key: "suggested_supplier", label: "Suggested Supplier" },
                        { key: "priority", label: "Priority" },
                      ].map((col) => (
                        <th key={col.key} className="whitespace-nowrap px-5 py-3 text-[11px] font-semibold uppercase tracking-wider text-slate-500 text-left">
                          {col.label}
                        </th>
                      ))}
                    </tr>
                  </thead>
                  <tbody className="divide-y divide-slate-100">
                    {recommendations.map((row: any, index: number) => (
                      <tr key={index} className="transition-colors hover:bg-slate-50/70">
                        <td className="whitespace-nowrap px-5 py-3 text-slate-700">{row.sku}</td>
                        <td className="whitespace-nowrap px-5 py-3 text-slate-700">{row.item_name}</td>
                        <td className="whitespace-nowrap px-5 py-3 text-slate-700 text-right">{row.recommended_qty}</td>
                        <td className="whitespace-nowrap px-5 py-3 text-slate-700">{row.suggested_supplier}</td>
                        <td className="whitespace-nowrap px-5 py-3 text-slate-700">
                          <Badge className={row.priority === "critical" ? "bg-rose-50 text-rose-700" : "bg-amber-50 text-amber-700"}>
                            {row.priority}
                          </Badge>
                        </td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>
            </CardContent>
          </Card>
        </div>
      )}
    </AppLayout>
  );
}
