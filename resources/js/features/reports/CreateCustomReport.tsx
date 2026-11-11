/**
 * Brixel FactoryOS — Create Custom Report
 * Create a new custom report definition.
 */

import { Head, Link, router, usePage, useForm } from "@inertiajs/react";
import { AppLayout } from "@/components/layout";
import { PageHeader } from "@/components/layout";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { FileType, ArrowLeft } from "lucide-react";
import { cn } from "@/lib/utils";

interface Employee { id: number; employee_code: string; first_name: string; last_name: string; }

export default function CreateCustomReport() {
  const { props: data } = usePage() as any;
  const employees: Employee[] = data?.employees || [];

  const { data: formData, setData, errors, isPending } =
    useForm<{ report_name: string; report_type: string; description: string; frequency: string; status: string; created_by: string; parameters: string }>({
      report_name: "",
      report_type: "operational",
      description: "",
      frequency: "monthly",
      status: "active",
      created_by: "",
      parameters: "",
    });

  const REPORT_TYPES = [
    { value: "operational", label: "Operational" },
    { value: "financial", label: "Financial" },
    { value: "quality", label: "Quality" },
    { value: "inventory", label: "Inventory" },
    { value: "production", label: "Production" },
    { value: "hr", label: "HR" },
  ];

  const FREQUENCIES = [
    { value: "daily", label: "Daily" },
    { value: "weekly", label: "Weekly" },
    { value: "monthly", label: "Monthly" },
    { value: "quarterly", label: "Quarterly" },
    { value: "yearly", label: "Yearly" },
    { value: "on_demand", label: "On Demand" },
  ];

  const submit = (event: React.FormEvent) => {
    event.preventDefault();
    router.post("/custom-reports", formData, {
      onSuccess: () => router.visit("/reports/custom"),
    });
  };

  return (
    <AppLayout title="Create Custom Report">
      <PageHeader
        title="Create Custom Report"
        subtitle="Create a new custom report definition"
        breadcrumbs={[{ label: "Reporting", href: "/reports/custom" }, { label: "Custom Reports" }, { label: "New Report" }]}
      >
        <Link href="/reports/custom" className="inline-flex h-9 items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 text-sm font-medium text-slate-700 shadow-sm transition-colors hover:bg-slate-50">
          <ArrowLeft size={16} /> Back to Custom Reports
        </Link>
      </PageHeader>

      <form onSubmit={submit} className="space-y-6 max-w-2xl">
        <Card>
          <CardHeader>
            <CardTitle className="text-base font-semibold text-slate-900">Report Definition</CardTitle>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Report Name</label>
                <Input
                  value={formData.report_name}
                  onChange={(e) => setData("report_name", e.currentTarget.value)}
                  placeholder="Monthly Production Report"
                  className={cn("w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-factory-500 focus:outline-none focus:ring-1 focus:ring-factory-500", errors.report_name ? "border-rose-500" : "")}
                />
                {errors.report_name && <p className="text-xs text-rose-600">{errors.report_name}</p>}
              </div>
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Report Type</label>
                <Select onValueChange={(value) => setData("report_type", value)}>
                  <SelectTrigger className="w-full">
                    <SelectValue placeholder={formData.report_type} />
                  </SelectTrigger>
                  <SelectContent>
                    {REPORT_TYPES.map((t) => (
                      <SelectItem key={t.value} value={t.value}>{t.label}</SelectItem>
                    ))}
                  </SelectContent>
                </Select>
                {errors.report_type && <p className="text-xs text-rose-600">{errors.report_type}</p>}
              </div>
            </div>

            <div className="space-y-1">
              <label className="text-sm font-medium text-slate-700">Description</label>
              <Input
                value={formData.description}
                onChange={(e) => setData("description", e.currentTarget.value)}
                placeholder="Report description"
                className="w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-factory-500 focus:outline-none focus:ring-1 focus:ring-factory-500"
              />
            </div>

            <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Frequency</label>
                <Select onValueChange={(value) => setData("frequency", value)}>
                  <SelectTrigger className="w-full">
                    <SelectValue placeholder={formData.frequency} />
                  </SelectTrigger>
                  <SelectContent>
                    {FREQUENCIES.map((f) => (
                      <SelectItem key={f.value} value={f.value}>{f.label}</SelectItem>
                    ))}
                  </SelectContent>
                </Select>
                {errors.frequency && <p className="text-xs text-rose-600">{errors.frequency}</p>}
              </div>
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Status</label>
                <Select onValueChange={(value) => setData("status", value)}>
                  <SelectTrigger className="w-full">
                    <SelectValue placeholder={formData.status} />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="active">Active</SelectItem>
                    <SelectItem value="inactive">Inactive</SelectItem>
                  </SelectContent>
                </Select>
                {errors.status && <p className="text-xs text-rose-600">{errors.status}</p>}
              </div>
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Created By</label>
                <Select onValueChange={(value) => setData("created_by", value)}>
                  <SelectTrigger className="w-full">
                    <SelectValue placeholder="Select employee" />
                  </SelectTrigger>
                  <SelectContent>
                    {employees.map((e) => (
                      <SelectItem key={e.id} value={String(e.id)}>{e.employee_code} — {e.first_name} {e.last_name}</SelectItem>
                    ))}
                  </SelectContent>
                </Select>
                {errors.created_by && <p className="text-xs text-rose-600">{errors.created_by}</p>}
              </div>
            </div>

            <div className="space-y-1">
              <label className="text-sm font-medium text-slate-700">Parameters (JSON)</label>
              <Input
                value={formData.parameters}
                onChange={(e) => setData("parameters", e.currentTarget.value)}
                placeholder='{"date_range": "last_month"}'
                className="w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-factory-500 focus:outline-none focus:ring-1 focus:ring-factory-500"
              />
              <p className="text-xs text-slate-400 mt-1">Optional report parameters in JSON format</p>
            </div>
          </CardContent>
        </Card>

        <div className="flex justify-end">
          <Button type="submit" disabled={isPending}>
            {isPending ? "Creating…" : <><FileType size={16} /> Create Report</>}
          </Button>
        </div>
      </form>
    </AppLayout>
  );
}
