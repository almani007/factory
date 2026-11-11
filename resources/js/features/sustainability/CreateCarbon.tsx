/**
 * Brixel FactoryOS — Sustainability Create Carbon
 * Record carbon emissions entry.
 */

import { Head, Link, router, usePage, useForm } from "@inertiajs/react";
import { AppLayout } from "@/components/layout";
import { PageHeader } from "@/components/layout";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { TreePine, ArrowLeft } from "lucide-react";
import { cn } from "@/lib/utils";

interface Employee {
  id: number;
  employee_code: string;
  name: string;
}

export default function CreateCarbonEntry() {
  const { props: data } = usePage() as any;
  const employees: Employee[] = data?.employees || [];

  const { data: formData, setData, errors, isPending } =
    useForm<{
      entry_date: string;
      scope: string;
      carbon_emissions_tons: string;
      source: string;
      notes: string;
    }>({
      entry_date: "",
      scope: "scope_1",
      carbon_emissions_tons: "",
      source: "",
      notes: "",
    });

  const SCOPES = [
    { value: "scope_1", label: "Scope 1 - Direct" },
    { value: "scope_2", label: "Scope 2 - Indirect Energy" },
    { value: "scope_3", label: "Scope 3 - Value Chain" },
  ];

  const submit = (event: React.FormEvent) => {
    event.preventDefault();
    router.post("/sustainability/carbon", formData, {
      onSuccess: () => router.visit("/sustainability/carbon"),
    });
  };

  return (
    <AppLayout title="Record Carbon Entry">
      <PageHeader
        title="Record Carbon Entry"
        subtitle="Record carbon emissions data"
        breadcrumbs={[
          { label: "Sustainability", href: "/sustainability" },
          { label: "Carbon Tracking", href: "/sustainability/carbon" },
          { label: "Record Carbon Entry" },
        ]}
        actions={
          <Link
            href="/sustainability/carbon"
            className="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-slate-200 bg-white px-3 text-sm font-medium text-slate-700 shadow-sm transition-colors hover:bg-slate-50"
          >
            <ArrowLeft size={14} /> Back to Carbon
          </Link>
        }
      />

      <form onSubmit={submit} className="space-y-6 max-w-2xl">
        <Card>
          <CardHeader>
            <CardTitle className="text-base font-semibold text-slate-900">Carbon Entry Details</CardTitle>
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
                <label className="text-sm font-medium text-slate-700">Scope</label>
                <Select onValueChange={(value) => setData("scope", value)}>
                  <SelectTrigger className="w-full">
                    <SelectValue placeholder={formData.scope} />
                  </SelectTrigger>
                  <SelectContent>
                    {SCOPES.map((s) => (
                      <SelectItem key={s.value} value={s.value}>{s.label}</SelectItem>
                    ))}
                  </SelectContent>
                </Select>
                {errors.scope && <p className="text-xs text-rose-600">{errors.scope}</p>}
              </div>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Carbon Emissions (tons)</label>
                <Input
                  type="number"
                  step="0.01"
                  value={formData.carbon_emissions_tons}
                  onChange={(e) => setData("carbon_emissions_tons", e.currentTarget.value)}
                  placeholder="0.00"
                  className={cn("w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-factory-500 focus:outline-none focus:ring-1 focus:ring-factory-500", errors.carbon_emissions_tons ? "border-rose-500" : "")}
                />
                {errors.carbon_emissions_tons && <p className="text-xs text-rose-600">{errors.carbon_emissions_tons}</p>}
              </div>
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Source</label>
                <Input
                  value={formData.source}
                  onChange={(e) => setData("source", e.currentTarget.value)}
                  placeholder="e.g., Diesel Generator"
                  className={cn("w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-factory-500 focus:outline-none focus:ring-1 focus:ring-factory-500", errors.source ? "border-rose-500" : "")}
                />
                {errors.source && <p className="text-xs text-rose-600">{errors.source}</p>}
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
                <TreePine size={16} />
                Record Carbon Entry
              </>
            )}
          </Button>
        </div>
      </form>
    </AppLayout>
  );
}
