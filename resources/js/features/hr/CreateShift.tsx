/**
 * Brixel FactoryOS — HR Create Shift
 * Create a new employee shift assignment.
 */

import { Head, Link, router, usePage, useForm } from "@inertiajs/react";
import { AppLayout } from "@/components/layout";
import { PageHeader } from "@/components/layout";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { Clock, ArrowLeft } from "lucide-react";
import { cn } from "@/lib/utils";

interface Employee {
  id: number;
  employee_code: string;
  first_name: string;
  last_name: string;
}

export default function CreateShift() {
  const { props: data } = usePage() as any;
  const employees: Employee[] = data?.employees || [];

  const { data: formData, setData, errors, isPending } =
    useForm<{
      shift_name: string;
      employee_id: string;
      shift_date: string;
      start_time: string;
      end_time: string;
      status: string;
      location: string;
    }>({
      shift_name: "",
      employee_id: "",
      shift_date: "",
      start_time: "",
      end_time: "",
      status: "scheduled",
      location: "",
    });

  const SHIFT_NAMES = [
    { value: "morning", label: "Morning Shift" },
    { value: "afternoon", label: "Afternoon Shift" },
    { value: "night", label: "Night Shift" },
    { value: "weekend", label: "Weekend Shift" },
  ];

  const submit = (event: React.FormEvent) => {
    event.preventDefault();
    router.post("/shifts", formData, {
      onSuccess: () => router.visit("/hr/shifts"),
    });
  };

  return (
    <AppLayout title="Create Shift">
      <PageHeader
        title="Create Shift"
        subtitle="Create a new employee shift assignment"
        breadcrumbs={[
          { label: "HR", href: "/hr" },
          { label: "Shift Management", href: "/hr/shifts" },
          { label: "New Shift" },
        ]}
        actions={
          <Link
            href="/hr/shifts"
            className="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-slate-200 bg-white px-3 text-sm font-medium text-slate-700 shadow-sm transition-colors hover:bg-slate-50"
          >
            <ArrowLeft size={14} /> Back to Shifts
          </Link>
        }
      />

      <form onSubmit={submit} className="space-y-6 max-w-2xl">
        <Card>
          <CardHeader>
            <CardTitle className="text-base font-semibold text-slate-900">Shift Details</CardTitle>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Shift Name</label>
                <Select onValueChange={(value) => setData("shift_name", value)}>
                  <SelectTrigger className="w-full">
                    <SelectValue placeholder={formData.shift_name} />
                  </SelectTrigger>
                  <SelectContent>
                    {SHIFT_NAMES.map((s) => (
                      <SelectItem key={s.value} value={s.value}>{s.label}</SelectItem>
                    ))}
                  </SelectContent>
                </Select>
                {errors.shift_name && <p className="text-xs text-rose-600">{errors.shift_name}</p>}
              </div>
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Employee</label>
                <Select onValueChange={(value) => setData("employee_id", value)}>
                  <SelectTrigger className="w-full">
                    <SelectValue placeholder="Select employee" />
                  </SelectTrigger>
                  <SelectContent>
                    {employees.map((e) => (
                      <SelectItem key={e.id} value={String(e.id)}>{e.employee_code} — {e.first_name} {e.last_name}</SelectItem>
                    ))}
                  </SelectContent>
                </Select>
                {errors.employee_id && <p className="text-xs text-rose-600">{errors.employee_id}</p>}
              </div>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Shift Date</label>
                <Input
                  type="date"
                  value={formData.shift_date}
                  onChange={(e) => setData("shift_date", e.currentTarget.value)}
                  className={cn("w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-factory-500 focus:outline-none focus:ring-1 focus:ring-factory-500", errors.shift_date ? "border-rose-500" : "")}
                />
                {errors.shift_date && <p className="text-xs text-rose-600">{errors.shift_date}</p>}
              </div>
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Start Time</label>
                <Input
                  type="time"
                  value={formData.start_time}
                  onChange={(e) => setData("start_time", e.currentTarget.value)}
                  className={cn("w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-factory-500 focus:outline-none focus:ring-1 focus:ring-factory-500", errors.start_time ? "border-rose-500" : "")}
                />
                {errors.start_time && <p className="text-xs text-rose-600">{errors.start_time}</p>}
              </div>
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">End Time</label>
                <Input
                  type="time"
                  value={formData.end_time}
                  onChange={(e) => setData("end_time", e.currentTarget.value)}
                  className={cn("w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-factory-500 focus:outline-none focus:ring-1 focus:ring-factory-500", errors.end_time ? "border-rose-500" : "")}
                />
                {errors.end_time && <p className="text-xs text-rose-600">{errors.end_time}</p>}
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
                    <SelectItem value="completed">Completed</SelectItem>
                    <SelectItem value="missed">Missed</SelectItem>
                    <SelectItem value="cancelled">Cancelled</SelectItem>
                  </SelectContent>
                </Select>
                {errors.status && <p className="text-xs text-rose-600">{errors.status}</p>}
              </div>
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Location</label>
                <Input
                  value={formData.location}
                  onChange={(e) => setData("location", e.currentTarget.value)}
                  placeholder="Shop Floor A"
                  className={cn("w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-factory-500 focus:outline-none focus:ring-1 focus:ring-factory-500")}
                />
              </div>
            </div>
          </CardContent>
        </Card>

        <div className="flex justify-end">
          <Button type="submit" disabled={isPending}>
            {isPending ? (
              "Creating…"
            ) : (
              <>
                <Clock size={16} />
                Create Shift
              </>
            )}
          </Button>
        </div>
      </form>
    </AppLayout>
  );
}
