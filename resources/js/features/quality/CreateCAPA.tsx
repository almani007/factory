/**
 * Brixel FactoryOS — Create CAPA
 * Create a new corrective/preventive action.
 */

import { Head, Link, router, usePage, useForm } from "@inertiajs/react";
import { AppLayout } from "@/components/layout";
import { PageHeader } from "@/components/layout";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { ShieldAlert, ArrowLeft } from "lucide-react";
import { cn } from "@/lib/utils";

interface NCR { id: number; ncr_number: string; title: string; }
interface Employee { id: number; employee_code: string; first_name: string; last_name: string; }

export default function CreateCAPA() {
  const { props: data } = usePage() as any;
  const ncrs: NCR[] = data?.ncrs || [];
  const employees: Employee[] = data?.employees || [];

  const { data: formData, setData, errors, isPending } =
    useForm<{ capa_number: string; title: string; ncr_id: string; type: string; description: string; assigned_to: string; due_date: string }>({
      capa_number: "",
      title: "",
      ncr_id: "",
      type: "corrective",
      description: "",
      assigned_to: "",
      due_date: "",
    });

  const submit = (event: React.FormEvent) => {
    event.preventDefault();
    router.post("/quality/capa", formData, {
      onSuccess: () => router.visit("/quality/capa"),
    });
  };

  return (
    <AppLayout title="Create CAPA">
      <PageHeader
        title="Create CAPA"
        subtitle="Create a new corrective/preventive action"
        breadcrumbs={[{ label: "Quality", href: "/quality" }, { label: "CAPA" }, { label: "New CAPA" }]}
      >
        <Link href="/quality/capa" className="inline-flex h-9 items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 text-sm font-medium text-slate-700 shadow-sm transition-colors hover:bg-slate-50">
          <ArrowLeft size={16} /> Back to CAPA
        </Link>
      </PageHeader>

      <form onSubmit={submit} className="space-y-6 max-w-2xl">
        <Card>
          <CardHeader>
            <CardTitle className="text-base font-semibold text-slate-900">CAPA Details</CardTitle>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">CAPA Number</label>
                <Input
                  value={formData.capa_number}
                  onChange={(e) => setData("capa_number", e.currentTarget.value)}
                  placeholder="CAPA-2024-0001"
                  className={cn("w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-factory-500 focus:outline-none focus:ring-1 focus:ring-factory-500", errors.capa_number ? "border-rose-500" : "")}
                />
                {errors.capa_number && <p className="text-xs text-rose-600">{errors.capa_number}</p>}
              </div>
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Related NCR</label>
                <Select onValueChange={(value) => setData("ncr_id", value)}>
                  <SelectTrigger className="w-full">
                    <SelectValue placeholder="Select NCR (optional)" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="">None</SelectItem>
                    {ncrs.map((n) => (
                      <SelectItem key={n.id} value={String(n.id)}>{n.ncr_number} — {n.title}</SelectItem>
                    ))}
                  </SelectContent>
                </Select>
              </div>
            </div>

            <div className="space-y-1">
              <label className="text-sm font-medium text-slate-700">Title</label>
              <Input
                value={formData.title}
                onChange={(e) => setData("title", e.currentTarget.value)}
                placeholder="CAPA title"
                className={cn("w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-factory-500 focus:outline-none focus:ring-1 focus:ring-factory-500", errors.title ? "border-rose-500" : "")}
              />
              {errors.title && <p className="text-xs text-rose-600">{errors.title}</p>}
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Type</label>
                <Select onValueChange={(value) => setData("type", value)}>
                  <SelectTrigger className="w-full">
                    <SelectValue placeholder={formData.type} />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="corrective">Corrective Action</SelectItem>
                    <SelectItem value="preventive">Preventive Action</SelectItem>
                  </SelectContent>
                </Select>
                {errors.type && <p className="text-xs text-rose-600">{errors.type}</p>}
              </div>
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Assigned To</label>
                <Select onValueChange={(value) => setData("assigned_to", value)}>
                  <SelectTrigger className="w-full">
                    <SelectValue placeholder="Select employee" />
                  </SelectTrigger>
                  <SelectContent>
                    {employees.map((e) => (
                      <SelectItem key={e.id} value={String(e.id)}>{e.employee_code} — {e.first_name} {e.last_name}</SelectItem>
                    ))}
                  </SelectContent>
                </Select>
                {errors.assigned_to && <p className="text-xs text-rose-600">{errors.assigned_to}</p>}
              </div>
            </div>

            <div className="space-y-1">
              <label className="text-sm font-medium text-slate-700">Description</label>
              <Input
                value={formData.description}
                onChange={(e) => setData("description", e.currentTarget.value)}
                placeholder="Action description and plan"
                className="w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-factory-500 focus:outline-none focus:ring-1 focus:ring-factory-500"
              />
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
            {isPending ? "Creating…" : <><ShieldAlert size={16} /> Create CAPA</>}
          </Button>
        </div>
      </form>
    </AppLayout>
  );
}
