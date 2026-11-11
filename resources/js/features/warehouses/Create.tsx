/**
 * Brixel FactoryOS — Create Warehouse
 * Register a new warehouse or storage location.
 */

import { Head, Link, router, useForm } from "@inertiajs/react";
import { AppLayout } from "@/components/layout";
import { PageHeader } from "@/components/layout";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { Building, ArrowLeft } from "lucide-react";
import { cn } from "@/lib/utils";

export default function CreateWarehouse() {
  const { data: formData, setData, errors, isPending } =
    useForm<{ code: string; name: string; type: string; location: string; is_active: boolean }>({
      code: "",
      name: "",
      type: "raw_material",
      location: "",
      is_active: true,
    });

  const WAREHOUSE_TYPES = [
    { value: "raw_material", label: "Raw Material" },
    { value: "finished_goods", label: "Finished Goods" },
    { value: "packaging", label: "Packaging" },
    { value: "quarantine", label: "Quarantine" },
  ];

  const submit = (event: React.FormEvent) => {
    event.preventDefault();
    router.post("/warehouses", formData, {
      onSuccess: () => router.visit("/warehouses"),
    });
  };

  return (
    <AppLayout title="Create Warehouse">
      <PageHeader
        title="Create Warehouse"
        subtitle="Register a new warehouse or storage location"
        breadcrumbs={[{ label: "Warehouses", href: "/warehouses" }, { label: "New Warehouse" }]}
      >
        <Link href="/warehouses" className="inline-flex h-9 items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 text-sm font-medium text-slate-700 shadow-sm transition-colors hover:bg-slate-50">
          <ArrowLeft size={16} /> Back to Warehouses
        </Link>
      </PageHeader>

      <form onSubmit={submit} className="space-y-6 max-w-2xl">
        <Card>
          <CardHeader>
            <CardTitle className="text-base font-semibold text-slate-900">Warehouse Details</CardTitle>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Warehouse Code</label>
                <Input
                  value={formData.code}
                  onChange={(e) => setData("code", e.currentTarget.value)}
                  placeholder="WH-001"
                  className={cn("w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-factory-500 focus:outline-none focus:ring-1 focus:ring-factory-500", errors.code ? "border-rose-500" : "")}
                />
                {errors.code && <p className="text-xs text-rose-600">{errors.code}</p>}
              </div>
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Warehouse Name</label>
                <Input
                  value={formData.name}
                  onChange={(e) => setData("name", e.currentTarget.value)}
                  placeholder="Main Raw Material Store"
                  className={cn("w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-factory-500 focus:outline-none focus:ring-1 focus:ring-factory-500", errors.name ? "border-rose-500" : "")}
                />
                {errors.name && <p className="text-xs text-rose-600">{errors.name}</p>}
              </div>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Type</label>
                <Select onValueChange={(value) => setData("type", value)}>
                  <SelectTrigger className="w-full">
                    <SelectValue placeholder={formData.type} />
                  </SelectTrigger>
                  <SelectContent>
                    {WAREHOUSE_TYPES.map((t) => (
                      <SelectItem key={t.value} value={t.value}>{t.label}</SelectItem>
                    ))}
                  </SelectContent>
                </Select>
                {errors.type && <p className="text-xs text-rose-600">{errors.type}</p>}
              </div>
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Location</label>
                <Input
                  value={formData.location}
                  onChange={(e) => setData("location", e.currentTarget.value)}
                  placeholder="Building A, Floor 1"
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
                <span className="text-sm text-slate-700">Active warehouse</span>
              </label>
            </div>
          </CardContent>
        </Card>

        <div className="flex justify-end">
          <Button type="submit" disabled={isPending}>
            {isPending ? "Creating…" : <><Building size={16} /> Create Warehouse</>}
          </Button>
        </div>
      </form>
    </AppLayout>
  );
}
