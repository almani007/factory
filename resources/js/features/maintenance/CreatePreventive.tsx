/**
 * Brixel FactoryOS — Maintenance Create Preventive Maintenance
 * Create a new preventive maintenance schedule.
 */

import { Head, Link, router, usePage, useForm } from "@inertiajs/react";
import { AppLayout } from "@/components/layout";
import { PageHeader } from "@/components/layout";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { RotateCcw, ArrowLeft } from "lucide-react";
import { cn } from "@/lib/utils";

interface Machine {
  id: number;
  machine_code: string;
  name: string;
}

export default function CreatePreventiveMaintenance() {
  const { props: data } = usePage() as any;
  const machines: Machine[] = data?.machines || [];

  const { data: formData, setData, errors, isPending } =
    useForm<{
      title: string;
      machine_id: string;
      schedule_type: string;
      next_due_date: string;
      last_done_date: string;
      status: string;
      assigned_to: string;
    }>({
      title: "",
      machine_id: "",
      schedule_type: "time_based",
      next_due_date: "",
      last_done_date: "",
      status: "scheduled",
      assigned_to: "",
    });

  const submit = (event: React.FormEvent) => {
    event.preventDefault();
    router.post("/preventive-maintenance", formData, {
      onSuccess: () => router.visit("/maintenance/preventive"),
    });
  };

  return (
    <AppLayout title="Create Preventive Maintenance">
      <PageHeader
        title="Create Preventive Maintenance"
        subtitle="Create a new preventive maintenance schedule"
        breadcrumbs={[
          { label: "Maintenance", href: "/maintenance" },
          { label: "Preventive", href: "/maintenance/preventive" },
          { label: "New Schedule" },
        ]}
        actions={
          <Link
            href="/maintenance/preventive"
            className="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-slate-200 bg-white px-3 text-sm font-medium text-slate-700 shadow-sm transition-colors hover:bg-slate-50"
          >
            <ArrowLeft size={14} /> Back to PM Schedules
          </Link>
        }
      />

      <form onSubmit={submit} className="space-y-6 max-w-2xl">
        <Card>
          <CardHeader>
            <CardTitle className="text-base font-semibold text-slate-900">PM Schedule Details</CardTitle>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Title</label>
                <Input
                  value={formData.title}
                  onChange={(e) => setData("title", e.currentTarget.value)}
                  placeholder="Monthly CNC Inspection"
                  className={cn("w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-factory-500 focus:outline-none focus:ring-1 focus:ring-factory-500", errors.title ? "border-rose-500" : "")}
                />
                {errors.title && <p className="text-xs text-rose-600">{errors.title}</p>}
              </div>
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Machine</label>
                <Select onValueChange={(value) => setData("machine_id", value)}>
                  <SelectTrigger className="w-full">
                    <SelectValue placeholder="Select machine" />
                  </SelectTrigger>
                  <SelectContent>
                    {machines.map((m) => (
                      <SelectItem key={m.id} value={String(m.id)}>{m.machine_code} — {m.name}</SelectItem>
                    ))}
                  </SelectContent>
                </Select>
                {errors.machine_id && <p className="text-xs text-rose-600">{errors.machine_id}</p>}
              </div>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Schedule Type</label>
                <Select onValueChange={(value) => setData("schedule_type", value)}>
                  <SelectTrigger className="w-full">
                    <SelectValue placeholder={formData.schedule_type} />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="time_based">Time Based</SelectItem>
                    <SelectItem value="usage_based">Usage Based</SelectItem>
                    <SelectItem value="condition_based">Condition Based</SelectItem>
                  </SelectContent>
                </Select>
                {errors.schedule_type && <p className="text-xs text-rose-600">{errors.schedule_type}</p>}
              </div>
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Next Due Date</label>
                <Input
                  type="date"
                  value={formData.next_due_date}
                  onChange={(e) => setData("next_due_date", e.currentTarget.value)}
                  className={cn("w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-factory-500 focus:outline-none focus:ring-1 focus:ring-factory-500", errors.next_due_date ? "border-rose-500" : "")}
                />
                {errors.next_due_date && <p className="text-xs text-rose-600">{errors.next_due_date}</p>}
              </div>
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Last Done Date</label>
                <Input
                  type="date"
                  value={formData.last_done_date}
                  onChange={(e) => setData("last_done_date", e.currentTarget.value)}
                  className={cn("w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-factory-500 focus:outline-none focus:ring-1 focus:ring-factory-500")}
                />
              </div>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Status</label>
                <Select onValueChange={(value) => setData("status", value)}>
                  <SelectTrigger className="w-full">
                    <SelectValue placeholder={formData.status} />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="scheduled">Scheduled</SelectItem>
                    <SelectItem value="due_soon">Due Soon</SelectItem>
                    <SelectItem value="overdue">Overdue</SelectItem>
                    <SelectItem value="completed">Completed</SelectItem>
                  </SelectContent>
                </Select>
                {errors.status && <p className="text-xs text-rose-600">{errors.status}</p>}
              </div>
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Assigned To</label>
                <Input
                  value={formData.assigned_to}
                  onChange={(e) => setData("assigned_to", e.currentTarget.value)}
                  placeholder="Technician name"
                  className={cn("w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-factory-500 focus:outline-none focus:ring-1 focus:ring-factory-500")}
                />
              </div>
            </div>
          </CardContent>
        </Card>

        <div className="flex justify-end">
          <Button type="submit" disabled={isPending}>
            {isPending ? (
              "Recording…"
            ) : (
              <>
                <RotateCcw size={16} />
                Create Schedule
              </>
            )}
          </Button>
        </div>
      </form>
    </AppLayout>
  );
}
