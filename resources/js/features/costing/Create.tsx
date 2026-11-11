/**
 * Brixel FactoryOS — Create Standard Cost
 * Set a standard cost for an item.
 */

import { Head, Link, router, usePage, useForm } from "@inertiajs/react";
import { AppLayout } from "@/components/layout";
import { PageHeader } from "@/components/layout";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { CreditCard, Receipt, ArrowLeft } from "lucide-react";
import { cn } from "@/lib/utils";

interface Item { id: number; name: string; sku: string; }

export default function CreateStandardCost() {
  const { props: data } = usePage() as any;
  const items: Item[] = data?.items || [];

  const { data: formData, setData, errors, isPending } =
    useForm<{ item_id: string; standard_cost: string; costing_method: string; effective_from: string; notes: string }>({
      item_id: "",
      standard_cost: "",
      costing_method: "weighted_average",
      effective_from: "",
      notes: "",
    });

  const submit = (event: React.FormEvent) => {
    event.preventDefault();
    router.post("/costing/standard-costs", formData, {
      onSuccess: () => router.visit("/costing"),
    });
  };

  return (
    <AppLayout title="Set Standard Cost">
      <PageHeader
        title="Set Standard Cost"
        subtitle="Define standard cost for a product item"
        breadcrumbs={[{ label: "Costing", href: "/costing" }, { label: "New Cost" }]}
      >
        <Link href="/costing" className="inline-flex h-9 items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 text-sm font-medium text-slate-700 shadow-sm transition-colors hover:bg-slate-50">
          <ArrowLeft size={16} /> Back to Costing
        </Link>
      </PageHeader>

      <form onSubmit={submit} className="space-y-6 max-w-2xl">
        <Card>
          <CardHeader>
            <CardTitle className="text-base font-semibold text-slate-900">Cost Details</CardTitle>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
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
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Costing Method</label>
                <Select onValueChange={(value) => setData("costing_method", value)}>
                  <SelectTrigger className="w-full">
                    <SelectValue placeholder={formData.costing_method} />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="weighted_average">Weighted Average</SelectItem>
                    <SelectItem value="fifo">FIFO</SelectItem>
                    <SelectItem value="lifo">LIFO</SelectItem>
                    <SelectItem value="standard">Standard Cost</SelectItem>
                  </SelectContent>
                </Select>
                {errors.costing_method && <p className="text-xs text-rose-600">{errors.costing_method}</p>}
              </div>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Standard Cost</label>
                <Input
                  type="number"
                  step="0.01"
                  value={formData.standard_cost}
                  onChange={(e) => setData("standard_cost", e.currentTarget.value)}
                  placeholder="0.00"
                  className={cn("w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-factory-500 focus:outline-none focus:ring-1 focus:ring-factory-500", errors.standard_cost ? "border-rose-500" : "")}
                />
                {errors.standard_cost && <p className="text-xs text-rose-600">{errors.standard_cost}</p>}
              </div>
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Effective From</label>
                <Input
                  type="date"
                  value={formData.effective_from}
                  onChange={(e) => setData("effective_from", e.currentTarget.value)}
                  className={cn("w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-factory-500 focus:outline-none focus:ring-1 focus:ring-factory-500", errors.effective_from ? "border-rose-500" : "")}
                />
                {errors.effective_from && <p className="text-xs text-rose-600">{errors.effective_from}</p>}
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
            {isPending ? "Setting…" : <><CreditCard size={16} /> Set Standard Cost</>}
          </Button>
        </div>
      </form>
    </AppLayout>
  );
}
