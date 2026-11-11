/**
 * Brixel FactoryOS — Create Purchase Requisition
 * Create an internal purchase request for approval.
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

interface Employee { id: number; employee_code: string; first_name: string; last_name: string; }

export default function CreatePurchaseRequisition() {
  const { props: data } = usePage() as any;
  const employees: Employee[] = data?.employees || [];

  const { data: formData, setData, errors, isPending } =
    useForm<{ requisition_number: string; requested_by: string; required_date: string; status: string; total_amount: string; notes: string }>({
      requisition_number: "",
      requested_by: "",
      required_date: "",
      status: "pending",
      total_amount: "",
      notes: "",
    });

  const submit = (event: React.FormEvent) => {
    event.preventDefault();
    router.post("/purchase-requisitions", formData, {
      onSuccess: () => router.visit("/supply-chain/purchase-requisitions"),
    });
  };

  return (
    <AppLayout title="Create Purchase Requisition">
      <PageHeader
        title="Create Purchase Requisition"
        subtitle="Create an internal purchase request for approval"
        breadcrumbs={[{ label: "Supply Chain", href: "/supply-chain/purchase-requisitions" }, { label: "Requisitions" }, { label: "New Requisition" }]}
      >
        <Link href="/supply-chain/purchase-requisitions" className="inline-flex h-9 items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 text-sm font-medium text-slate-700 shadow-sm transition-colors hover:bg-slate-50">
          <ArrowLeft size={16} /> Back to Requisitions
        </Link>
      </PageHeader>

      <form onSubmit={submit} className="space-y-6 max-w-2xl">
        <Card>
          <CardHeader>
            <CardTitle className="text-base font-semibold text-slate-900">Requisition Details</CardTitle>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Requisition Number</label>
                <Input
                  value={formData.requisition_number}
                  onChange={(e) => setData("requisition_number", e.currentTarget.value)}
                  placeholder="PR-2024-0001"
                  className={cn("w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-factory-500 focus:outline-none focus:ring-1 focus:ring-factory-500", errors.requisition_number ? "border-rose-500" : "")}
                />
                {errors.requisition_number && <p className="text-xs text-rose-600">{errors.requisition_number}</p>}
              </div>
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Requested By</label>
                <Select onValueChange={(value) => setData("requested_by", value)}>
                  <SelectTrigger className="w-full">
                    <SelectValue placeholder="Select employee" />
                  </SelectTrigger>
                  <SelectContent>
                    {employees.map((e) => (
                      <SelectItem key={e.id} value={String(e.id)}>
                        {e.employee_code} — {e.first_name} {e.last_name}
                      </SelectItem>
                    ))}
                  </SelectContent>
                </Select>
                {errors.requested_by && <p className="text-xs text-rose-600">{errors.requested_by}</p>}
              </div>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Required Date</label>
                <Input
                  type="date"
                  value={formData.required_date}
                  onChange={(e) => setData("required_date", e.currentTarget.value)}
                  className={cn("w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-factory-500 focus:outline-none focus:ring-1 focus:ring-factory-500", errors.required_date ? "border-rose-500" : "")}
                />
                {errors.required_date && <p className="text-xs text-rose-600">{errors.required_date}</p>}
              </div>
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Status</label>
                <Select onValueChange={(value) => setData("status", value)}>
                  <SelectTrigger className="w-full">
                    <SelectValue placeholder={formData.status} />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="pending">Pending</SelectItem>
                    <SelectItem value="approved">Approved</SelectItem>
                    <SelectItem value="rejected">Rejected</SelectItem>
                  </SelectContent>
                </Select>
                {errors.status && <p className="text-xs text-rose-600">{errors.status}</p>}
              </div>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Total Amount</label>
                <Input
                  type="number"
                  step="0.01"
                  value={formData.total_amount}
                  onChange={(e) => setData("total_amount", e.currentTarget.value)}
                  placeholder="0.00"
                  className={cn("w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-factory-500 focus:outline-none focus:ring-1 focus:ring-factory-500", errors.total_amount ? "border-rose-500" : "")}
                />
                {errors.total_amount && <p className="text-xs text-rose-600">{errors.total_amount}</p>}
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
            {isPending ? "Creating…" : <><ClipboardList size={16} /> Create Requisition</>}
          </Button>
        </div>
      </form>
    </AppLayout>
  );
}
