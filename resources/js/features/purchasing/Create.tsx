/**
 * Brixel FactoryOS — Create Purchase Order
 * Create a new purchase order for a supplier.
 */

import { Head, Link, router, usePage, useForm } from "@inertiajs/react";
import { AppLayout } from "@/components/layout";
import { PageHeader } from "@/components/layout";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { ShoppingCart, Truck, ArrowLeft } from "lucide-react";
import { cn } from "@/lib/utils";

interface Supplier { id: number; name: string; code: string; }
interface Item { id: number; name: string; sku: string; }

export default function CreatePurchaseOrder() {
  const { props: data } = usePage() as any;
  const suppliers: Supplier[] = data?.suppliers || [];
  const items: Item[] = data?.items || [];

  const { data: formData, setData, errors, isPending } =
    useForm<{ po_number: string; supplier_id: string; order_date: string; required_date: string; notes: string }>({
      po_number: "",
      supplier_id: "",
      order_date: "",
      required_date: "",
      notes: "",
    });

  const submit = (event: React.FormEvent) => {
    event.preventDefault();
    router.post("/purchase-orders", formData, {
      onSuccess: () => router.visit("/purchasing"),
    });
  };

  return (
    <AppLayout title="Create Purchase Order">
      <PageHeader
        title="Create Purchase Order"
        subtitle="Create a new purchase order for a supplier"
        breadcrumbs={[{ label: "Purchasing", href: "/purchasing" }, { label: "New Order" }]}
      >
        <Link href="/purchasing" className="inline-flex h-9 items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 text-sm font-medium text-slate-700 shadow-sm transition-colors hover:bg-slate-50">
          <ArrowLeft size={16} /> Back to Purchasing
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
                <label className="text-sm font-medium text-slate-700">PO Number</label>
                <Input
                  value={formData.po_number}
                  onChange={(e) => setData("po_number", e.currentTarget.value)}
                  placeholder="PO-2024-0001"
                  className={cn("w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-factory-500 focus:outline-none focus:ring-1 focus:ring-factory-500", errors.po_number ? "border-rose-500" : "")}
                />
                {errors.po_number && <p className="text-xs text-rose-600">{errors.po_number}</p>}
              </div>
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Supplier</label>
                <Select onValueChange={(value) => setData("supplier_id", value)}>
                  <SelectTrigger className="w-full">
                    <SelectValue placeholder="Select supplier" />
                  </SelectTrigger>
                  <SelectContent>
                    {suppliers.map((s) => (
                      <SelectItem key={s.id} value={String(s.id)}>
                        {s.code} — {s.name}
                      </SelectItem>
                    ))}
                  </SelectContent>
                </Select>
                {errors.supplier_id && <p className="text-xs text-rose-600">{errors.supplier_id}</p>}
              </div>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Order Date</label>
                <Input
                  type="date"
                  value={formData.order_date}
                  onChange={(e) => setData("order_date", e.currentTarget.value)}
                  className={cn("w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-factory-500 focus:outline-none focus:ring-1 focus:ring-factory-500", errors.order_date ? "border-rose-500" : "")}
                />
                {errors.order_date && <p className="text-xs text-rose-600">{errors.order_date}</p>}
              </div>
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Required Date</label>
                <Input
                  type="date"
                  value={formData.required_date}
                  onChange={(e) => setData("required_date", e.currentTarget.value)}
                  className={cn("w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-factory-500 focus:outline-none focus:ring-1 focus:ring-factory-500", errors.required_date ? "border-rose-500" : "")}
                />
                {errors.required_date && <p className="text-xs text-rose-600">{errors.required_date}</p>}
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
            {isPending ? "Creating…" : <><ShoppingCart size={16} /> Create PO</>}
          </Button>
        </div>
      </form>
    </AppLayout>
  );
}
