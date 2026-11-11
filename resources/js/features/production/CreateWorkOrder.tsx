/**
 * Brixel FactoryOS — Create Work Order
 * Create a new production work order.
 */

import { Head, Link, router, usePage, useForm } from "@inertiajs/react";
import { AppLayout } from "@/components/layout";
import { PageHeader } from "@/components/layout";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { ClipboardList, ArrowLeft } from "lucide-react";
import { cn } from "@/lib/utils";

interface ProductionOrder { id: number; order_number: string; item_name: string; }
interface WorkCentre { id: number; code: string; name: string; }

export default function CreateWorkOrder() {
  const { props: data } = usePage() as any;
  const productionOrders: ProductionOrder[] = data?.production_orders || [];
  const workCentres: WorkCentre[] = data?.work_centres || [];

  const { data: formData, setData, errors, isPending } =
    useForm<{ work_order_number: string; production_order_id: string; work_centre_id: string; planned_start_date: string; planned_end_date: string; quantity: string; status: string; notes: string }>({
      work_order_number: "",
      production_order_id: "",
      work_centre_id: "",
      planned_start_date: "",
      planned_end_date: "",
      quantity: "",
      status: "draft",
      notes: "",
    });

  const submit = (event: React.FormEvent) => {
    event.preventDefault();
    router.post("/work-orders", formData, {
      onSuccess: () => router.visit("/production/work-orders"),
    });
  };

  return (
    <AppLayout title="Create Work Order">
      <PageHeader
        title="Create Work Order"
        subtitle="Create a new production work order"
        breadcrumbs={[{ label: "Production", href: "/production" }, { label: "Work Orders" }, { label: "New Work Order" }]}
      >
        <Link href="/production/work-orders" className="inline-flex h-9 items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 text-sm font-medium text-slate-700 shadow-sm transition-colors hover:bg-slate-50">
          <ArrowLeft size={16} /> Back to Work Orders
        </Link>
      </PageHeader>

      <form onSubmit={submit} className="space-y-6 max-w-2xl">
        <Card>
          <CardHeader>
            <CardTitle className="text-base font-semibold text-slate-900">Work Order Details</CardTitle>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Work Order Number</label>
                <Input
                  value={formData.work_order_number}
                  onChange={(e) => setData("work_order_number", e.currentTarget.value)}
                  placeholder="WO-2024-0001"
                  className={cn("w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-factory-500 focus:outline-none focus:ring-1 focus:ring-factory-500", errors.work_order_number ? "border-rose-500" : "")}
                />
                {errors.work_order_number && <p className="text-xs text-rose-600">{errors.work_order_number}</p>}
              </div>
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Production Order</label>
                <Select onValueChange={(value) => setData("production_order_id", value)}>
                  <SelectTrigger className="w-full">
                    <SelectValue placeholder="Select production order" />
                  </SelectTrigger>
                  <SelectContent>
                    {productionOrders.map((po) => (
                      <SelectItem key={po.id} value={String(po.id)}>{po.order_number} — {po.item_name}</SelectItem>
                    ))}
                  </SelectContent>
                </Select>
                {errors.production_order_id && <p className="text-xs text-rose-600">{errors.production_order_id}</p>}
              </div>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Work Centre</label>
                <Select onValueChange={(value) => setData("work_centre_id", value)}>
                  <SelectTrigger className="w-full">
                    <SelectValue placeholder="Select work centre" />
                  </SelectTrigger>
                  <SelectContent>
                    {workCentres.map((wc) => (
                      <SelectItem key={wc.id} value={String(wc.id)}>{wc.code} — {wc.name}</SelectItem>
                    ))}
                  </SelectContent>
                </Select>
                {errors.work_centre_id && <p className="text-xs text-rose-600">{errors.work_centre_id}</p>}
              </div>
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Status</label>
                <Select onValueChange={(value) => setData("status", value)}>
                  <SelectTrigger className="w-full">
                    <SelectValue placeholder={formData.status} />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="draft">Draft</SelectItem>
                    <SelectItem value="released">Released</SelectItem>
                    <SelectItem value="in_progress">In Progress</SelectItem>
                    <SelectItem value="completed">Completed</SelectItem>
                    <SelectItem value="closed">Closed</SelectItem>
                    <SelectItem value="delayed">Delayed</SelectItem>
                    <SelectItem value="cancelled">Cancelled</SelectItem>
                  </SelectContent>
                </Select>
                {errors.status && <p className="text-xs text-rose-600">{errors.status}</p>}
              </div>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Planned Quantity</label>
                <Input
                  type="number"
                  value={formData.quantity}
                  onChange={(e) => setData("quantity", e.currentTarget.value)}
                  placeholder="0"
                  className="w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-factory-500 focus:outline-none focus:ring-1 focus:ring-factory-500"
                />
              </div>
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Planned Start Date</label>
                <Input
                  type="date"
                  value={formData.planned_start_date}
                  onChange={(e) => setData("planned_start_date", e.currentTarget.value)}
                  className="w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-factory-500 focus:outline-none focus:ring-1 focus:ring-factory-500"
                />
              </div>
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Planned End Date</label>
                <Input
                  type="date"
                  value={formData.planned_end_date}
                  onChange={(e) => setData("planned_end_date", e.currentTarget.value)}
                  className="w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-factory-500 focus:outline-none focus:ring-1 focus:ring-factory-500"
                />
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
            {isPending ? "Creating…" : <><ClipboardList size={16} /> Create Work Order</>}
          </Button>
        </div>
      </form>
    </AppLayout>
  );
}
