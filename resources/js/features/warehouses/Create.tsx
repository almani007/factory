/**
 * Brixel FactoryOS — Create Warehouse
 * Register a new warehouse or storage location.
 */

import { Link, router, useForm, usePage } from "@inertiajs/react";
import { AppLayout } from "@/components/layout";
import { PageHeader } from "@/components/layout";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { Building, ArrowLeft } from "lucide-react";
import { cn } from "@/lib/utils";

export default function CreateWarehouse() {
  const { props } = usePage() as any;
  const factories = props?.factories || [];
  const { data: formData, setData, errors, processing, post } = useForm({
    code: "",
    name: "",
    type: "raw_material",
    location: "",
    factory_id: "",
    is_active: true,
  });

  const WAREHOUSE_TYPES = [
    { value: "raw_material", label: "Raw Material" },
    { value: "finished_goods", label: "Finished Goods" },
    { value: "packaging", label: "Packaging" },
    { value: "quarantine", label: "Quarantine" },
    { value: "wip", label: "Work in Progress" },
    { value: "general", label: "General" },
  ];

  const submit = (event: React.FormEvent) => {
    event.preventDefault();
    post("/warehouses", { onSuccess: () => router.visit("/warehouses") });
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
          <CardHeader><CardTitle className="text-base font-semibold text-slate-900">Warehouse Details</CardTitle></CardHeader>
          <CardContent className="space-y-4">
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Warehouse Code</label>
                <Input value={formData.code} onChange={(e) => setData("code", e.currentTarget.value)} placeholder="WH-001" className={cn(errors.code ? "border-rose-500" : "")} />
                {errors.code && <p className="text-xs text-rose-600">{errors.code}</p>}
              </div>
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Warehouse Name</label>
                <Input value={formData.name} onChange={(e) => setData("name", e.currentTarget.value)} placeholder="Main Raw Material Store" className={cn(errors.name ? "border-rose-500" : "")} />
                {errors.name && <p className="text-xs text-rose-600">{errors.name}</p>}
              </div>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Type</label>
                <Select value={formData.type} onValueChange={(value) => setData("type", value)}>
                  <SelectTrigger className="w-full"><SelectValue placeholder="Select type" /></SelectTrigger>
                  <SelectContent>{WAREHOUSE_TYPES.map((t) => (<SelectItem key={t.value} value={t.value}>{t.label}</SelectItem>))}</SelectContent>
                </Select>
                {errors.type && <p className="text-xs text-rose-600">{errors.type}</p>}
              </div>
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Factory</label>
                <Select value={formData.factory_id || "none"} onValueChange={(value) => setData("factory_id", value === "none" ? "" : value)}>
                  <SelectTrigger className="w-full"><SelectValue placeholder="Select factory" /></SelectTrigger>
                  <SelectContent>
                    <SelectItem value="none">Unassigned</SelectItem>
                    {factories.map((f: any) => (<SelectItem key={f.id} value={String(f.id)}>{f.name}</SelectItem>))}
                  </SelectContent>
                </Select>
              </div>
            </div>

            <div className="space-y-1">
              <label className="text-sm font-medium text-slate-700">Location / Address</label>
              <Input value={formData.location} onChange={(e) => setData("location", e.currentTarget.value)} placeholder="Building A, Floor 1" />
            </div>

            <label className="flex items-center gap-2 cursor-pointer">
              <input type="checkbox" checked={formData.is_active} onChange={(e) => setData("is_active", e.currentTarget.checked)} className="rounded border-slate-300 text-factory-600 focus:ring-factory-500" />
              <span className="text-sm text-slate-700">Active warehouse</span>
            </label>
          </CardContent>
        </Card>

        <div className="flex justify-end">
          <Button type="submit" disabled={processing}>{processing ? "Creating…" : <><Building size={16} /> Create Warehouse</>}</Button>
        </div>
      </form>
    </AppLayout>
  );
}
