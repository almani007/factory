/**
 * Brixel FactoryOS — Sustainability Create Waste
 * Record waste entry.
 */

import { Head, Link, router, usePage, useForm } from "@inertiajs/react";
import { AppLayout } from "@/components/layout";
import { PageHeader } from "@/components/layout";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { Trash2, ArrowLeft } from "lucide-react";
import { cn } from "@/lib/utils";

interface Employee {
  id: number;
  employee_code: string;
  name: string;
}

export default function CreateWasteEntry() {
  const { props: data } = usePage() as any;
  const employees: Employee[] = data?.employees || [];

  const { data: formData, setData, errors, isPending } =
    useForm<{
      entry_date: string;
      waste_type: string;
      quantity_tons: string;
      disposal_method: string;
      notes: string;
    }>({
      entry_date: "",
      waste_type: "",
      quantity_tons: "",
      disposal_method: "",
      notes: "",
    });

  const WASTE_TYPES = [
    { value: "hazardous", label: "Hazardous Waste" },
    { value: "non_hazardous", label: "Non-Hazardous Waste" },
    { value: "recyclable", label: "Recyclable Waste" },
    { value: "organic", label: "Organic Waste" },
    { value: "electronic", label: "Electronic Waste" },
  ];

  const DISPOSAL_METHODS = [
    { value: "landfill", label: "Landfill" },
    { value: "recycling", label: "Recycling" },
    { value: "incineration", label: "Incineration" },
    { value: "composting", label: "Composting" },
    { value: "reuse", label: "Reuse" },
  ];

  const submit = (event: React.FormEvent) => {
    event.preventDefault();
    router.post("/sustainability/waste", formData, {
      onSuccess: () => router.visit("/sustainability/waste"),
    });
  };

  return (
    <AppLayout title="Record Waste Entry">
      <PageHeader
        title="Record Waste Entry"
        subtitle="Record waste entry"
        breadcrumbs={[
          { label: "Sustainability", href: "/sustainability" },
          { label: "Waste Tracking", href: "/sustainability/waste" },
          { label: "Record Waste Entry" },
        ]}
        actions={
          <Link
            href="/sustainability/waste"
            className="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-slate-200 bg-white px-3 text-sm font-medium text-slate-700 shadow-sm transition-colors hover:bg-slate-50"
          >
            <ArrowLeft size={14} /> Back to Waste
          </Link>
        }
      />

      <form onSubmit={submit} className="space-y-6 max-w-2xl">
        <Card>
          <CardHeader>
            <CardTitle className="text-base font-semibold text-slate-900">Waste Entry Details</CardTitle>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Entry Date</label>
                <Input
                  type="date"
                  value={formData.entry_date}
                  onChange={(e) => setData("entry_date", e.currentTarget.value)}
                  className={cn("w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-factory-500 focus:outline-none focus:ring-1 focus:ring-factory-500", errors.entry_date ? "border-rose-500" : "")}
                />
                {errors.entry_date && <p className="text-xs text-rose-600">{errors.entry_date}</p>}
              </div>
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Waste Type</label>
                <Select onValueChange={(value) => setData("waste_type", value)}>
                  <SelectTrigger className="w-full">
                    <SelectValue placeholder={formData.waste_type} />
                  </SelectTrigger>
                  <SelectContent>
                    {WASTE_TYPES.map((t) => (
                      <SelectItem key={t.value} value={t.value}>{t.label}</SelectItem>
                    ))}
                  </SelectContent>
                </Select>
                {errors.waste_type && <p className="text-xs text-rose-600">{errors.waste_type}</p>}
              </div>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Quantity (tons)</label>
                <Input
                  type="number"
                  step="0.01"
                  value={formData.quantity_tons}
                  onChange={(e) => setData("quantity_tons", e.currentTarget.value)}
                  placeholder="0.00"
                  className={cn("w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-factory-500 focus:outline-none focus:ring-1 focus:ring-factory-500", errors.quantity_tons ? "border-rose-500" : "")}
                />
                {errors.quantity_tons && <p className="text-xs text-rose-600">{errors.quantity_tons}</p>}
              </div>
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Disposal Method</label>
                <Select onValueChange={(value) => setData("disposal_method", value)}>
                  <SelectTrigger className="w-full">
                    <SelectValue placeholder={formData.disposal_method} />
                  </SelectTrigger>
                  <SelectContent>
                    {DISPOSAL_METHODS.map((m) => (
                      <SelectItem key={m.value} value={m.value}>{m.label}</SelectItem>
                    ))}
                  </SelectContent>
                </Select>
                {errors.disposal_method && <p className="text-xs text-rose-600">{errors.disposal_method}</p>}
              </div>
            </div>

            <div className="space-y-1">
              <label className="text-sm font-medium text-slate-700">Notes</label>
              <Input
                value={formData.notes}
                onChange={(e) => setData("notes", e.currentTarget.value)}
                placeholder="Notes"
                className={cn("w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-factory-500 focus:outline-none focus:ring-1 focus:ring-factory-500", errors.notes ? "border-rose-500" : "")}
              />
              {errors.notes && <p className="text-xs text-rose-600">{errors.notes}</p>}
            </div>
          </CardContent>
        </Card>

        <div className="flex justify-end">
          <Button type="submit" disabled={isPending}>
            {isPending ? (
              "Recording…"
            ) : (
              <>
                <Trash2 size={16} />
                Record Waste Entry
              </>
            )}
          </Button>
        </div>
      </form>
    </AppLayout>
  );
}
