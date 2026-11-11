/**
 * Brixel FactoryOS — Energy Create Consumption
 * Record energy consumption data.
 */

import { Head, Link, router, usePage, useForm } from "@inertiajs/react";
import { AppLayout } from "@/components/layout";
import { PageHeader } from "@/components/layout";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Activity, ArrowLeft } from "lucide-react";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { cn } from "@/lib/utils";

interface Meter {
  id: number;
  meter_name: string;
  meter_type: string;
  unit: string;
}

export default function CreateEnergyConsumption() {
  const { props: data } = usePage() as any;
  const meters: Meter[] = data?.meters || [];

  const { data: formData, setData, errors, isPending } =
    useForm<{
      meter_id: string;
      consumption_date: string;
      consumption_value: string;
      unit: string;
      cost: string;
    }>({
      meter_id: "",
      consumption_date: "",
      consumption_value: "",
      unit: "",
      cost: "",
    });

  const submit = (event: React.FormEvent) => {
    event.preventDefault();
    router.post("/energy/consumption", formData, {
      onSuccess: () => router.visit("/energy/consumption"),
    });
  };

  return (
    <AppLayout title="Record Consumption">
      <PageHeader
        title="Record Energy Consumption"
        subtitle="Record energy meter consumption data"
        breadcrumbs={[
          { label: "Energy", href: "/energy" },
          { label: "Consumption", href: "/energy/consumption" },
          { label: "Record Consumption" },
        ]}
        actions={
          <Link
            href="/energy/consumption"
            className="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-slate-200 bg-white px-3 text-sm font-medium text-slate-700 shadow-sm transition-colors hover:bg-slate-50"
          >
            <ArrowLeft size={14} /> Back to Consumption
          </Link>
        }
      />

      <form onSubmit={submit} className="space-y-6 max-w-2xl">
        <Card>
          <CardHeader>
            <CardTitle className="text-base font-semibold text-slate-900">Consumption Details</CardTitle>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Meter</label>
                <Select onValueChange={(value) => setData("meter_id", value)}>
                  <SelectTrigger className="w-full">
                    <SelectValue placeholder="Select meter" />
                  </SelectTrigger>
                  <SelectContent>
                    {meters.map((m) => (
                      <SelectItem key={m.id} value={String(m.id)}>{m.meter_name} ({m.meter_type})</SelectItem>
                    ))}
                  </SelectContent>
                </Select>
                {errors.meter_id && <p className="text-xs text-rose-600">{errors.meter_id}</p>}
              </div>
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Consumption Date</label>
                <Input
                  type="date"
                  value={formData.consumption_date}
                  onChange={(e) => setData("consumption_date", e.currentTarget.value)}
                  className={cn("w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-factory-500 focus:outline-none focus:ring-1 focus:ring-factory-500", errors.consumption_date ? "border-rose-500" : "")}
                />
                {errors.consumption_date && <p className="text-xs text-rose-600">{errors.consumption_date}</p>}
              </div>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Consumption Value</label>
                <Input
                  type="number"
                  step="0.01"
                  value={formData.consumption_value}
                  onChange={(e) => setData("consumption_value", e.currentTarget.value)}
                  placeholder="0.00"
                  className={cn("w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-factory-500 focus:outline-none focus:ring-1 focus:ring-factory-500", errors.consumption_value ? "border-rose-500" : "")}
                />
                {errors.consumption_value && <p className="text-xs text-rose-600">{errors.consumption_value}</p>}
              </div>
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Cost</label>
                <Input
                  type="number"
                  step="0.01"
                  value={formData.cost}
                  onChange={(e) => setData("cost", e.currentTarget.value)}
                  placeholder="0.00"
                  className={cn("w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-factory-500 focus:outline-none focus:ring-1 focus:ring-factory-500", errors.cost ? "border-rose-500" : "")}
                />
                {errors.cost && <p className="text-xs text-rose-600">{errors.cost}</p>}
              </div>
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Unit</label>
                <Input
                  value={formData.unit}
                  onChange={(e) => setData("unit", e.currentTarget.value)}
                  placeholder="kWh"
                  className={cn("w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-factory-500 focus:outline-none focus:ring-1 focus:ring-factory-500", errors.unit ? "border-rose-500" : "")}
                />
                {errors.unit && <p className="text-xs text-rose-600">{errors.unit}</p>}
              </div>
            </div>

            <div className="mt-6 flex items-center justify-between">
              <Button type="submit" disabled={isPending}>
                {isPending ? (
                  "Recording…"
                ) : (
                  <>
                    <Activity size={16} />
                    Record Consumption
                  </>
                )}
              </Button>
            </div>
          </CardContent>
        </Card>
      </form>
    </AppLayout>
  );
}
