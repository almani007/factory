/**
 * Brixel FactoryOS — Create Serial Number
 * Register a new serialized item.
 */

import { Head, Link, router, usePage, useForm } from "@inertiajs/react";
import { AppLayout } from "@/components/layout";
import { PageHeader } from "@/components/layout";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { Target, ArrowLeft } from "lucide-react";
import { cn } from "@/lib/utils";

interface Item { id: number; sku: string; name: string; }

export default function CreateSerial() {
  const { props: data } = usePage() as any;
  const items: Item[] = data?.items || [];

  const { data: formData, setData, errors, isPending } =
    useForm<{ serial_number: string; item_id: string; status: string; warranty_expiry: string; current_location: string }>({
      serial_number: "",
      item_id: "",
      status: "in_stock",
      warranty_expiry: "",
      current_location: "",
    });

  const submit = (event: React.FormEvent) => {
    event.preventDefault();
    router.post("/stock-serials", formData, {
      onSuccess: () => router.visit("/inventory/serials"),
    });
  };

  return (
    <AppLayout title="Create Serial Number">
      <PageHeader
        title="Create Serial Number"
        subtitle="Register a new serialized item"
        breadcrumbs={[{ label: "Inventory", href: "/inventory" }, { label: "Serial Numbers" }, { label: "New Serial" }]}
      >
        <Link href="/inventory/serials" className="inline-flex h-9 items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 text-sm font-medium text-slate-700 shadow-sm transition-colors hover:bg-slate-50">
          <ArrowLeft size={16} /> Back to Serials
        </Link>
      </PageHeader>

      <form onSubmit={submit} className="space-y-6 max-w-2xl">
        <Card>
          <CardHeader>
            <CardTitle className="text-base font-semibold text-slate-900">Serial Details</CardTitle>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Serial Number</label>
                <Input
                  value={formData.serial_number}
                  onChange={(e) => setData("serial_number", e.currentTarget.value)}
                  placeholder="SN-2024-0001"
                  className={cn("w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-factory-500 focus:outline-none focus:ring-1 focus:ring-factory-500", errors.serial_number ? "border-rose-500" : "")}
                />
                {errors.serial_number && <p className="text-xs text-rose-600">{errors.serial_number}</p>}
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

            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Status</label>
                <Select onValueChange={(value) => setData("status", value)}>
                  <SelectTrigger className="w-full">
                    <SelectValue placeholder={formData.status} />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="in_stock">In Stock</SelectItem>
                    <SelectItem value="sold">Sold</SelectItem>
                    <SelectItem value="in_repair">In Repair</SelectItem>
                    <SelectItem value="scrapped">Scrapped</SelectItem>
                  </SelectContent>
                </Select>
                {errors.status && <p className="text-xs text-rose-600">{errors.status}</p>}
              </div>
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Location</label>
                <Input
                  value={formData.current_location}
                  onChange={(e) => setData("current_location", e.currentTarget.value)}
                  placeholder="Warehouse A"
                  className="w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-factory-500 focus:outline-none focus:ring-1 focus:ring-factory-500"
                />
              </div>
            </div>

            <div className="space-y-1">
              <label className="text-sm font-medium text-slate-700">Warranty Expiry</label>
              <Input
                type="date"
                value={formData.warranty_expiry}
                onChange={(e) => setData("warranty_expiry", e.currentTarget.value)}
                className="w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-factory-500 focus:outline-none focus:ring-1 focus:ring-factory-500"
              />
            </div>
          </CardContent>
        </Card>

        <div className="flex justify-end">
          <Button type="submit" disabled={isPending}>
            {isPending ? "Creating…" : <><Target size={16} /> Create Serial</>}
          </Button>
        </div>
      </form>
    </AppLayout>
  );
}
