/**
 * Brixel FactoryOS — Create Inventory Movement
 * Record a new stock movement (receipt, issue, transfer, adjustment).
 */

import { Head, Link, router, usePage, useForm } from "@inertiajs/react";
import { AppLayout } from "@/components/layout";
import { PageHeader } from "@/components/layout";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { Plus, Truck, ArrowLeft } from "lucide-react";
import { cn } from "@/lib/utils";

interface Item { id: number; name: string; sku: string; }
interface Warehouse { id: number; name: string; }

type MovementType = "receipt" | "issue" | "transfer" | "adjustment";

const MOVEMENT_TYPES: { value: MovementType; label: string }[] = [
  { value: "receipt", label: "Goods Receipt" },
  { value: "issue", label: "Material Issue" },
  { value: "transfer", label: "Stock Transfer" },
  { value: "adjustment", label: "Stock Adjustment" },
];

export default function CreateInventoryMovement() {
  const { props: data } = usePage() as any;
  const items: Item[] = data?.items || [];
  const warehouses: Warehouse[] = data?.warehouses || [];

  const { data: formData, setData, errors, isPending, recentErrors } =
    useForm<{ reference: string; movement_type: MovementType; item_id: string; warehouse_id: string; quantity: string; notes: string }>({
      reference: "",
      movement_type: "receipt",
      item_id: "",
      warehouse_id: "",
      quantity: "",
      notes: "",
    });

  const submit = (event: React.FormEvent) => {
    event.preventDefault();
    router.post("/inventory/movements", formData, {
      onSuccess: () => router.visit("/inventory"),
      onError: () => {},
    });
  };

  return (
    <AppLayout title="Create Stock Movement">
      <PageHeader
        title="Create Stock Movement"
        subtitle="Record a new inventory transaction"
        breadcrumbs={[{ label: "Inventory", href: "/inventory" }, { label: "New Movement" }]}
      >
        <Link href="/inventory" className="inline-flex h-9 items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 text-sm font-medium text-slate-700 shadow-sm transition-colors hover:bg-slate-50">
          <ArrowLeft size={16} /> Back to Movements
        </Link>
      </PageHeader>

      <form onSubmit={submit} className="space-y-6 max-w-2xl">
        <Card>
          <CardHeader>
            <CardTitle className="text-base font-semibold text-slate-900">Movement Details</CardTitle>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Reference</label>
                <Input
                  value={formData.reference}
                  onChange={(e) => setData("reference", e.currentTarget.value)}
                  placeholder="MOV-2024-0001"
                  className={cn("w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-factory-500 focus:outline-none focus:ring-1 focus:ring-factory-500", errors.reference ? "border-rose-500" : "")}
                />
                {errors.reference && <p className="text-xs text-rose-600">{errors.reference}</p>}
              </div>
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Movement Type</label>
                <Select onValueChange={(value) => setData("movement_type", value as MovementType)}>
                  <SelectTrigger className="w-full">
                    <SelectValue placeholder={formData.movement_type} />
                  </SelectTrigger>
                  <SelectContent>
                    {MOVEMENT_TYPES.map((t) => (
                      <SelectItem key={t.value} value={t.value}>{t.label}</SelectItem>
                    ))}
                  </SelectContent>
                </Select>
                {errors.movement_type && <p className="text-xs text-rose-600">{errors.movement_type}</p>}
              </div>
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Item</label>
                <Select onValueChange={(value) => setData("item_id", value)}>
                  <SelectTrigger className="w-full">
                    <SelectValue placeholder="Select item" />
                  </SelectTrigger>
                  <SelectContent>
                    {items.map((item) => (
                      <SelectItem key={item.id} value={String(item.id)}>
                        {item.sku} — {item.name}
                      </SelectItem>
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
                      <SelectItem key={w.id} value={String(w.id)}>{w.name}</SelectItem>
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
                  className={cn("w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-factory-500 focus:outline-none focus:ring-1 focus:ring-factory-500", errors.quantity ? "border-rose-500" : "")}
                />
                {errors.quantity && <p className="text-xs text-rose-600">{errors.quantity}</p>}
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
            </div>
          </CardContent>
        </Card>

        <div className="flex justify-end">
          <Button type="submit" disabled={isPending}>
            {isPending ? "Recording…" : <><Plus size={16} /> Record Movement</>}
          </Button>
        </div>

        {recentErrors && (
          <div className="rounded-lg bg-rose-50 px-4 py-3 text-sm text-rose-700 border border-rose-100">
            Failed to record movement. Please check the form and try again.
          </div>
        )}
      </form>
    </AppLayout>
  );
}
