/**
 * Brixel FactoryOS — Create Batch
 * Create a new inventory batch/lot.
 */

import { Head, Link, router, usePage, useForm } from "@inertiajs/react";
import { AppLayout } from "@/components/layout";
import { PageHeader } from "@/components/layout";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { Bot, ArrowLeft } from "lucide-react";
import { cn } from "@/lib/utils";

interface Item { id: number; sku: string; name: string; }
interface Warehouse { id: number; code: string; name: string; }

export default function CreateBatch() {
  const { props: data } = usePage() as any;
  const items: Item[] = data?.items || [];
  const warehouses: Warehouse[] = data?.warehouses || [];

  const { data: formData, setData, errors, isPending } =
    useForm<{ batch_number: string; item_id: string; warehouse_id: string; quantity: string; manufacturing_date: string; expiry_date: string; status: string }>({
      batch_number: "",
      item_id: "",
      warehouse_id: "",
      quantity: "",
      manufacturing_date: "",
      expiry_date: "",
      status: "active",
    });

  const submit = (event: React.FormEvent) => {
    event.preventDefault();
    router.post("/stock-batches", formData, {
      onSuccess: () => router.visit("/inventory/batches"),
    });
  };

  return (
    <AppLayout title="Create Batch">
      <PageHeader
        title="Create Batch"
        subtitle="Create a new inventory batch/lot"
        breadcrumbs={[{ label: "Inventory", href: "/inventory" }, { label: "Batches / Lots" }, { label: "New Batch" }]}
      >
        <Link href="/inventory/batches" className="inline-flex h-9 items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 text-sm font-medium text-slate-700 shadow-sm transition-colors hover:bg-slate-50">
          <ArrowLeft size={16} /> Back to Batches
        </Link>
      </PageHeader>

      <form onSubmit={submit} className="space-y-6 max-w-2xl">
        <Card>
          <CardHeader>
            <CardTitle className="text-base font-semibold text-slate-900">Batch Details</CardTitle>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Batch Number</label>
                <Input
                  value={formData.batch_number}
                  onChange={(e) => setData("batch_number", e.currentTarget.value)}
                  placeholder="BATCH-2024-0001"
                  className={cn("w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-factory-500 focus:outline-none focus:ring-1 focus:ring-factory-500", errors.batch_number ? "border-rose-500" : "")}
                />
                {errors.batch_number && <p className="text-xs text-rose-600">{errors.batch_number}</p>}
              </div>
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Item</label>
                <Select onValueChange={(value) => setData("item_id", value)}>
                  <SelectTrigger className="w-full">
                    <SelectValue placeholder="Select item" />
                  </SelectTrigger>
                  <SelectContent>
                    {items.map((i) => (
                      <SelectItem key={i.id} value={String(i.id)}>{i.sku} — {i.name}</SelectItem>
                    ))}
                  </SelectContent>
                </Select>
                {errors.item_id && <p className="text-xs text-rose-600">{errors.item_id}</p>}
              </div>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Warehouse</label>
                <Select onValueChange={(value) => setData("warehouse_id", value)}>
                  <SelectTrigger className="w-full">
                    <SelectValue placeholder="Select warehouse" />
                  </SelectTrigger>
                  <SelectContent>
                    {warehouses.map((w) => (
                      <SelectItem key={w.id} value={String(w.id)}>{w.code} — {w.name}</SelectItem>
                    ))}
                  </SelectContent>
                </Select>
                {errors.warehouse_id && <p className="text-xs text-rose-600">{errors.warehouse_id}</p>}
              </div>
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Quantity</label>
                <Input
                  type="number"
                  value={formData.quantity}
                  onChange={(e) => setData("quantity", e.currentTarget.value)}
                  placeholder="0"
                  className="w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-factory-500 focus:outline-none focus:ring-1 focus:ring-factory-500"
                />
              </div>
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Status</label>
                <Select onValueChange={(value) => setData("status", value)}>
                  <SelectTrigger className="w-full">
                    <SelectValue placeholder={formData.status} />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="active">Active</SelectItem>
                    <SelectItem value="expired">Expired</SelectItem>
                    <SelectItem value="consumed">Consumed</SelectItem>
                    <SelectItem value="quarantined">Quarantined</SelectItem>
                  </SelectContent>
                </Select>
                {errors.status && <p className="text-xs text-rose-600">{errors.status}</p>}
              </div>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Manufacturing Date</label>
                <Input
                  type="date"
                  value={formData.manufacturing_date}
                  onChange={(e) => setData("manufacturing_date", e.currentTarget.value)}
                  className="w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-factory-500 focus:outline-none focus:ring-1 focus:ring-factory-500"
                />
              </div>
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Expiry Date</label>
                <Input
                  type="date"
                  value={formData.expiry_date}
                  onChange={(e) => setData("expiry_date", e.currentTarget.value)}
                  className="w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-factory-500 focus:outline-none focus:ring-1 focus:ring-factory-500"
                />
              </div>
            </div>
          </CardContent>
        </Card>

        <div className="flex justify-end">
          <Button type="submit" disabled={isPending}>
            {isPending ? "Creating…" : <><Bot size={16} /> Create Batch</>}
          </Button>
        </div>
      </form>
    </AppLayout>
  );
}
