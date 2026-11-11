/**
 * Brixel FactoryOS — Create Customer Invoice
 * Create a new customer invoice for billing.
 */

import { Head, Link, router, usePage, useForm } from "@inertiajs/react";
import { AppLayout } from "@/components/layout";
import { PageHeader } from "@/components/layout";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { Banknote, ArrowLeft } from "lucide-react";
import { cn } from "@/lib/utils";

interface Customer { id: number; name: string; code: string; }
interface Item { id: number; sku: string; name: string; default_sales_price: number; }

export default function CreateCustomerInvoice() {
  const { props: data } = usePage() as any;
  const customers: Customer[] = data?.customers || [];
  const items: Item[] = data?.items || [];

  const { data: formData, setData, errors, isPending } =
    useForm<{ invoice_number: string; customer_id: string; invoice_date: string; due_date: string; status: string; notes: string }>({
      invoice_number: "",
      customer_id: "",
      invoice_date: "",
      due_date: "",
      status: "unpaid",
      notes: "",
    });

  const submit = (event: React.FormEvent) => {
    event.preventDefault();
    router.post("/customer-invoices", formData, {
      onSuccess: () => router.visit("/sales/invoices"),
    });
  };

  return (
    <AppLayout title="Create Customer Invoice">
      <PageHeader
        title="Create Customer Invoice"
        subtitle="Create a new customer invoice for billing"
        breadcrumbs={[{ label: "Sales", href: "/sales" }, { label: "Invoices" }, { label: "New Invoice" }]}
      >
        <Link href="/sales/invoices" className="inline-flex h-9 items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 text-sm font-medium text-slate-700 shadow-sm transition-colors hover:bg-slate-50">
          <ArrowLeft size={16} /> Back to Invoices
        </Link>
      </PageHeader>

      <form onSubmit={submit} className="space-y-6 max-w-2xl">
        <Card>
          <CardHeader>
            <CardTitle className="text-base font-semibold text-slate-900">Invoice Details</CardTitle>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Invoice Number</label>
                <Input
                  value={formData.invoice_number}
                  onChange={(e) => setData("invoice_number", e.currentTarget.value)}
                  placeholder="INV-2024-0001"
                  className={cn("w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-factory-500 focus:outline-none focus:ring-1 focus:ring-factory-500", errors.invoice_number ? "border-rose-500" : "")}
                />
                {errors.invoice_number && <p className="text-xs text-rose-600">{errors.invoice_number}</p>}
              </div>
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Customer</label>
                <Select onValueChange={(value) => setData("customer_id", value)}>
                  <SelectTrigger className="w-full">
                    <SelectValue placeholder="Select customer" />
                  </SelectTrigger>
                  <SelectContent>
                    {customers.map((c) => (
                      <SelectItem key={c.id} value={String(c.id)}>{c.code} — {c.name}</SelectItem>
                    ))}
                  </SelectContent>
                </Select>
                {errors.customer_id && <p className="text-xs text-rose-600">{errors.customer_id}</p>}
              </div>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Invoice Date</label>
                <Input
                  type="date"
                  value={formData.invoice_date}
                  onChange={(e) => setData("invoice_date", e.currentTarget.value)}
                  className={cn("w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-factory-500 focus:outline-none focus:ring-1 focus:ring-factory-500", errors.invoice_date ? "border-rose-500" : "")}
                />
                {errors.invoice_date && <p className="text-xs text-rose-600">{errors.invoice_date}</p>}
              </div>
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Due Date</label>
                <Input
                  type="date"
                  value={formData.due_date}
                  onChange={(e) => setData("due_date", e.currentTarget.value)}
                  className={cn("w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-factory-500 focus:outline-none focus:ring-1 focus:ring-factory-500", errors.due_date ? "border-rose-500" : "")}
                />
                {errors.due_date && <p className="text-xs text-rose-600">{errors.due_date}</p>}
              </div>
            </div>

            <div className="space-y-1">
              <label className="text-sm font-medium text-slate-700">Status</label>
              <Select onValueChange={(value) => setData("status", value)}>
                <SelectTrigger className="w-full">
                  <SelectValue placeholder={formData.status} />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="unpaid">Unpaid</SelectItem>
                  <SelectItem value="paid">Paid</SelectItem>
                  <SelectItem value="overdue">Overdue</SelectItem>
                  <SelectItem value="draft">Draft</SelectItem>
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
            {isPending ? "Creating…" : <><Banknote size={16} /> Create Invoice</>}
          </Button>
        </div>
      </form>
    </AppLayout>
  );
}
