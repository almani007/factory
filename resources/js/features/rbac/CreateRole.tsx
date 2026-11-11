/**
 * Brixel FactoryOS — Create Role
 * Create a new role with permission assignments.
 */

import * as React from "react";
import { Head, Link, router, usePage, useForm } from "@inertiajs/react";
import { AppLayout } from "@/components/layout";
import { PageHeader } from "@/components/layout";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { User, ArrowLeft } from "lucide-react";
import { cn } from "@/lib/utils";

interface Permission {
  id: number;
  name: string;
  category: string;
}

export default function CreateRole() {
  const { props: data } = usePage() as any;
  const permissions: Permission[] = data?.permissions || [];

  const { data: formData, setData, errors, isPending } =
    useForm<{ name: string; permission_ids: string[] }>({
      name: "",
      permission_ids: [],
    });

  // Group permissions by category
  const permissionsByCategory = React.useMemo(() => {
    const grouped: Record<string, Permission[]> = {};
    permissions.forEach((p) => {
      if (!grouped[p.category]) grouped[p.category] = [];
      grouped[p.category].push(p);
    });
    return grouped;
  }, [permissions]);

  const categories = Object.keys(permissionsByCategory);

  const togglePermission = (id: number) => {
    setData("permission_ids", (prev) => {
      if (prev.includes(String(id))) {
        return prev.filter((pid) => pid !== String(id));
      }
      return [...prev, String(id)];
    });
  };

  const submit = (event: React.FormEvent) => {
    event.preventDefault();
    router.post("/rbac/roles", formData, {
      onSuccess: () => router.visit("/rbac/roles"),
    });
  };

  return (
    <AppLayout title="Create Role">
      <PageHeader
        title="Create Role"
        subtitle="Create a new role with permission assignments"
        breadcrumbs={[{ label: "Administration", href: "/rbac/roles" }, { label: "Roles" }, { label: "New Role" }]}
      >
        <Link href="/rbac/roles" className="inline-flex h-9 items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 text-sm font-medium text-slate-700 shadow-sm transition-colors hover:bg-slate-50">
          <ArrowLeft size={16} /> Back to Roles
        </Link>
      </PageHeader>

      <form onSubmit={submit} className="space-y-6 max-w-4xl">
        <Card>
          <CardHeader>
            <CardTitle className="text-base font-semibold text-slate-900">Role Details</CardTitle>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="space-y-1">
              <label className="text-sm font-medium text-slate-700">Role Name</label>
              <Input
                value={formData.name}
                onChange={(e) => setData("name", e.currentTarget.value)}
                placeholder="e.g., warehouse_manager"
                className={cn("w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-factory-500 focus:outline-none focus:ring-1 focus:ring-factory-500", errors.name ? "border-rose-500" : "")}
              />
              {errors.name && <p className="text-xs text-rose-600">{errors.name}</p>}
              <p className="text-[10px] text-slate-400 mt-1">Use snake_case, e.g. production_supervisor</p>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardHeader className="pb-4">
            <CardTitle className="text-base font-semibold text-slate-900">Permissions</CardTitle>
          </CardHeader>
          <CardContent className="p-0">
            <div className="divide-y divide-slate-100">
              {categories.map((category) => (
                <div key={category} className="p-4">
                  <p className="mb-3 text-xs font-semibold uppercase tracking-wider text-slate-400">
                    {category.replace(/_/g, " ")}
                  </p>
                  <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                    {permissionsByCategory[category].map((perm) => (
                      <label
                        key={perm.id}
                        className="flex items-center gap-2 cursor-pointer rounded-lg border p-2 hover:bg-slate-50 transition-colors"
                      >
                        <input
                          type="checkbox"
                          checked={formData.permission_ids.includes(String(perm.id))}
                          onChange={() => togglePermission(perm.id)}
                          className="rounded border-slate-300 text-factory-600 focus:ring-factory-500"
                        />
                        <span className="text-sm text-slate-700">{perm.name}</span>
                      </label>
                    ))}
                  </div>
                </div>
              ))}
            </div>
          </CardContent>
        </Card>

        <div className="flex justify-end">
          <Button type="submit" disabled={isPending}>
            {isPending ? "Creating…" : <><User size={16} /> Create Role</>}
          </Button>
        </div>
      </form>
    </AppLayout>
  );
}
