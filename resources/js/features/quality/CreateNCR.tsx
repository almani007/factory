/**
 * Brixel FactoryOS — Create NCR
 * Create a new non-conformance report.
 */

import { Head, Link, router, usePage, useForm } from "@inertiajs/react";
import { AppLayout } from "@/components/layout";
import { PageHeader } from "@/components/layout";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { AlertCircle, ArrowLeft } from "lucide-react";
import { cn } from "@/lib/utils";

interface Item { id: number; sku: string; name: string; }
interface Employee { id: number; employee_code: string; first_name: string; last_name: string; }

export default function CreateNCR() {
  const { props: data } = usePage() as any;
  const items: Item[] = data?.items || [];
  const employees: Employee[] = data?.employees || [];

  const { data: formData, setData, errors, isPending } =
    useForm<{ ncr_number: string; item_id: string; title: string; description: string; severity: string; reported_by: string; reported_date: string; due_date: string }>({
      ncr_number: "",
      item_id: "",
      title: "",
      description: "",
      severity: "minor",
      reported_by: "",
      reported_date: "",
      due_date: "",
    });

  const submit = (event: React.FormEvent) => {
    event.preventDefault();
    router.post("/quality/ncrs", formData, {
      onSuccess: () => router.visit("/quality/non-conformance"),
    });
  };

  return (
    <AppLayout title="Create NCR">
      <PageHeader
        title="Create Non-Conformance Report"
        subtitle="Create a new quality non-conformance report"
        breadcrumbs={[{ label: "Quality", href: "/quality" }, { label: "NCR Reports" }, { label: "New NCR" }]}
      >
        <Link href="/quality/non-conformance" className="inline-flex h-9 items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 text-sm font-medium text-slate-700 shadow-sm transition-colors hover:bg-slate-50">
          <ArrowLeft size={16} /> Back to NCRs
        </Link>
      </PageHeader>

      <form onSubmit={submit} className="space-y-6 max-w-2xl">
        <Card>
          <CardHeader>
            <CardTitle className="text-base font-semibold text-slate-900">NCR Details</CardTitle>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">NCR Number</label>
                <Input
                  value={formData.ncr_number}
                  onChange={(e) => setData("ncr_number", e.currentTarget.value)}
                  placeholder="NCR-2024-0001"
                  className={cn("w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-factory-500 focus:outline-none focus:ring-1 focus:ring-factory-500", errors.ncr_number ? "border-rose-500" : "")}
                />
                {errors.ncr_number && <p className="text-xs text-rose-600">{errors.ncr_number}</p>}
              </div>
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Item</label>
                <Select onValueChange={(value) => setData("item_id", value)}>
                  <SelectTrigger className="w-full">
                    <SelectValue placeholder="Select item" />
                  </SelectTrigger>
                  <SelectContent>
                    {items.map((i) => (
                      <SelectItem key={i.id} value={String(i.id)}>{i.sku} — {i.name}</SelectItem>
                    ))}
                  </SelectContent>
                </Select>
                {errors.item_id && <p className="text-xs text-rose-600">{errors.item_id}</p>}
              </div>
            </div>

            <div className="space-y-1">
              <label className="text-sm font-medium text-slate-700">Title</label>
              <Input
                value={formData.title}
                onChange={(e) => setData("title", e.currentTarget.value)}
                placeholder="Brief description of non-conformance"
                className={cn("w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-factory-500 focus:outline-none focus:ring-1 focus:ring-factory-500", errors.title ? "border-rose-500" : "")}
              />
              {errors.title && <p className="text-xs text-rose-600">{errors.title}</p>}
            </div>

            <div className="space-y-1">
              <label className="text-sm font-medium text-slate-700">Description</label>
              <Input
                value={formData.description}
                onChange={(e) => setData("description", e.currentTarget.value)}
                placeholder="Detailed description"
                className="w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-factory-500 focus:outline-none focus:ring-1 focus:ring-factory-500"
              />
            </div>

            <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Severity</label>
                <Select onValueChange={(value) => setData("severity", value)}>
                  <SelectTrigger className="w-full">
                    <SelectValue placeholder={formData.severity} />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="minor">Minor</SelectItem>
                    <SelectItem value="major">Major</SelectItem>
                    <SelectItem value="critical">Critical</SelectItem>
                  </SelectContent>
                </Select>
                {errors.severity && <p className="text-xs text-rose-600">{errors.severity}</p>}
              </div>
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Reported By</label>
                <Select onValueChange={(value) => setData("reported_by", value)}>
                  <SelectTrigger className="w-full">
                    <SelectValue placeholder="Select employee" />
                  </SelectTrigger>
                  <SelectContent>
                    {employees.map((e) => (
                      <SelectItem key={e.id} value={String(e.id)}>{e.employee_code} — {e.first_name} {e.last_name}</SelectItem>
                    ))}
                  </SelectContent>
                </Select>
                {errors.reported_by && <p className="text-xs text-rose-600">{errors.reported_by}</p>}
              </div>
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Reported Date</label>
                <Input
                  type="date"
                  value={formData.reported_date}
                  onChange={(e) => setData("reported_date", e.currentTarget.value)}
                  className="w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-factory-500 focus:outline-none focus:ring-1 focus:ring-factory-500"
                />
              </div>
            </div>

            <div className="space-y-1">
              <label className="text-sm font-medium text-slate-700">Due Date</label>
              <Input
                type="date"
                value={formData.due_date}
                onChange={(e) => setData("due_date", e.currentTarget.value)}
                className="w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-factory-500 focus:outline-none focus:ring-1 focus:ring-factory-500"
              />
            </div>
          </CardContent>
        </Card>

        <div className="flex justify-end">
          <Button type="submit" disabled={isPending}>
            {isPending ? "Creating…" : <><AlertCircle size={16} /> Create NCR</>}
          </Button>
        </div>
      </form>
    </AppLayout>
  );
}
