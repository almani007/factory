/**
 * Brixel FactoryOS — Create Production Order
 * Create a new production order from a BOM.
 */

import { Head, Link, router, usePage, useForm } from "@inertiajs/react";
import { AppLayout } from "@/components/layout";
import { PageHeader } from "@/components/layout";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { Factory, ClipboardList, ArrowLeft } from "lucide-react";
import { cn } from "@/lib/utils";

interface Bom { id: number; item_name: string; version: string; }

export default function CreateProductionOrder() {
  const { props: data } = usePage() as any;
  const boms: Bom[] = data?.boms || [];

  const { data: formData, setData, errors, isPending } =
    useForm<{ order_number: string; bom_header_id: string; planned_quantity: string; planned_start_date: string; notes: string }>({
      order_number: "",
      bom_header_id: "",
      planned_quantity: "",
      planned_start_date: "",
      notes: "",
    });

  const submit = (event: React.FormEvent) => {
    event.preventDefault();
    router.post("/production-orders", formData, {
      onSuccess: () => router.visit("/production"),
    });
  };

  return (
    <AppLayout title="Create Production Order">
      <PageHeader
        title="Create Production Order"
        subtitle="Create a new work order from a bill of materials"
        breadcrumbs={[{ label: "Production", href: "/production" }, { label: "New Order" }]}
      >
        <Link href="/production" className="inline-flex h-9 items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 text-sm font-medium text-slate-700 shadow-sm transition-colors hover:bg-slate-50">
          <ArrowLeft size={16} /> Back to Orders
        </Link>
      </PageHeader>

      <form onSubmit={submit} className="space-y-6 max-w-2xl">
        <Card>
          <CardHeader>
            <CardTitle className="text-base font-semibold text-slate-900">Order Details</CardTitle>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Order Number</label>
                <Input
                  value={formData.order_number}
                  onChange={(e) => setData("order_number", e.currentTarget.value)}
                  placeholder="WO-2024-0001"
                  className={cn("w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-factory-500 focus:outline-none focus:ring-1 focus:ring-factory-500", errors.order_number ? "border-rose-500" : "")}
                />
                {errors.order_number && <p className="text-xs text-rose-600">{errors.order_number}</p>}
              </div>
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">BOM / Product</label>
                <Select onValueChange={(value) => setData("bom_header_id", value)}>
                  <SelectTrigger className="w-full">
                    <SelectValue placeholder="Select BOM" />
                  </SelectTrigger>
                  <SelectContent>
                    {boms.map((bom) => (
                      <SelectItem key={bom.id} value={String(bom.id)}>
                        {bom.item_name} (v{bom.version})
                      </SelectItem>
                    ))}
                  </SelectContent>
                </Select>
                {errors.bom_header_id && <p className="text-xs text-rose-600">{errors.bom_header_id}</p>}
              </div>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Planned Quantity</label>
                <Input
                  type="number"
                  value={formData.planned_quantity}
                  onChange={(e) => setData("planned_quantity", e.currentTarget.value)}
                  placeholder="0"
                  className={cn("w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-factory-500 focus:outline-none focus:ring-1 focus:ring-factory-500", errors.planned_quantity ? "border-rose-500" : "")}
                />
                {errors.planned_quantity && <p className="text-xs text-rose-600">{errors.planned_quantity}</p>}
              </div>
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Planned Start Date</label>
                <Input
                  type="date"
                  value={formData.planned_start_date}
                  onChange={(e) => setData("planned_start_date", e.currentTarget.value)}
                  className={cn("w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-factory-500 focus:outline-none focus:ring-1 focus:ring-factory-500", errors.planned_start_date ? "border-rose-500" : "")}
                />
                {errors.planned_start_date && <p className="text-xs text-rose-600">{errors.planned_start_date}</p>}
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
            {isPending ? "Creating…" : <><Factory size={16} /> Create Order</>}
          </Button>
        </div>
      </form>
    </AppLayout>
  );
}
