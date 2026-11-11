/**
 * Brixel FactoryOS — HR Create Leave Request
 * Submit a new employee leave request.
 */

import { Head, Link, router, usePage, useForm } from "@inertiajs/react";
import { AppLayout } from "@/components/layout";
import { PageHeader } from "@/components/layout";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { Calendar, ArrowLeft } from "lucide-react";
import { cn } from "@/lib/utils";

interface Employee {
  id: number;
  employee_code: string;
  name: string;
}

export default function CreateLeave() {
  const { props: data } = usePage() as any;
  const employees: Employee[] = data?.employees || [];

  const { data: formData, setData, errors, isPending } =
    useForm<{
      leave_number: string;
      employee_id: string;
      leave_type: string;
      start_date: string;
      end_date: string;
      notes: string;
    }>({
      leave_number: "",
      employee_id: "",
      leave_type: "annual",
      start_date: "",
      end_date: "",
      notes: "",
    });

  const LEAVE_TYPES = [
    { value: "annual", label: "Annual Leave" },
    { value: "sick", label: "Sick Leave" },
    { value: "personal", label: "Personal Leave" },
    { value: "maternity", label: "Maternity Leave" },
    { value: "paternity", label: "Paternity Leave" },
    { value: "unpaid", label: "Unpaid Leave" },
  ];

  const submit = (event: React.FormEvent) => {
    event.preventDefault();
    router.post("/leave-requests", formData, {
      onSuccess: () => router.visit("/hr/leave"),
    });
  };

  return (
    <AppLayout title="Create Leave Request">
      <PageHeader
        title="Create Leave Request"
        subtitle="Submit a new employee leave request"
        breadcrumbs={[
          { label: "HR", href: "/hr" },
          { label: "Leave Management", href: "/hr/leave" },
          { label: "New Request" },
        ]}
        actions={
          <Link
            href="/hr/leave"
            className="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-slate-200 bg-white px-3 text-sm font-medium text-slate-700 shadow-sm transition-colors hover:bg-slate-50"
          >
            <ArrowLeft size={14} /> Back to Leave Requests
          </Link>
        }
      />

      <form onSubmit={submit} className="space-y-6 max-w-2xl">
        <Card>
          <CardHeader>
            <CardTitle className="text-base font-semibold text-slate-900">Leave Request Details</CardTitle>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Leave Number</label>
                <Input
                  value={formData.leave_number}
                  onChange={(e) => setData("leave_number", e.currentTarget.value)}
                  placeholder="LV-2024-0001"
                  className={cn("w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-factory-500 focus:outline-none focus:ring-1 focus:ring-factory-500", errors.leave_number ? "border-rose-500" : "")}
                />
                {errors.leave_number && <p className="text-xs text-rose-600">{errors.leave_number}</p>}
              </div>
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Employee</label>
                <Select onValueChange={(value) => setData("employee_id", value)}>
                  <SelectTrigger className="w-full">
                    <SelectValue placeholder="Select employee" />
                  </SelectTrigger>
                  <SelectContent>
                    {employees.map((e) => (
                      <SelectItem key={e.id} value={String(e.id)}>{e.employee_code} — {e.name}</SelectItem>
                    ))}
                  </SelectContent>
                </Select>
                {errors.employee_id && <p className="text-xs text-rose-600">{errors.employee_id}</p>}
              </div>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Leave Type</label>
                <Select onValueChange={(value) => setData("leave_type", value)}>
                  <SelectTrigger className="w-full">
                    <SelectValue placeholder={formData.leave_type} />
                  </SelectTrigger>
                  <SelectContent>
                    {LEAVE_TYPES.map((t) => (
                      <SelectItem key={t.value} value={t.value}>{t.label}</SelectItem>
                    ))}
                  </SelectContent>
                </Select>
                {errors.leave_type && <p className="text-xs text-rose-600">{errors.leave_type}</p>}
              </div>
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Start Date</label>
                <Input
                  type="date"
                  value={formData.start_date}
                  onChange={(e) => setData("start_date", e.currentTarget.value)}
                  className={cn("w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-factory-500 focus:outline-none focus:ring-1 focus:ring-factory-500", errors.start_date ? "border-rose-500" : "")}
                />
                {errors.start_date && <p className="text-xs text-rose-600">{errors.start_date}</p>}
              </div>
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">End Date</label>
                <Input
                  type="date"
                  value={formData.end_date}
                  onChange={(e) => setData("end_date", e.currentTarget.value)}
                  className={cn("w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-factory-500 focus:outline-none focus:ring-1 focus:ring-factory-500", errors.end_date ? "border-rose-500" : "")}
                />
                {errors.end_date && <p className="text-xs text-rose-600">{errors.end_date}</p>}
              </div>
            </div>

            <div className="space-y-1">
              <label className="text-sm font-medium text-slate-700">Notes</label>
              <Input
                value={formData.notes}
                onChange={(e) => setData("notes", e.currentTarget.value)}
                placeholder="Reason for leave"
                className={cn("w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-factory-500 focus:outline-none focus:ring-1 focus:ring-factory-500", errors.notes ? "border-rose-500" : "")}
              />
              {errors.notes && <p className="text-xs text-rose-600">{errors.notes}</p>}
            </div>
          </CardContent>
        </Card>

        <div className="flex justify-end">
          <Button type="submit" disabled={isPending}>
            {isPending ? "Submitting…" : (
              <>
                <Calendar size={16} />
                Submit Request
              </>
            )}
          </Button>
        </div>
      </form>
    </AppLayout>
  );
}
