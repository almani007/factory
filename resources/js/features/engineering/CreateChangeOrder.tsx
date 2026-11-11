/**
 * Brixel FactoryOS — Engineering Create Change Order
 * Create a new engineering change order.
 */

import { Head, Link, router, usePage, useForm } from "@inertiajs/react";
import { AppLayout } from "@/components/layout";
import { PageHeader } from "@/components/layout";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { Shield, ArrowLeft } from "lucide-react";
import { cn } from "@/lib/utils";

interface Employee {
  id: number;
  employee_code: string;
  name: string;
}

export default function CreateChangeOrder() {
  const { props: data } = usePage() as any;
  const employees: Employee[] = data?.employees || [];

  const { data: formData, setData, errors, isPending } =
    useForm<{
      change_number: string;
      title: string;
      change_type: string;
      description: string;
      requested_by: string;
      requested_date: string;
      status: string;
    }>({
      change_number: "",
      title: "",
      change_type: "minor",
      description: "",
      requested_by: "",
      requested_date: "",
      status: "draft",
    });

  const CHANGE_TYPES = [
    { value: "minor", label: "Minor Change" },
    { value: "major", label: "Major Change" },
    { value: "emergency", label: "Emergency Change" },
    { value: "standard", label: "Standard Change" },
  ];

  const submit = (event: React.FormEvent) => {
    event.preventDefault();
    router.post("/engineering/change-orders", formData, {
      onSuccess: () => router.visit("/engineering/change-orders"),
    });
  };

  return (
    <AppLayout title="Create Change Order">
      <PageHeader
        title="Create Change Order"
        subtitle="Create a new engineering change order"
        breadcrumbs={[
          { label: "Engineering", href: "/engineering/change-orders" },
          { label: "Change Orders", href: "/engineering/change-orders" },
          { label: "New Change Order" },
        ]}
        actions={
          <Link
            href="/engineering/change-orders"
            className="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-slate-200 bg-white px-3 text-sm font-medium text-slate-700 shadow-sm transition-colors hover:bg-slate-50"
          >
            <ArrowLeft size={14} /> Back to Change Orders
          </Link>
        }
      />

      <form onSubmit={submit} className="space-y-6 max-w-2xl">
        <Card>
          <CardHeader>
            <CardTitle className="text-base font-semibold text-slate-900">Change Order Details</CardTitle>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Change Number</label>
                <Input
                  value={formData.change_number}
                  onChange={(e) => setData("change_number", e.currentTarget.value)}
                  placeholder="ECO-2024-0001"
                  className={cn("w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-factory-500 focus:outline-none focus:ring-1 focus:ring-factory-500", errors.change_number ? "border-rose-500" : "")}
                />
                {errors.change_number && <p className="text-xs text-rose-600">{errors.change_number}</p>}
              </div>
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Title</label>
                <Input
                  value={formData.title}
                  onChange={(e) => setData("title", e.currentTarget.value)}
                  placeholder="Change order title"
                  className={cn("w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-factory-500 focus:outline-none focus:ring-1 focus:ring-factory-500", errors.title ? "border-rose-500" : "")}
                />
                {errors.title && <p className="text-xs text-rose-600">{errors.title}</p>}
              </div>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Change Type</label>
                <Select onValueChange={(value) => setData("change_type", value)}>
                  <SelectTrigger className="w-full">
                    <SelectValue placeholder={formData.change_type} />
                  </SelectTrigger>
                  <SelectContent>
                    {CHANGE_TYPES.map((t) => (
                      <SelectItem key={t.value} value={t.value}>{t.label}</SelectItem>
                    ))}
                  </SelectContent>
                </Select>
                {errors.change_type && <p className="text-xs text-rose-600">{errors.change_type}</p>}
              </div>
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Requested By</label>
                <Select onValueChange={(value) => setData("requested_by", value)}>
                  <SelectTrigger className="w-full">
                    <SelectValue placeholder="Select employee" />
                  </SelectTrigger>
                  <SelectContent>
                    {employees.map((e) => (
                      <SelectItem key={e.id} value={String(e.id)}>{e.employee_code} — {e.name}</SelectItem>
                    ))}
                  </SelectContent>
                </Select>
                {errors.requested_by && <p className="text-xs text-rose-600">{errors.requested_by}</p>}
              </div>
            </div>

            <div className="space-y-1">
              <label className="text-sm font-medium text-slate-700">Description</label>
              <Input
                value={formData.description}
                onChange={(e) => setData("description", e.currentTarget.value)}
                placeholder="Describe the change"
                className={cn("w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-factory-500 focus:outline-none focus:ring-1 focus:ring-factory-500", errors.description ? "border-rose-500" : "")}
              />
              {errors.description && <p className="text-xs text-rose-600">{errors.description}</p>}
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Requested Date</label>
                <Input
                  type="date"
                  value={formData.requested_date}
                  onChange={(e) => setData("requested_date", e.currentTarget.value)}
                  className={cn("w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-factory-500 focus:outline-none focus:ring-1 focus:ring-factory-500", errors.requested_date ? "border-rose-500" : "")}
                />
                {errors.requested_date && <p className="text-xs text-rose-600">{errors.requested_date}</p>}
              </div>
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Status</label>
                <Select onValueChange={(value) => setData("status", value)}>
                  <SelectTrigger className="w-full">
                    <SelectValue placeholder={formData.status} />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="draft">Draft</SelectItem>
                    <SelectItem value="submitted">Submitted</SelectItem>
                    <SelectItem value="under_review">Under Review</SelectItem>
                    <SelectItem value="approved">Approved</SelectItem>
                    <SelectItem value="implemented">Implemented</SelectItem>
                    <SelectItem value="closed">Closed</SelectItem>
                    <SelectItem value="rejected">Rejected</SelectItem>
                  </SelectContent>
                </Select>
                {errors.status && <p className="text-xs text-rose-600">{errors.status}</p>}
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
                <Shield size={16} />
                Create Change Order
              </>
            )}
          </Button>
        </div>
      </form>
    </AppLayout>
  );
}
