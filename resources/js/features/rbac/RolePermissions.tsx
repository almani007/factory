/**
 * Brixel FactoryOS — Role Permissions
 * Assign permissions to a role.
 */

import * as React from "react";
import { Head, Link, router, usePage } from "@inertiajs/react";
import { AppLayout } from "@/components/layout";
import { PageHeader } from "@/components/layout";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Shield, ArrowLeft, Save, AlertCircle } from "lucide-react";
import { cn } from "@/lib/utils";

interface Permission {
  id: number;
  name: string;
  category: string;
}

interface Role {
  id: number;
  name: string;
  display_name: string;
  description: string;
  is_system: boolean;
}

export default function RolePermissions() {
  const { props: data } = usePage() as any;
  const role: Role = data?.role;
  const allPermissions: Permission[] = data?.permissions || [];
  const assignedPermissions: number[] = data?.assigned_permissions || [];

  const [selectedPermissions, setSelectedPermissions] = React.useState<number[]>(assignedPermissions);
  const [isSaving, setIsSaving] = React.useState(false);

  // Group permissions by category
  const permissionsByCategory = React.useMemo(() => {
    const grouped: Record<string, Permission[]> = {};
    allPermissions.forEach((p) => {
      if (!grouped[p.category]) grouped[p.category] = [];
      grouped[p.category].push(p);
    });
    return grouped;
  }, [allPermissions]);

  const categories = Object.keys(permissionsByCategory);

  const hasChanges = selectedPermissions.length !== assignedPermissions.length ||
    !selectedPermissions.every((id) => assignedPermissions.includes(id));

  const togglePermission = (id: number) => {
    setSelectedPermissions((prev) => {
      if (prev.includes(id)) {
        return prev.filter((pid) => pid !== id);
      }
      return [...prev, id];
    });
  };

  const isSelected = (id: number) => selectedPermissions.includes(id);

  const savePermissions = () => {
    setIsSaving(true);
    router.put(`/rbac/roles/${role.id}/permissions`, {
      permission_ids: selectedPermissions,
    }, {
      onSuccess: () => {
        setIsSaving(false);
        router.visit("/rbac/roles");
      },
      onError: () => {
        setIsSaving(false);
      },
    });
  };

  const selectAllInCategory = (categoryPermissions: Permission[]) => {
    const ids = categoryPermissions.map((p) => p.id);
    setSelectedPermissions((prev) => {
      const allSelected = ids.every((id) => prev.includes(id));
      if (allSelected) {
        return prev.filter((pid) => !ids.includes(pid));
      }
      return [...new Set([...prev, ...ids])];
    });
  };

  const isCategoryFullySelected = (categoryPermissions: Permission[]) =>
    categoryPermissions.every((p) => selectedPermissions.includes(p.id));

  return (
    <AppLayout title="Role Permissions">
      <PageHeader
        title="Role Permissions"
        subtitle={`Assign permissions to ${role.display_name}`}
        breadcrumbs={[
          { label: "Administration", href: "/rbac/roles" },
          { label: "Roles", href: "/rbac/roles" },
          { label: "Permissions" },
        ]}
        actions={
          <>
            <Button
              onClick={savePermissions}
              disabled={isSaving || !hasChanges}
              variant={hasChanges ? "default" : "outline"}
            >
              {isSaving ? <Save size={14} className="mr-2" /> : <Save size={14} />}
              {isSaving ? "Saving…" : "Save Changes"}
            </Button>
            <Link
              href="/rbac/roles"
              className="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-slate-200 bg-white px-3 text-sm font-medium text-slate-700 shadow-sm transition-colors hover:bg-slate-50"
            >
              <ArrowLeft size={14} /> Back to Roles
            </Link>
          </>
        }
      />

      <div className="max-w-4xl">
        <Card>
          <CardHeader>
            <CardTitle className="text-base font-semibold text-slate-900">{role.display_name}</CardTitle>
            <CardDescription className="text-sm text-slate-500">
              {role.description || "No description"}
            </CardDescription>
            {role.is_system && (
              <div className="mt-3 inline-flex items-center gap-1 rounded-lg bg-amber-50 px-3 py-1 text-xs font-medium text-amber-700">
                <Shield size={12} className="mr-1" />
                System Role — permissions can be customized
              </div>
            )}
          </CardHeader>
          <CardContent className="space-y-6">
            <div className="flex items-center justify-between mb-4">
              <h3 className="text-sm font-semibold text-slate-900">Permissions by Category</h3>
              <span className="text-xs font-medium text-slate-500">
                {selectedPermissions.length} of {allPermissions.length} selected
              </span>
            </div>

            <div className="divide-y divide-slate-100">
              {categories.map((category) => (
                <div key={category} className="p-4">
                  <div className="flex items-center justify-between mb-3">
                    <p className="text-xs font-semibold uppercase tracking-wider text-slate-400">
                      {category.replace(/_/g, " ")}
                    </p>
                    <div className="flex items-center gap-2">
                      <span className={`text-xs font-medium ${isCategoryFullySelected(permissionsByCategory[category]) ? "text-emerald-600" : "text-slate-500"}`}>
                        {selectedPermissions.filter((id) => categoryPermissions.some((p) => p.id === id)).length}/{categoryPermissions.length}
                      </span>
                      <button
                        type="button"
                        onClick={() => selectAllInCategory(permissionsByCategory[category])}
                        className="text-xs font-medium text-factory-600 hover:text-factory-700"
                      >
                        {isCategoryFullySelected(permissionsByCategory[category]) ? "Deselect All" : "Select All"}
                      </button>
                    </div>
                  </div>
                  <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
                    {permissionsByCategory[category].map((perm) => (
                      <label
                        key={perm.id}
                        className={cn(
                          "flex items-center gap-2 rounded-lg border p-2 transition-colors cursor-pointer",
                          isSelected(perm.id)
                            ? "bg-factory-50 border-factory-200"
                            : "bg-white border-slate-200 hover:bg-slate-50"
                        )}
                      >
                        <input
                          type="checkbox"
                          checked={isSelected(perm.id)}
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

        {hasChanges && (
          <Card className="mt-4 border-rose-200 bg-rose-50">
            <CardContent className="p-4">
              <div className="flex items-center gap-2">
                <AlertCircle size={16} className="text-rose-600" />
                <p className="text-sm text-rose-700">
                  You have unsaved permission changes. Click <strong>Save Changes</strong> to apply them.
                </p>
              </div>
            </CardContent>
          </Card>
        )}
      </div>
    </AppLayout>
  );
}
