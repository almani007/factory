/**
 * Brixel FactoryOS — Create SMS Template
 * Create a new SMS template.
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

export default function CreateSMSTemplate() {
  const { data: formData, setData, errors, isPending } =
    useForm<{
      template_name: string;
      message: string;
      status: string;
    }>({
      template_name: "",
      message: "",
      status: "active",
    });

  const submit = (event: React.FormEvent) => {
    event.preventDefault();
    router.post("/communications/sms-templates", formData, {
      onSuccess: () => router.visit("/communications/sms-templates"),
    });
  };

  return (
    <AppLayout title="Create SMS Template">
      <PageHeader
        title="Create SMS Template"
        subtitle="Create a new SMS template"
        breadcrumbs={[
          { label: "Communications", href: "/communications/sms-templates" },
          { label: "SMS Templates", href: "/communications/sms-templates" },
          { label: "New Template" },
        ]}
        actions={
          <Link
            href="/communications/sms-templates"
            className="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-slate-200 bg-white px-3 text-sm font-medium text-slate-700 shadow-sm transition-colors hover:bg-slate-50"
          >
            <ArrowLeft size={14} /> Back to Templates
          </Link>
        }
      />

      <form onSubmit={submit} className="space-y-6 max-w-2xl">
        <Card>
          <CardHeader>
            <CardTitle className="text-base font-semibold text-slate-900">SMS Template Details</CardTitle>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="space-y-1">
              <label className="text-sm font-medium text-slate-700">Template Name</label>
              <Input
                value={formData.template_name}
                onChange={(e) => setData("template_name", e.currentTarget.value)}
                placeholder="Order Confirmation SMS"
                className={cn("w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-factory-500 focus:outline-none focus:ring-1 focus:ring-factory-500", errors.template_name ? "border-rose-500" : "")}
              />
              {errors.template_name && <p className="text-xs text-rose-600">{errors.template_name}</p>}
            </div>

            <div className="space-y-1">
              <label className="text-sm font-medium text-slate-700">Message</label>
              <Input
                value={formData.message}
                onChange={(e) => setData("message", e.currentTarget.value)}
                rows={6}
                placeholder="Dear {{customer_name}}, your order {{order_number}} has been confirmed."
                className={cn("w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-factory-500 focus:outline-none focus:ring-1 focus:ring-factory-500", errors.message ? "border-rose-500" : "")}
              />
              {errors.message && <p className="text-xs text-rose-600">{errors.message}</p>}
              <p className="text-xs text-slate-400 mt-1">Use {{variable_name}} for template variables.</p>
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
