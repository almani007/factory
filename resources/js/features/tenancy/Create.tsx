/**
 * Brixel FactoryOS — Onboard Tenant
 * Create a new tenant workspace.
 */

import { Head, Link, router, useForm } from "@inertiajs/react";
import { AppLayout } from "@/components/layout";
import { PageHeader } from "@/components/layout";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { Settings, Plus, ArrowLeft } from "lucide-react";
import { cn } from "@/lib/utils";

export default function CreateTenant() {
  const { data: formData, setData, errors, isPending } =
    useForm<{ name: string; slug: string; email: string; plan: string; status: string }>({
      name: "",
      slug: "",
      email: "",
      plan: "standard",
      status: "active",
    });

  const submit = (event: React.FormEvent) => {
    event.preventDefault();
    router.post("/tenants", formData, {
      onSuccess: () => router.visit("/tenancy"),
    });
  };

  return (
    <AppLayout title="Onboard Tenant">
      <PageHeader
        title="Onboard Tenant"
        subtitle="Create a new tenant workspace"
        breadcrumbs={[{ label: "Tenancy", href: "/tenancy" }, { label: "New Tenant" }]}
      >
        <Link href="/tenancy" className="inline-flex h-9 items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 text-sm font-medium text-slate-700 shadow-sm transition-colors hover:bg-slate-50">
          <ArrowLeft size={16} /> Back to Tenants
        </Link>
      </PageHeader>

      <form onSubmit={submit} className="space-y-6 max-w-2xl">
        <Card>
          <CardHeader>
            <CardTitle className="text-base font-semibold text-slate-900">Tenant Details</CardTitle>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Tenant Name</label>
                <Input
                  value={formData.name}
                  onChange={(e) => setData("name", e.currentTarget.value)}
                  placeholder="Acme Manufacturing"
                  className={cn("w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-factory-500 focus:outline-none focus:ring-1 focus:ring-factory-500", errors.name ? "border-rose-500" : "")}
                />
                {errors.name && <p className="text-xs text-rose-600">{errors.name}</p>}
              </div>
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Slug</label>
                <Input
                  value={formData.slug}
                  onChange={(e) => setData("slug", e.currentTarget.value)}
                  placeholder="acme-mfg"
                  className={cn("w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-factory-500 focus:outline-none focus:ring-1 focus:ring-factory-500", errors.slug ? "border-rose-500" : "")}
                />
                {errors.slug && <p className="text-xs text-rose-600">{errors.slug}</p>}
              </div>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Email</label>
                <Input
                  type="email"
                  value={formData.email}
                  onChange={(e) => setData("email", e.currentTarget.value)}
                  placeholder="ops@example.com"
                  className={cn("w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-factory-500 focus:outline-none focus:ring-1 focus:ring-factory-500", errors.email ? "border-rose-500" : "")}
                />
                {errors.email && <p className="text-xs text-rose-600">{errors.email}</p>}
              </div>
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Plan</label>
                <Select onValueChange={(value) => setData("plan", value)}>
                  <SelectTrigger className="w-full">
                    <SelectValue placeholder={formData.plan} />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="starter">Starter</SelectItem>
                    <SelectItem value="standard">Standard</SelectItem>
                    <SelectItem value="professional">Professional</SelectItem>
                    <SelectItem value="enterprise">Enterprise</SelectItem>
                  </SelectContent>
                </Select>
                {errors.plan && <p className="text-xs text-rose-600">{errors.plan}</p>}
              </div>
            </div>

            <div className="space-y-1">
              <label className="text-sm font-medium text-slate-700">Status</label>
              <Select onValueChange={(value) => setData("status", value)}>
                <SelectTrigger className="w-full">
                  <SelectValue placeholder={formData.status} />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="active">Active</SelectItem>
                  <SelectItem value="suspended">Suspended</SelectItem>
                </SelectContent>
              </Select>
              {errors.status && <p className="text-xs text-rose-600">{errors.status}</p>}
            </div>
          </CardContent>
        </Card>

        <div className="flex justify-end">
          <Button type="submit" disabled={isPending}>
            {isPending ? "Onboarding…" : <><Plus size={16} /> Onboard Tenant</>}
          </Button>
        </div>
      </form>
    </AppLayout>
  );
}
