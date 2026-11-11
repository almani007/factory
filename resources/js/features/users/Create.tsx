/**
 * Brixel FactoryOS — Create User
 * Add a new system user with role-based access.
 */

import { Head, Link, router, useForm } from "@inertiajs/react";
import { AppLayout } from "@/components/layout";
import { PageHeader } from "@/components/layout";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { User, ArrowLeft } from "lucide-react";
import { cn } from "@/lib/utils";

export default function CreateUser() {
  const { data: formData, setData, errors, isPending } =
    useForm<{ name: string; email: string; role: string; password: string; password_confirmation: string; is_active: boolean }>({
      name: "",
      email: "",
      role: "operator",
      password: "",
      password_confirmation: "",
      is_active: true,
    });

  const ROLES = [
    { value: "super_admin", label: "Super Admin" },
    { value: "admin", label: "Admin" },
    { value: "manager", label: "Manager" },
    { value: "operator", label: "Operator" },
    { value: "viewer", label: "Viewer" },
  ];

  const submit = (event: React.FormEvent) => {
    event.preventDefault();
    router.post("/users", formData, {
      onSuccess: () => router.visit("/users"),
    });
  };

  return (
    <AppLayout title="Create User">
      <PageHeader
        title="Create User"
        subtitle="Add a new system user with role-based access"
        breadcrumbs={[{ label: "Administration", href: "/users" }, { label: "Users" }, { label: "New User" }]}
      >
        <Link href="/users" className="inline-flex h-9 items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 text-sm font-medium text-slate-700 shadow-sm transition-colors hover:bg-slate-50">
          <ArrowLeft size={16} /> Back to Users
        </Link>
      </PageHeader>

      <form onSubmit={submit} className="space-y-6 max-w-2xl">
        <Card>
          <CardHeader>
            <CardTitle className="text-base font-semibold text-slate-900">User Details</CardTitle>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Full Name</label>
                <Input
                  value={formData.name}
                  onChange={(e) => setData("name", e.currentTarget.value)}
                  placeholder="John Doe"
                  className={cn("w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-factory-500 focus:outline-none focus:ring-1 focus:ring-factory-500", errors.name ? "border-rose-500" : "")}
                />
                {errors.name && <p className="text-xs text-rose-600">{errors.name}</p>}
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
                <label className="text-sm font-medium text-slate-700">Role</label>
                <Select onValueChange={(value) => setData("role", value)}>
                  <SelectTrigger className="w-full">
                    <SelectValue placeholder={formData.role} />
                  </SelectTrigger>
                  <SelectContent>
                    {ROLES.map((r) => (
                      <SelectItem key={r.value} value={r.value}>{r.label}</SelectItem>
                    ))}
                  </SelectContent>
                </Select>
                {errors.role && <p className="text-xs text-rose-600">{errors.role}</p>}
              </div>
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Password</label>
                <Input
                  type="password"
                  value={formData.password}
                  onChange={(e) => setData("password", e.currentTarget.value)}
                  placeholder="••••••••"
                  className={cn("w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-factory-500 focus:outline-none focus:ring-1 focus:ring-factory-500", errors.password ? "border-rose-500" : "")}
                />
                {errors.password && <p className="text-xs text-rose-600">{errors.password}</p>}
              </div>
            </div>

            <div className="space-y-1">
              <label className="text-sm font-medium text-slate-700">Confirm Password</label>
              <Input
                type="password"
                value={formData.password_confirmation}
                onChange={(e) => setData("password_confirmation", e.currentTarget.value)}
                placeholder="••••••••"
                className={cn("w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-factory-500 focus:outline-none focus:ring-1 focus:ring-factory-500", errors.password_confirmation ? "border-rose-500" : "")}
              />
              {errors.password_confirmation && <p className="text-xs text-rose-600">{errors.password_confirmation}</p>}
            </div>

            <div className="space-y-1">
              <label className="flex items-center gap-2 cursor-pointer">
                <input
                  type="checkbox"
                  checked={formData.is_active}
                  onChange={(e) => setData("is_active", e.currentTarget.checked)}
                  className="rounded border-slate-300 text-factory-600 focus:ring-factory-500"
                />
                <span className="text-sm text-slate-700">Active user</span>
              </label>
            </div>
          </CardContent>
        </Card>

        <div className="flex justify-end">
          <Button type="submit" disabled={isPending}>
            {isPending ? "Creating…" : <><User size={16} /> Create User</>}
          </Button>
        </div>
      </form>
    </AppLayout>
  );
}
