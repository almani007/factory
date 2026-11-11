/**
 * Brixel FactoryOS — Create Goods Receipt
 * Record goods received from a supplier against a purchase order.
 */

import { Head, Link, router, usePage, useForm } from "@inertiajs/react";
import { AppLayout } from "@/components/layout";
import { PageHeader } from "@/components/layout";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { Truck, ArrowLeft } from "lucide-react";
import { cn } from "@/lib/utils";

interface PurchaseOrder { id: number; po_number: string; supplier_name: string; }

export default function CreateGoodsReceipt() {
  const { props: data } = usePage() as any;
  const purchaseOrders: PurchaseOrder[] = data?.purchase_orders || [];

  const { data: formData, setData, errors, isPending } =
    useForm<{ receipt_number: string; purchase_order_id: string; receipt_date: string; received_by: string; status: string; notes: string }>({
      receipt_number: "",
      purchase_order_id: "",
      receipt_date: "",
      received_by: "",
      status: "received",
      notes: "",
    });

  const submit = (event: React.FormEvent) => {
    event.preventDefault();
    router.post("/purchase-orders/receive", formData, {
      onSuccess: () => router.visit("/supply-chain/goods-receipts"),
    });
  };

  return (
    <AppLayout title="Create Goods Receipt">
      <PageHeader
        title="Create Goods Receipt"
        subtitle="Record goods received from a supplier"
        breadcrumbs={[{ label: "Supply Chain", href: "/supply-chain/goods-receipts" }, { label: "Goods Receipts" }, { label: "New Receipt" }]}
      >
        <Link href="/supply-chain/goods-receipts" className="inline-flex h-9 items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 text-sm font-medium text-slate-700 shadow-sm transition-colors hover:bg-slate-50">
          <ArrowLeft size={16} /> Back to Goods Receipts
        </Link>
      </PageHeader>

      <form onSubmit={submit} className="space-y-6 max-w-2xl">
        <Card>
          <CardHeader>
            <CardTitle className="text-base font-semibold text-slate-900">Receipt Details</CardTitle>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Receipt Number</label>
                <Input
                  value={formData.receipt_number}
                  onChange={(e) => setData("receipt_number", e.currentTarget.value)}
                  placeholder="GR-2024-0001"
                  className={cn("w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-factory-500 focus:outline-none focus:ring-1 focus:ring-factory-500", errors.receipt_number ? "border-rose-500" : "")}
                />
                {errors.receipt_number && <p className="text-xs text-rose-600">{errors.receipt_number}</p>}
              </div>
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Purchase Order</label>
                <Select onValueChange={(value) => setData("purchase_order_id", value)}>
                  <SelectTrigger className="w-full">
                    <SelectValue placeholder="Select PO" />
                  </SelectTrigger>
                  <SelectContent>
                    {purchaseOrders.map((po) => (
                      <SelectItem key={po.id} value={String(po.id)}>
                        {po.po_number} — {po.supplier_name}
                      </SelectItem>
                    ))}
                  </SelectContent>
                </Select>
                {errors.purchase_order_id && <p className="text-xs text-rose-600">{errors.purchase_order_id}</p>}
              </div>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Receipt Date</label>
                <Input
                  type="date"
                  value={formData.receipt_date}
                  onChange={(e) => setData("receipt_date", e.currentTarget.value)}
                  className={cn("w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-factory-500 focus:outline-none focus:ring-1 focus:ring-factory-500", errors.receipt_date ? "border-rose-500" : "")}
                />
                {errors.receipt_date && <p className="text-xs text-rose-600">{errors.receipt_date}</p>}
              </div>
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Received By</label>
                <Input
                  value={formData.received_by}
                  onChange={(e) => setData("received_by", e.currentTarget.value)}
                  placeholder="Receiver name"
                  className="w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-factory-500 focus:outline-none focus:ring-1 focus:ring-factory-500"
                />
              </div>
            </div>

            <div className="space-y-1">
              <label className="text-sm font-medium text-slate-700">Status</label>
              <Select onValueChange={(value) => setData("status", value)}>
                <SelectTrigger className="w-full">
                  <SelectValue placeholder={formData.status} />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="received">Received</SelectItem>
                  <SelectItem value="partial">Partial</SelectItem>
                  <SelectItem value="pending">Pending</SelectItem>
                </SelectContent>
              </Select>
              {errors.status && <p className="text-xs text-rose-600">{errors.status}</p>}
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
            {isPending ? "Recording…" : <><Truck size={16} /> Record Receipt</>}
          </Button>
        </div>
      </form>
    </AppLayout>
  );
}
