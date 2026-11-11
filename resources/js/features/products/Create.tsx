/**
 * Brixel FactoryOS — Create Product
 * Add a new product item to the master data.
 */

import { Head, Link, router, usePage, useForm } from "@inertiajs/react";
import { AppLayout } from "@/components/layout";
import { PageHeader } from "@/components/layout";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { Package, ArrowLeft } from "lucide-react";
import { cn } from "@/lib/utils";

interface Category { id: number; name: string; }
interface Unit { id: number; name: string; code: string; }

export default function CreateProduct() {
  const { props: data } = usePage() as any;
  const categories: Category[] = data?.categories || [];
  const units: Unit[] = data?.units || [];

  const { data: formData, setData, errors, isPending } =
    useForm<{ sku: string; name: string; category_id: string; unit_of_measure: string; default_purchase_price: string; default_sales_price: string; reorder_level: string; is_active: boolean }>({
      sku: "",
      name: "",
      category_id: "",
      unit_of_measure: "",
      default_purchase_price: "",
      default_sales_price: "",
      reorder_level: "0",
      is_active: true,
    });

  const submit = (event: React.FormEvent) => {
    event.preventDefault();
    router.post("/items", formData, {
      onSuccess: () => router.visit("/products"),
    });
  };

  return (
    <AppLayout title="Create Product">
      <PageHeader
        title="Create Product"
        subtitle="Add a new product item to the master data"
        breadcrumbs={[{ label: "Products", href: "/products" }, { label: "New Product" }]}
      >
        <Link href="/products" className="inline-flex h-9 items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 text-sm font-medium text-slate-700 shadow-sm transition-colors hover:bg-slate-50">
          <ArrowLeft size={16} /> Back to Products
        </Link>
      </PageHeader>

      <form onSubmit={submit} className="space-y-6 max-w-2xl">
        <Card>
          <CardHeader>
            <CardTitle className="text-base font-semibold text-slate-900">Product Details</CardTitle>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">SKU</label>
                <Input
                  value={formData.sku}
                  onChange={(e) => setData("sku", e.currentTarget.value)}
                  placeholder="SKU-0001"
                  className={cn("w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-factory-500 focus:outline-none focus:ring-1 focus:ring-factory-500", errors.sku ? "border-rose-500" : "")}
                />
                {errors.sku && <p className="text-xs text-rose-600">{errors.sku}</p>}
              </div>
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Product Name</label>
                <Input
                  value={formData.name}
                  onChange={(e) => setData("name", e.currentTarget.value)}
                  placeholder="Raw Material"
                  className={cn("w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-factory-500 focus:outline-none focus:ring-1 focus:ring-factory-500", errors.name ? "border-rose-500" : "")}
                />
                {errors.name && <p className="text-xs text-rose-600">{errors.name}</p>}
              </div>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Category</label>
                <Select onValueChange={(value) => setData("category_id", value)}>
                  <SelectTrigger className="w-full">
                    <SelectValue placeholder="Select category" />
                  </SelectTrigger>
                  <SelectContent>
                    {categories.map((c) => (
                      <SelectItem key={c.id} value={String(c.id)}>{c.name}</SelectItem>
                    ))}
                  </SelectContent>
                </Select>
                {errors.category_id && <p className="text-xs text-rose-600">{errors.category_id}</p>}
              </div>
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Unit of Measure</label>
                <Select onValueChange={(value) => setData("unit_of_measure", value)}>
                  <SelectTrigger className="w-full">
                    <SelectValue placeholder={formData.unit_of_measure} />
                  </SelectTrigger>
                  <SelectContent>
                    {units.map((u) => (
                      <SelectItem key={u.id} value={u.code}>{u.name} ({u.code})</SelectItem>
                    ))}
                  </SelectContent>
                </Select>
                {errors.unit_of_measure && <p className="text-xs text-rose-600">{errors.unit_of_measure}</p>}
              </div>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Purchase Price</label>
                <Input
                  type="number"
                  step="0.01"
                  value={formData.default_purchase_price}
                  onChange={(e) => setData("default_purchase_price", e.currentTarget.value)}
                  placeholder="0.00"
                  className={cn("w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-factory-500 focus:outline-none focus:ring-1 focus:ring-factory-500", errors.default_purchase_price ? "border-rose-500" : "")}
                />
                {errors.default_purchase_price && <p className="text-xs text-rose-600">{errors.default_purchase_price}</p>}
              </div>
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Sales Price</label>
                <Input
                  type="number"
                  step="0.01"
                  value={formData.default_sales_price}
                  onChange={(e) => setData("default_sales_price", e.currentTarget.value)}
                  placeholder="0.00"
                  className={cn("w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-factory-500 focus:outline-none focus:ring-1 focus:ring-factory-500", errors.default_sales_price ? "border-rose-500" : "")}
                />
                {errors.default_sales_price && <p className="text-xs text-rose-600">{errors.default_sales_price}</p>}
              </div>
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Reorder Level</label>
                <Input
                  type="number"
                  value={formData.reorder_level}
                  onChange={(e) => setData("reorder_level", e.currentTarget.value)}
                  placeholder="0"
                  className="w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-factory-500 focus:outline-none focus:ring-1 focus:ring-factory-500"
                />
              </div>
            </div>

            <div className="space-y-1">
              <label className="flex items-center gap-2 cursor-pointer">
                <input
                  type="checkbox"
                  checked={formData.is_active}
                  onChange={(e) => setData("is_active", e.currentTarget.checked)}
                  className="rounded border-slate-300 text-factory-600 focus:ring-factory-500"
                />
                <span className="text-sm text-slate-700">Active product</span>
              </label>
            </div>
          </CardContent>
        </Card>

        <div className="flex justify-end">
          <Button type="submit" disabled={isPending}>
            {isPending ? "Creating…" : <><Package size={16} /> Create Product</>}
          </Button>
        </div>
      </form>
    </AppLayout>
  );
}
