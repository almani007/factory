/**
 * Brixel FactoryOS — Edit Role
 * Edit an existing role and its permissions.
 */

import * as React from "react";
import { Head, Link, router, usePage, useForm } from "@inertiajs/react";
import { AppLayout } from "@/components/layout";
import { PageHeader } from "@/components/layout";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { User, ArrowLeft, Trash2 } from "lucide-react";
import { cn } from "@/lib/utils";

interface Permission {
  id: number;
  name: string;
  category: string;
}

export default function EditRole() {
  const { props: data } = usePage() as any;
  const role: any = data?.role;
  const permissions: Permission[] = data?.permissions || [];
  const assigned: number[] = data?.assigned || [];

  const { data: formData, setData, errors, isPending, processing } =
    useForm<{ name: string }>({
      name: role?.name || "",
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

  const handleUpdateRole = (event: React.FormEvent) => {
    event.preventDefault();
    router.put(`/rbac/roles/${role.id}`, formData, {
      onSuccess: () => router.visit("/rbac/roles"),
    });
  };

  const handleDeleteRole = () => {
    if (window.confirm(`Are you sure you want to delete the role "${role.display_name}"?`)) {
      router.delete(`/rbac/roles/${role.id}`, {
        onSuccess: () => router.visit("/rbac/roles"),
        onError: () => {},
      });
    }
  };

  return (
    <AppLayout title="Edit Role">
      <PageHeader
        title="Edit Role"
        subtitle={`Manage permissions for ${role.display_name}`}
        breadcrumbs={[
          { label: "Administration", href: "/rbac/roles" },
          { label: "Roles", href: "/rbac/roles" },
          { label: "Edit Role" },
        ]}
        actions={
          <>
            <Button
              onClick={handleDeleteRole}
              variant="destructive"
              size="sm"
              className="text-xs"
            >
              <Trash2 size={14} /> Delete
            </Button>
            <Link
              href={`/rbac/roles/${role.id}/permissions`}
              className="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-factory-50 text-factory-700 font-medium text-sm hover:bg-factory-100 transition-colors"
            >
                <User size={14} /> Manage Permissions
            </Link>
          </>
        }
      />

      <form onSubmit={handleUpdateRole} className="space-y-6 max-w-4xl">
        <Card>
          <CardHeader>
            <CardTitle className="text-base font-semibold text-slate-900">Role Details</CardTitle>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Role Name</label>
                <Input
                  value={formData.name}
                  onChange={(e) => setData("name", e.currentTarget.value)}
                  className={cn("w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-factory-500 focus:outline-none focus:ring-1 focus:ring-factory-500", errors.name ? "border-rose-500" : "")}
                />
                {errors.name && <p className="text-xs text-rose-600">{errors.name}</p>}
              </div>
            </div>
          </CardContent>
        </Card>

        <div className="flex justify-end">
          <Button type="submit" disabled={processing}>
            {processing ? "Updating…" : <><User size={16} /> Update Role</>}
          </Button>
        </div>
      </form>
    </AppLayout>
  );
}
