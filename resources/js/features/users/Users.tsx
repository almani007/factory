/**
 * Brixel FactoryOS — Users
 * System user management, roles, and access control.
 */

import { Head, Link, router, usePage } from "@inertiajs/react";
import { AppLayout } from "@/components/layout";
import { PageHeader } from "@/components/layout";
import { DataTable } from "@/components/DataTable";
import { Plus, Eye, Edit, User } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";

interface SystemUser {
  id: number;
  name: string;
  email: string;
  role: string;
  status: string;
}

export default function Users() {
  const { props: data } = usePage() as any;
  const users: SystemUser[] = data?.users?.data || [];
  const meta = data?.users?.meta || {};

  return (
    <AppLayout title="Users">
      <PageHeader
        title="Users"
        subtitle="System user management, roles, and access control"
        breadcrumbs={[{ label: "Administration", href: "/users" }, { label: "Users" }]}
      >
        <Button onClick={() => router.visit("/users/create")}>
          <Plus size={16} /> New User
        </Button>
      </PageHeader>

      <DataTable
        title="Users"
        columns={[
          { key: "name", label: "Name" },
          { key: "email", label: "Email" },
          { key: "role", label: "Role" },
          { key: "status", label: "Status", format: (value: string) => (
            <Badge className={value === "active" ? "bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-200" : "bg-slate-100 text-slate-500 ring-1 ring-inset ring-slate-200"}>
              {value}
            </Badge>
          )},
        ]}
        data={users}
        meta={meta}
        rowActions={[
          { label: "View", icon: <Eye size={14} />, variant: "ghost" },
          { label: "Edit", icon: <Edit size={14} />, variant: "outline" },
        ]}
      />
    </AppLayout>
  );
}
