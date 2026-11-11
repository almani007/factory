/**
 * Brixel FactoryOS — RBAC Users
 * User management with role assignments.
 */

import { Head, Link, router, usePage } from "@inertiajs/react";
import { AppLayout } from "@/components/layout";
import { PageHeader } from "@/components/layout";
import { DataTable } from "@/components/DataTable";
import { Plus, User, Shield, Eye, Edit } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { cn } from "@/lib/utils";

interface SystemUser {
  id: number;
  name: string;
  email: string;
  role_name: string;
  role: string | null;
  status: string;
}

interface RoleOption {
  id: number;
  name: string;
  display_name: string;
}

const STATUS_BADGE: Record<string, string> = {
  active: "bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-200",
  inactive: "bg-slate-100 text-slate-500 ring-1 ring-inset ring-slate-200",
};

export default function UsersWithRoles() {
  const { props: data } = usePage() as any;
  const users: SystemUser[] = data?.users?.data || [];
  const meta = data?.users?.meta || {};
  const availableRoles: RoleOption[] = data?.available_roles || [];

  const [changingRole, setChangingRole] = React.useState<Set<number>>(new Set());

  const changeUserRole = (userId: number, role: string | null) => {
    setChangingRole((prev) => new Set([...prev, userId]));
    router.put(`/rbac/users/${userId}/role`, {
      role: role,
    }, {
      onSuccess: () => {
        setChangingRole((prev) => {
          const next = new Set(prev);
          next.delete(userId);
          return next;
        });
        router.visit("/rbac/users");
      },
      onError: () => {
        setChangingRole((prev) => {
          const next = new Set(prev);
          next.delete(userId);
          return next;
        });
      },
    });
  };

  return (
    <AppLayout title="Users & Roles">
      <PageHeader
        title="Users"
        subtitle="User management with role assignments"
        breadcrumbs={[{ label: "Administration", href: "/rbac/users" }, { label: "Users" }]}
      >
        <Button onClick={() => router.visit("/users/create")}>
          <Plus size={16} /> New User
        </Button>
      </PageHeader>

      <div className="mb-6 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <table className="w-full text-sm">
          <thead>
            <tr className="border-b border-slate-200 bg-slate-50/80">
              <th className="whitespace-nowrap px-5 py-3 text-[10px] font-semibold uppercase tracking-wider text-slate-400 text-left">Name</th>
              <th className="whitespace-nowrap px-5 py-3 text-[10px] font-semibold uppercase tracking-wider text-slate-400 text-left">Email</th>
              <th className="whitespace-nowrap px-5 py-3 text-[10px] font-semibold uppercase tracking-wider text-slate-400 text-left">Role</th>
              <th className="whitespace-nowrap px-5 py-3 text-[10px] font-semibold uppercase tracking-wider text-slate-400 text-left">Status</th>
              <th className="whitespace-nowrap px-5 py-3 text-[10px] font-semibold uppercase tracking-wider text-slate-400 text-center">Actions</th>
            </tr>
          </thead>
          <tbody className="divide-y divide-slate-100">
            {users.length === 0 ? (
              <tr>
                <td colSpan={5} className="px-5 py-14 text-center">
                  <p className="text-sm font-medium text-slate-600">No users found.</p>
                </td>
              </tr>
            ) : (
              users.map((user) => (
                <tr key={user.id} className="transition-colors hover:bg-slate-50/70">
                  <td className="whitespace-nowrap px-5 py-3 text-slate-700 font-medium">{user.name}</td>
                  <td className="whitespace-nowrap px-5 py-3 text-slate-700">{user.email}</td>
                  <td className="whitespace-nowrap px-5 py-3 text-slate-700">
                    <Select
                      value={user.role || ""}
                      onValueChange={(val) => changeUserRole(user.id, val || null)}
                      className="w-full text-sm"
                    >
                      <SelectTrigger className="w-full h-8 text-xs px-2 py-1.5">
                        <SelectValue placeholder={user.role_name || "No Role"} />
                      </SelectTrigger>
                      <SelectContent>
                        <SelectItem value="">No Role</SelectItem>
                        {availableRoles.map((r) => (
                          <SelectItem key={r.id} value={r.name}>{r.display_name || r.name}</SelectItem>
                        ))}
                      </SelectContent>
                    </Select>
                  </td>
                  <td className="whitespace-nowrap px-5 py-3 text-slate-700">
                    <Badge className={STATUS_BADGE[user.status] || "bg-slate-100 text-slate-600"}>
                      {user.status}
                    </Badge>
                  </td>
                  <td className="whitespace-nowrap px-5 py-3 text-center">
                    <div className="flex items-center justify-center gap-1">
                      <Link
                        href={`/users/${user.id}/edit`}
                        className="rounded-md p-1.5 text-slate-600 hover:bg-slate-100 hover:text-slate-900 transition-colors"
                        title="Edit user"
                      >
                        <Edit size={14} />
                      </Link>
                      <Eye size={14} className="rounded-md p-1.5 text-slate-600 hover:bg-slate-100 hover:text-slate-900 transition-colors" />
                    </div>
                  </td>
                </tr>
              ))
            )}
          </tbody>
        </table>
      </div>

      {meta && (
        <div className="mt-4 flex flex-col gap-3 border-t border-slate-200/60 px-5 py-3 text-sm text-slate-500 sm:flex-row sm:items-center sm:justify-between">
          <p>
            Showing <span className="font-medium text-slate-700">{meta.from ?? 0}</span>–{" "}
            <span className="font-medium text-slate-700">{meta.to ?? 0}</span> of{" "}
            <span className="font-medium text-slate-700">{meta.total}</span>
          </p>
          <div className="flex items-center gap-2">
            <Button
              asChild
              href={`/rbac/users?page=${Math.max(1, meta.current_page - 1)}`}
              disabled={meta.current_page <= 1}
              variant="outline"
              size="sm"
            >
              Previous
            </Button>
            <span className="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-medium text-slate-600">
              Page {meta.current_page} of {meta.last_page}
            </span>
            <Button
              asChild
              href={`/rbac/users?page=${Math.min(meta.last_page, meta.current_page + 1)}`}
              disabled={meta.current_page >= meta.last_page}
              variant="outline"
              size="sm"
            >
              Next
            </Button>
          </div>
        </div>
      )}
    </AppLayout>
  );
}
