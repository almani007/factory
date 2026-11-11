/**
 * Brixel FactoryOS — Create Machine
 * Register a new machine or equipment for maintenance tracking.
 */

import { Head, Link, router, useForm } from "@inertiajs/react";
import { AppLayout } from "@/components/layout";
import { PageHeader } from "@/components/layout";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { Wrench, ArrowLeft } from "lucide-react";
import { cn } from "@/lib/utils";

export default function CreateMachine() {
  const { data: formData, setData, errors, isPending } =
    useForm<{ machine_code: string; name: string; type: string; status: string; location: string; purchase_date: string; last_maintenance_date: string }>({
      machine_code: "",
      name: "",
      type: "",
      status: "active",
      location: "",
      purchase_date: "",
      last_maintenance_date: "",
    });

  const MACHINE_TYPES = ["CNC Machine", "Injection Molder", "Press", "Welder", "Conveyor", "Robot", "Other"];
  const MACHINE_STATUSES = ["active", "maintenance", "inactive", "broken"];

  const submit = (event: React.FormEvent) => {
    event.preventDefault();
    router.post("/machines", formData, {
      onSuccess: () => router.visit("/maintenance"),
    });
  };

  return (
    <AppLayout title="Create Machine">
      <PageHeader
        title="Create Machine"
        subtitle="Register a new machine for maintenance tracking"
        breadcrumbs={[{ label: "Maintenance", href: "/maintenance" }, { label: "New Machine" }]}
      >
        <Link href="/maintenance" className="inline-flex h-9 items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 text-sm font-medium text-slate-700 shadow-sm transition-colors hover:bg-slate-50">
          <ArrowLeft size={16} /> Back to Machines
        </Link>
      </PageHeader>

      <form onSubmit={submit} className="space-y-6 max-w-2xl">
        <Card>
          <CardHeader>
            <CardTitle className="text-base font-semibold text-slate-900">Machine Details</CardTitle>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Machine Code</label>
                <Input
                  value={formData.machine_code}
                  onChange={(e) => setData("machine_code", e.currentTarget.value)}
                  placeholder="M-0001"
                  className={cn("w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-factory-500 focus:outline-none focus:ring-1 focus:ring-factory-500", errors.machine_code ? "border-rose-500" : "")}
                />
                {errors.machine_code && <p className="text-xs text-rose-600">{errors.machine_code}</p>}
              </div>
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Machine Name</label>
                <Input
                  value={formData.name}
                  onChange={(e) => setData("name", e.currentTarget.value)}
                  placeholder="CNC Mill"
                  className={cn("w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-factory-500 focus:outline-none focus:ring-1 focus:ring-factory-500", errors.name ? "border-rose-500" : "")}
                />
                {errors.name && <p className="text-xs text-rose-600">{errors.name}</p>}
              </div>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Type</label>
                <Select onValueChange={(value) => setData("type", value)}>
                  <SelectTrigger className="w-full">
                    <SelectValue placeholder={formData.type} />
                  </SelectTrigger>
                  <SelectContent>
                    {MACHINE_TYPES.map((t) => (
                      <SelectItem key={t} value={t}>{t}</SelectItem>
                    ))}
                  </SelectContent>
                </Select>
                {errors.type && <p className="text-xs text-rose-600">{errors.type}</p>}
              </div>
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Status</label>
                <Select onValueChange={(value) => setData("status", value)}>
                  <SelectTrigger className="w-full">
                    <SelectValue placeholder={formData.status} />
                  </SelectTrigger>
                  <SelectContent>
                    {MACHINE_STATUSES.map((s) => (
                      <SelectItem key={s} value={s}>{s.replace(/_/g, " ")}</SelectItem>
                    ))}
                  </SelectContent>
                </Select>
                {errors.status && <p className="text-xs text-rose-600">{errors.status}</p>}
              </div>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Location</label>
                <Input
                  value={formData.location}
                  onChange={(e) => setData("location", e.currentTarget.value)}
                  placeholder="Shop Floor A"
                  className="w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-factory-500 focus:outline-none focus:ring-1 focus:ring-factory-500"
                />
              </div>
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Purchase Date</label>
                <Input
                  type="date"
                  value={formData.purchase_date}
                  onChange={(e) => setData("purchase_date", e.currentTarget.value)}
                  className="w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-factory-500 focus:outline-none focus:ring-1 focus:ring-factory-500"
                />
              </div>
            </div>

            <div className="space-y-1">
              <label className="text-sm font-medium text-slate-700">Last Maintenance Date</label>
              <Input
                type="date"
                value={formData.last_maintenance_date}
                onChange={(e) => setData("last_maintenance_date", e.currentTarget.value)}
                className="w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-factory-500 focus:outline-none focus:ring-1 focus:ring-factory-500"
              />
            </div>
          </CardContent>
        </Card>

        <div className="flex justify-end">
          <Button type="submit" disabled={isPending}>
            {isPending ? "Creating…" : <><Wrench size={16} /> Create Machine</>}
          </Button>
        </div>
      </form>
    </AppLayout>
  );
}
