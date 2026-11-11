/**
 * Brixel FactoryOS — Create Email Template
 * Create a new email template.
 */

import { Head, Link, router, usePage, useForm } from "@inertiajs/react";
import { AppLayout } from "@/components/layout";
import { PageHeader } from "@/components/layout";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { FileText, ArrowLeft } from "lucide-react";
import { cn } from "@/lib/utils";

export default function CreateEmailTemplate() {
  const { data: formData, setData, errors, isPending } =
    useForm<{
      template_name: string;
      template_type: string;
      subject: string;
      body: string;
      status: string;
    }>({
      template_name: "",
      template_type: "general",
      subject: "",
      body: "",
      status: "active",
    });

  const TEMPLATE_TYPES = [
    { value: "general", label: "General" },
    { value: "order_confirmation", label: "Order Confirmation" },
    { value: "invoice", label: "Invoice" },
    { value: "shipping", label: "Shipping Notification" },
    { value: "reminder", label: "Reminder" },
    { value: "marketing", label: "Marketing" },
  ];

  const submit = (event: React.FormEvent) => {
    event.preventDefault();
    router.post("/communications/email-templates", formData, {
      onSuccess: () => router.visit("/communications/email-templates"),
    });
  };

  return (
    <AppLayout title="Create Email Template">
      <PageHeader
        title="Create Email Template"
        subtitle="Create a new email template"
        breadcrumbs={[
          { label: "Communications", href: "/communications/email-templates" },
          { label: "Email Templates", href: "/communications/email-templates" },
          { label: "New Template" },
        ]}
        actions={
          <Link
            href="/communications/email-templates"
            className="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-slate-200 bg-white px-3 text-sm font-medium text-slate-700 shadow-sm transition-colors hover:bg-slate-50"
          >
            <ArrowLeft size={14} /> Back to Templates
          </Link>
        }
      />

      <form onSubmit={submit} className="space-y-6 max-w-2xl">
        <Card>
          <CardHeader>
            <CardTitle className="text-base font-semibold text-slate-900">Template Details</CardTitle>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Template Name</label>
                <Input
                  value={formData.template_name}
                  onChange={(e) => setData("template_name", e.currentTarget.value)}
                  placeholder="Order Confirmation Template"
                  className={cn("w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-factory-500 focus:outline-none focus:ring-1 focus:ring-factory-500", errors.template_name ? "border-rose-500" : "")}
                />
                {errors.template_name && <p className="text-xs text-rose-600">{errors.template_name}</p>}
              </div>
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Template Type</label>
                <Select onValueChange={(value) => setData("template_type", value)}>
                  <SelectTrigger className="w-full">
                    <SelectValue placeholder={formData.template_type} />
                  </SelectTrigger>
                  <SelectContent>
                    {TEMPLATE_TYPES.map((t) => (
                      <SelectItem key={t.value} value={t.value}>{t.label}</SelectItem>
                    ))}
                  </SelectContent>
                </Select>
                {errors.template_type && <p className="text-xs text-rose-600">{errors.template_type}</p>}
              </div>
            </div>

            <div className="space-y-1">
              <label className="text-sm font-medium text-slate-700">Subject</label>
              <Input
                value={formData.subject}
                onChange={(e) => setData("subject", e.currentTarget.value)}
                placeholder="Order Confirmation - {{order_number}}"
                className={cn("w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-factory-500 focus:outline-none focus:ring-1 focus:ring-factory-500", errors.subject ? "border-rose-500" : "")}
                />
                {errors.subject && <p className="text-xs text-rose-600">{errors.subject}</p>}
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
              <label className="text-sm font-medium text-slate-700">Body</label>
              <textarea
                value={formData.body}
                onChange={(e) => setData("body", e.currentTarget.value)}
                rows={8}
                placeholder="Dear {{customer_name}},..."
                className={cn("w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-factory-500 focus:outline-none focus:ring-1 focus:ring-factory-500", errors.body ? "border-rose-500" : "")}
              />
              {errors.body && <p className="text-xs text-rose-600">{errors.body}</p>}
              <p className="text-xs text-slate-400 mt-1">Use {{variable_name}} for template variables.</p>
            </div>
          </CardContent>
        </Card>

        <div className="flex justify-end">
          <Button type="submit" disabled={isPending}>
            {isPending ? (
              "Creating…"
            ) : (
              <>
                <FileText size={16} />
                Create Template
              </>
            )}
          </Button>
        </div>
      </form>
    </AppLayout>
  );
}
