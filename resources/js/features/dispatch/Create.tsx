/**
 * Brixel FactoryOS — Create Dispatch
 * Create a new dispatch order for delivery.
 */

import { Head, Link, router, usePage, useForm } from "@inertiajs/react";
import { AppLayout } from "@/components/layout";
import { PageHeader } from "@/components/layout";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { Send, Truck, ArrowLeft } from "lucide-react";
import { cn } from "@/lib/utils";

interface Order { id: number; so_number: string; status: string; }

export default function CreateDispatch() {
  const { props: data } = usePage() as any;
  const orders: Order[] = data?.orders || [];

  const { data: formData, setData, errors, isPending } =
    useForm<{ dispatch_number: string; sales_order_id: string; driver_name: string; dispatch_date: string; notes: string }>({
      dispatch_number: "",
      sales_order_id: "",
      driver_name: "",
      dispatch_date: "",
      notes: "",
    });

  const submit = (event: React.FormEvent) => {
    event.preventDefault();
    router.post("/dispatches", formData, {
      onSuccess: () => router.visit("/dispatch"),
    });
  };

  return (
    <AppLayout title="Create Dispatch">
      <PageHeader
        title="Create Dispatch"
        subtitle="Create a new dispatch order for delivery"
        breadcrumbs={[{ label: "Dispatch", href: "/dispatch" }, { label: "New Dispatch" }]}
      >
        <Link href="/dispatch" className="inline-flex h-9 items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 text-sm font-medium text-slate-700 shadow-sm transition-colors hover:bg-slate-50">
          <ArrowLeft size={16} /> Back to Dispatch
        </Link>
      </PageHeader>

      <form onSubmit={submit} className="space-y-6 max-w-2xl">
        <Card>
          <CardHeader>
            <CardTitle className="text-base font-semibold text-slate-900">Dispatch Details</CardTitle>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Dispatch Number</label>
                <Input
                  value={formData.dispatch_number}
                  onChange={(e) => setData("dispatch_number", e.currentTarget.value)}
                  placeholder="DISP-2024-0001"
                  className={cn("w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-factory-500 focus:outline-none focus:ring-1 focus:ring-factory-500", errors.dispatch_number ? "border-rose-500" : "")}
                />
                {errors.dispatch_number && <p className="text-xs text-rose-600">{errors.dispatch_number}</p>}
              </div>
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Sales Order</label>
                <Select onValueChange={(value) => setData("sales_order_id", value)}>
                  <SelectTrigger className="w-full">
                    <SelectValue placeholder="Select order" />
                  </SelectTrigger>
                  <SelectContent>
                    {orders.map((o) => (
                      <SelectItem key={o.id} value={String(o.id)}>
                        {o.so_number} ({o.status})
                      </SelectItem>
                    ))}
                  </SelectContent>
                </Select>
                {errors.sales_order_id && <p className="text-xs text-rose-600">{errors.sales_order_id}</p>}
              </div>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Driver Name</label>
                <Input
                  value={formData.driver_name}
                  onChange={(e) => setData("driver_name", e.currentTarget.value)}
                  placeholder="Driver name"
                  className={cn("w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-factory-500 focus:outline-none focus:ring-1 focus:ring-factory-500", errors.driver_name ? "border-rose-500" : "")}
                />
                {errors.driver_name && <p className="text-xs text-rose-600">{errors.driver_name}</p>}
              </div>
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Dispatch Date</label>
                <Input
                  type="date"
                  value={formData.dispatch_date}
                  onChange={(e) => setData("dispatch_date", e.currentTarget.value)}
                  className={cn("w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-factory-500 focus:outline-none focus:ring-1 focus:ring-factory-500", errors.dispatch_date ? "border-rose-500" : "")}
                />
                {errors.dispatch_date && <p className="text-xs text-rose-600">{errors.dispatch_date}</p>}
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
            {isPending ? "Creating…" : <><Send size={16} /> Create Dispatch</>}
          </Button>
        </div>
      </form>
    </AppLayout>
  );
}
