/**
 * Brixel FactoryOS — Create Quality Inspection
 * Record a new quality inspection result.
 */

import { Head, Link, router, usePage, useForm } from "@inertiajs/react";
import { AppLayout } from "@/components/layout";
import { PageHeader } from "@/components/layout";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { Shield, ClipboardList, ArrowLeft } from "lucide-react";
import { cn } from "@/lib/utils";

interface Order { id: number; order_number: string; status: string; }

const INSPECTION_TYPES = ["incoming", "in_process", "final"] as const;
const INSPECTION_STATUSES = ["pending", "passed", "failed", "rejected"] as const;

export default function CreateQualityInspection() {
  const { props: data } = usePage() as any;
  const orders: Order[] = data?.orders || [];

  const { data: formData, setData, errors, isPending } =
    useForm<{ reference: string; reference_type: string; reference_id: string; inspection_type: string; status: string; inspected_at: string; notes: string }>({
      reference: "",
      reference_type: "production_order",
      reference_id: "",
      inspection_type: "incoming",
      status: "pending",
      inspected_at: "",
      notes: "",
    });

  const submit = (event: React.FormEvent) => {
    event.preventDefault();
    router.post("/quality/inspections", formData, {
      onSuccess: () => router.visit("/quality"),
    });
  };

  return (
    <AppLayout title="Create Quality Inspection">
      <PageHeader
        title="Create Quality Inspection"
        subtitle="Record inspection results for a production order"
        breadcrumbs={[{ label: "Quality", href: "/quality" }, { label: "New Inspection" }]}
      >
        <Link href="/quality" className="inline-flex h-9 items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 text-sm font-medium text-slate-700 shadow-sm transition-colors hover:bg-slate-50">
          <ArrowLeft size={16} /> Back to Inspections
        </Link>
      </PageHeader>

      <form onSubmit={submit} className="space-y-6 max-w-2xl">
        <Card>
          <CardHeader>
            <CardTitle className="text-base font-semibold text-slate-900">Inspection Details</CardTitle>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Reference</label>
                <Input
                  value={formData.reference}
                  onChange={(e) => setData("reference", e.currentTarget.value)}
                  placeholder="INS-2024-0001"
                  className={cn("w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-factory-500 focus:outline-none focus:ring-1 focus:ring-factory-500", errors.reference ? "border-rose-500" : "")}
                />
                {errors.reference && <p className="text-xs text-rose-600">{errors.reference}</p>}
              </div>
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Production Order</label>
                <Select onValueChange={(value) => setData("reference_id", value)}>
                  <SelectTrigger className="w-full">
                    <SelectValue placeholder="Select order" />
                  </SelectTrigger>
                  <SelectContent>
                    {orders.map((o) => (
                      <SelectItem key={o.id} value={String(o.id)}>
                        {o.order_number} ({o.status})
                      </SelectItem>
                    ))}
                  </SelectContent>
                </Select>
                {errors.reference_id && <p className="text-xs text-rose-600">{errors.reference_id}</p>}
              </div>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Inspection Type</label>
                <Select onValueChange={(value) => setData("inspection_type", value)}>
                  <SelectTrigger className="w-full">
                    <SelectValue placeholder={formData.inspection_type} />
                  </SelectTrigger>
                  <SelectContent>
                    {INSPECTION_TYPES.map((t) => (
                      <SelectItem key={t} value={t}>{t.replace("_", " ")}</SelectItem>
                    ))}
                  </SelectContent>
                </Select>
              </div>
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Status</label>
                <Select onValueChange={(value) => setData("status", value)}>
                  <SelectTrigger className="w-full">
                    <SelectValue placeholder={formData.status} />
                  </SelectTrigger>
                  <SelectContent>
                    {INSPECTION_STATUSES.map((s) => (
                      <SelectItem key={s} value={s}>{s}</SelectItem>
                    ))}
                  </SelectContent>
                </Select>
              </div>
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Inspected At</label>
                <Input
                  type="datetime-local"
                  value={formData.inspected_at}
                  onChange={(e) => setData("inspected_at", e.currentTarget.value)}
                  className={cn("w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-factory-500 focus:outline-none focus:ring-1 focus:ring-factory-500", errors.inspected_at ? "border-rose-500" : "")}
                />
                {errors.inspected_at && <p className="text-xs text-rose-600">{errors.inspected_at}</p>}
              </div>
            </div>

            <div className="space-y-1">
              <label className="text-sm font-medium text-slate-700">Notes</label>
              <Input
                value={formData.notes}
                onChange={(e) => setData("notes", e.currentTarget.value)}
                placeholder="Optional notes"
                className="w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-factory-500 focus:outline-none focus:ring-1 focus:ring-factory-500"
              />
            </div>
          </CardContent>
        </Card>

        <div className="flex justify-end">
          <Button type="submit" disabled={isPending}>
            {isPending ? "Recording…" : <><Shield size={16} /> Record Inspection</>}
          </Button>
        </div>
      </form>
    </AppLayout>
  );
}
