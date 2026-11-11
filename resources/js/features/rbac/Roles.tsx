/**
 * Brixel FactoryOS — RBAC Roles
 * Role-based access control role management.
 */

import { Head, Link, router, usePage } from "@inertiajs/react";
import { AppLayout } from "@/components/layout";
import { PageHeader } from "@/components/layout";
import { DataTable } from "@/components/DataTable";
import { Plus, User, Shield, Eye, Edit, Trash2 } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";

interface Role {
  id: number;
  name: string;
  display_name: string;
  description: string;
  is_system: boolean;
  created_at: string;
}

const SYSTEM_BADGE: Record<boolean, string> = {
  true: "bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-200",
  false: "bg-slate-100 text-slate-500 ring-1 ring-inset ring-slate-200",
};

export default function Roles() {
  const { props: data } = usePage() as any;
  const roles: Role[] = data?.roles?.data || [];
  const meta = data?.roles?.meta || {};

  const handleDeleteRole = (id: number, name: string) => {
    if (window.confirm(`Are you sure you want to delete the role "${name}"?`)) {
      router.delete(`/rbac/roles/${id}`, {
        onSuccess: () => router.visit("/rbac/roles"),
        onError: () => {},
      });
    }
  };

  return (
    <AppLayout title="Roles & Permissions">
      <PageHeader
        title="Roles & Permissions"
        subtitle="Role-based access control — manage roles and their permissions"
        breadcrumbs={[{ label: "Administration", href: "/rbac/roles" }, { label: "Roles" }]}
      >
        <Button onClick={() => router.visit("/rbac/roles/create")}>
          <Plus size={16} /> New Role
        </Button>
      </PageHeader>

      <DataTable
        title="Roles"
        columns={[
          { key: "name", label: "Role Name" },
          { key: "display_name", label: "Display Name" },
          { key: "description", label: "Description" },
          { key: "is_system", label: "Type", format: (value: boolean) => (
            <Badge className={SYSTEM_BADGE[value]}>
              {value ? "System" : "Custom"}
            </Badge>
          )},
          { key: "created_at", label: "Created" },
        ]}
        data={roles}
        meta={meta}
        rowActions={[
          {
            label: "View Permissions",
            icon: <Eye size={14} />,
            variant: "ghost",
            href: (row: any) => `/rbac/roles/${row.id}/permissions`,
          },
          {
            label: "Edit",
            icon: <Edit size={14} />,
            variant: "outline",
            href: (row: any) => `/rbac/roles/${row.id}/edit`,
          },
          {
            label: "Delete",
            icon: <Trash2 size={14} />,
            variant: "danger",
            onClick: (row: any) => {
              if (!row.is_system) {
                handleDeleteRole(row.id, row.display_name);
              }
            },
          },
        ]}
      />
    </AppLayout>
  );
}
