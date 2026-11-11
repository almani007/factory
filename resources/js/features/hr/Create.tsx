/**
 * Brixel FactoryOS — Create Employee
 * Add a new employee to the workforce.
 */

import { Head, Link, router, usePage, useForm } from "@inertiajs/react";
import { AppLayout } from "@/components/layout";
import { PageHeader } from "@/components/layout";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { User, ArrowLeft } from "lucide-react";
import { cn } from "@/lib/utils";

interface Department { id: number; name: string; }

export default function CreateEmployee() {
  const { props: data } = usePage() as any;
  const departments: Department[] = data?.departments || [];

  const { data: formData, setData, errors, isPending } =
    useForm<{ employee_code: string; first_name: string; last_name: string; email: string; phone: string; department_id: string; position: string; hire_date: string; is_active: boolean }>({
      employee_code: "",
      first_name: "",
      last_name: "",
      email: "",
      phone: "",
      department_id: "",
      position: "",
      hire_date: "",
      is_active: true,
    });

  const submit = (event: React.FormEvent) => {
    event.preventDefault();
    router.post("/employees", formData, {
      onSuccess: () => router.visit("/hr"),
    });
  };

  return (
    <AppLayout title="Create Employee">
      <PageHeader
        title="Create Employee"
        subtitle="Add a new employee to the workforce"
        breadcrumbs={[{ label: "HR", href: "/hr" }, { label: "New Employee" }]}
      >
        <Link href="/hr" className="inline-flex h-9 items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 text-sm font-medium text-slate-700 shadow-sm transition-colors hover:bg-slate-50">
          <ArrowLeft size={16} /> Back to Employees
        </Link>
      </PageHeader>

      <form onSubmit={submit} className="space-y-6 max-w-2xl">
        <Card>
          <CardHeader>
            <CardTitle className="text-base font-semibold text-slate-900">Employee Details</CardTitle>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Employee Code</label>
                <Input
                  value={formData.employee_code}
                  onChange={(e) => setData("employee_code", e.currentTarget.value)}
                  placeholder="EMP-0001"
                  className={cn("w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-factory-500 focus:outline-none focus:ring-1 focus:ring-factory-500", errors.employee_code ? "border-rose-500" : "")}
                />
                {errors.employee_code && <p className="text-xs text-rose-600">{errors.employee_code}</p>}
              </div>
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">First Name</label>
                <Input
                  value={formData.first_name}
                  onChange={(e) => setData("first_name", e.currentTarget.value)}
                  placeholder="John"
                  className={cn("w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-factory-500 focus:outline-none focus:ring-1 focus:ring-factory-500", errors.first_name ? "border-rose-500" : "")}
                />
                {errors.first_name && <p className="text-xs text-rose-600">{errors.first_name}</p>}
              </div>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Last Name</label>
                <Input
                  value={formData.last_name}
                  onChange={(e) => setData("last_name", e.currentTarget.value)}
                  placeholder="Doe"
                  className={cn("w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-factory-500 focus:outline-none focus:ring-1 focus:ring-factory-500", errors.last_name ? "border-rose-500" : "")}
                />
                {errors.last_name && <p className="text-xs text-rose-600">{errors.last_name}</p>}
              </div>
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Email</label>
                <Input
                  type="email"
                  value={formData.email}
                  onChange={(e) => setData("email", e.currentTarget.value)}
                  placeholder="john.doe@example.com"
                  className={cn("w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-factory-500 focus:outline-none focus:ring-1 focus:ring-factory-500", errors.email ? "border-rose-500" : "")}
                />
                {errors.email && <p className="text-xs text-rose-600">{errors.email}</p>}
              </div>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Phone</label>
                <Input
                  type="tel"
                  value={formData.phone}
                  onChange={(e) => setData("phone", e.currentTarget.value)}
                  placeholder="+92 300 0000000"
                  className="w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-factory-500 focus:outline-none focus:ring-1 focus:ring-factory-500"
                />
              </div>
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Department</label>
                <Select onValueChange={(value) => setData("department_id", value)}>
                  <SelectTrigger className="w-full">
                    <SelectValue placeholder="Select department" />
                  </SelectTrigger>
                  <SelectContent>
                    {departments.map((d) => (
                      <SelectItem key={d.id} value={String(d.id)}>{d.name}</SelectItem>
                    ))}
                  </SelectContent>
                </Select>
                {errors.department_id && <p className="text-xs text-rose-600">{errors.department_id}</p>}
              </div>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Position</label>
                <Input
                  value={formData.position}
                  onChange={(e) => setData("position", e.currentTarget.value)}
                  placeholder="Production Operator"
                  className="w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-factory-500 focus:outline-none focus:ring-1 focus:ring-factory-500"
                />
              </div>
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Hire Date</label>
                <Input
                  type="date"
                  value={formData.hire_date}
                  onChange={(e) => setData("hire_date", e.currentTarget.value)}
                  className="w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-factory-500 focus:outline-none focus:ring-1 focus:ring-factory-500"
                />
              </div>
            </div>

            <div className="space-y-1">
              <label className="flex items-center gap-2 cursor-pointer">
                <input
                  type="checkbox"
                  checked={formData.is_active}
                  onChange={(e) => setData("is_active", e.currentTarget.checked)}
                  className="rounded border-slate-300 text-factory-600 focus:ring-factory-500"
                />
                <span className="text-sm text-slate-700">Active employee</span>
              </label>
            </div>
          </CardContent>
        </Card>

        <div className="flex justify-end">
          <Button type="submit" disabled={isPending}>
            {isPending ? "Creating…" : <><User size={16} /> Create Employee</>}
          </Button>
        </div>
      </form>
    </AppLayout>
  );
}
