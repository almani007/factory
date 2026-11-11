/**
 * Brixel FactoryOS — Create Time Entry
 * Record employee time and attendance.
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

interface Employee { id: number; employee_code: string; first_name: string; last_name: string; }

export default function CreateTimeEntry() {
  const { props: data } = usePage() as any;
  const employees: Employee[] = data?.employees || [];

  const { data: formData, setData, errors, isPending } =
    useForm<{ employee_id: string; entry_date: string; clock_in: string; clock_out: string; status: string }>({
      employee_id: "",
      entry_date: "",
      clock_in: "",
      clock_out: "",
      status: "present",
    });

  const submit = (event: React.FormEvent) => {
    event.preventDefault();
    router.post("/time-entries", formData, {
      onSuccess: () => router.visit("/hr/time-attendance"),
    });
  };

  return (
    <AppLayout title="Create Time Entry">
      <PageHeader
        title="Create Time Entry"
        subtitle="Record employee time and attendance"
        breadcrumbs={[{ label: "HR", href: "/hr" }, { label: "Time & Attendance" }, { label: "New Entry" }]}
      >
        <Link href="/hr/time-attendance" className="inline-flex h-9 items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 text-sm font-medium text-slate-700 shadow-sm transition-colors hover:bg-slate-50">
          <ArrowLeft size={16} /> Back to Time Entries
        </Link>
      </PageHeader>

      <form onSubmit={submit} className="space-y-6 max-w-2xl">
        <Card>
          <CardHeader>
            <CardTitle className="text-base font-semibold text-slate-900">Time Entry Details</CardTitle>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Employee</label>
                <Select onValueChange={(value) => setData("employee_id", value)}>
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
                {errors.employee_id && <p className="text-xs text-rose-600">{errors.employee_id}</p>}
              </div>
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Entry Date</label>
                <Input
                  type="date"
                  value={formData.entry_date}
                  onChange={(e) => setData("entry_date", e.currentTarget.value)}
                  className={cn("w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-factory-500 focus:outline-none focus:ring-1 focus:ring-factory-500", errors.entry_date ? "border-rose-500" : "")}
                />
                {errors.entry_date && <p className="text-xs text-rose-600">{errors.entry_date}</p>}
              </div>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Clock In</label>
                <Input
                  type="time"
                  value={formData.clock_in}
                  onChange={(e) => setData("clock_in", e.currentTarget.value)}
                  className={cn("w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-factory-500 focus:outline-none focus:ring-1 focus:ring-factory-500", errors.clock_in ? "border-rose-500" : "")}
                />
                {errors.clock_in && <p className="text-xs text-rose-600">{errors.clock_in}</p>}
              </div>
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Clock Out</label>
                <Input
                  type="time"
                  value={formData.clock_out}
                  onChange={(e) => setData("clock_out", e.currentTarget.value)}
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
                  <SelectItem value="present">Present</SelectItem>
                  <SelectItem value="absent">Absent</SelectItem>
                  <SelectItem value="late">Late</SelectItem>
                  <SelectItem value="half_day">Half Day</SelectItem>
                </SelectContent>
              </Select>
              {errors.status && <p className="text-xs text-rose-600">{errors.status}</p>}
            </div>
          </CardContent>
        </Card>

        <div className="flex justify-end">
          <Button type="submit" disabled={isPending}>
            {isPending ? "Recording…" : <><Clock size={16} /> Record Entry</>}
          </Button>
        </div>
      </form>
    </AppLayout>
  );
}
