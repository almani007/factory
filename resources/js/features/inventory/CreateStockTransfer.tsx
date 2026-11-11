/**
 * Brixel FactoryOS — Inventory — Create Stock Transfer
 * Transfer stock between warehouses.
 */

import { Head, Link, router, usePage, useForm } from "@inertiajs/react";
import { AppLayout } from "@/components/layout";
import { PageHeader } from "@/components/layout";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { Shirt, ArrowLeft } from "lucide-react";
import { cn } from "@/lib/utils";

interface Warehouse {
  id: number;
  code: string;
  name: string;
}

export default function CreateStockTransfer() {
  const { props: data } = usePage() as any;
  const warehouses: Warehouse[] = data?.warehouses || [];

  const { data: formData, setData, errors, isPending } =
    useForm<{
      transfer_number: string;
      from_warehouse_id: string;
      to_warehouse_id: string;
      transfer_date: string;
      notes: string;
    }>({
      transfer_number: "",
      from_warehouse_id: "",
      to_warehouse_id: "",
      transfer_date: "",
      notes: "",
    });

  const submit = (event: React.FormEvent) => {
    event.preventDefault();
    router.post("/stock-transfers", formData, {
      onSuccess: () => router.visit("/inventory/transfers"),
    });
  };

  return (
    <AppLayout title="Create Stock Transfer">
      <PageHeader
        title="Create Stock Transfer"
        subtitle="Transfer inventory between warehouses"
        breadcrumbs={[
          { label: "Inventory", href: "/inventory" },
          { label: "Stock Transfers", href: "/inventory/transfers" },
          { label: "New Transfer" },
        ]}
        actions={
          <Link
            href="/inventory/transfers"
            className="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-slate-200 bg-white px-3 text-sm font-medium text-slate-700 shadow-sm transition-colors hover:bg-slate-50"
          >
            <ArrowLeft size={14} /> Back to Transfers
          </Link>
        }
      />

      <form onSubmit={submit} className="space-y-6 max-w-2xl">
        <Card>
          <CardHeader>
            <CardTitle className="text-base font-semibold text-slate-900">Transfer Details</CardTitle>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Transfer Number</label>
                <Input
                  value={formData.transfer_number}
                  onChange={(e) => setData("transfer_number", e.currentTarget.value)}
                  placeholder="TRF-2024-0001"
                  className={cn("w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-factory-500 focus:outline-none focus:ring-1 focus:ring-factory-500", errors.transfer_number ? "border-rose-500" : "")}
                />
                {errors.transfer_number && <p className="text-xs text-rose-600">{errors.transfer_number}</p>}
              </div>
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">From Warehouse</label>
                <Select onValueChange={(value) => setData("from_warehouse_id", value)}>
                  <SelectTrigger className="w-full">
                    <SelectValue placeholder="Select warehouse" />
                  </SelectTrigger>
                  <SelectContent>
                    {warehouses.map((w) => (
                      <SelectItem key={w.id} value={String(w.id)}>{w.code} — {w.name}</SelectItem>
                    ))}
                  </SelectContent>
                </Select>
                {errors.from_warehouse_id && <p className="text-xs text-rose-600">{errors.from_warehouse_id}</p>}
              </div>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">To Warehouse</label>
                <Select onValueChange={(value) => setData("to_warehouse_id", value)}>
                  <SelectTrigger className="w-full">
                    <SelectValue placeholder="Select warehouse" />
                  </SelectTrigger>
                  <SelectContent>
                    {warehouses.map((w) => (
                      <SelectItem key={w.id} value={String(w.id)}>{w.code} — {w.name}</SelectItem>
                    ))}
                  </SelectContent>
                </Select>
                {errors.to_warehouse_id && <p className="text-xs text-rose-600">{errors.to_warehouse_id}</p>}
              </div>
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Transfer Date</label>
                <Input
                  type="date"
                  value={formData.transfer_date}
                  onChange={(e) => setData("transfer_date", e.currentTarget.value)}
                  className={cn("w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-factory-500 focus:outline-none focus:ring-1 focus:ring-factory-500", errors.transfer_date ? "border-rose-500" : "")}
                />
                {errors.transfer_date && <p className="text-xs text-rose-600">{errors.transfer_date}</p>}
              </div>
            </div>

            <div className="space-y-1">
              <label className="text-sm font-medium text-slate-700">Notes</label>
              <Input
                value={formData.notes}
                onChange={(e) => setData("notes", e.currentTarget.value)}
                placeholder="Optional notes"
                className={cn("w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-factory-500 focus:outline-none focus:ring-1 focus:ring-factory-500", errors.notes ? "border-rose-500" : "")}
              />
              {errors.notes && <p className="text-xs text-rose-600">{errors.notes}</p>}
            </div>
          </CardContent>
        </Card>

        <div className="flex justify-end">
          <Button type="submit" disabled={isPending}>
            {isPending ? "Recording…" : (
              <>
                <Shirt size={16} />
                Create Transfer
              </>
            )}
          </Button>
        </div>
      </form>
    </AppLayout>
  );
}
