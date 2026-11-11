/**
 * Brixel FactoryOS — Engineering Change Orders
 * Engineering change order management.
 */

import { Head, Link, router, usePage } from "@inertiajs/react";
import { AppLayout } from "@/components/layout";
import { PageHeader } from "@/components/layout";
import { DataTable } from "@/components/DataTable";
import { Plus, Shield, Activity } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";

interface ChangeOrder {
  id: number;
  change_number: string;
  title: string;
  change_type: string;
  status: string;
  requested_date: string;
  approved_date: string;
  requested_by: string;
}

const STATUS_BADGE: Record<string, string> = {
  draft: "bg-slate-100 text-slate-500 ring-1 ring-inset ring-slate-200",
  submitted: "bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-200",
  under_review: "bg-sky-50 text-sky-700 ring-1 ring-inset ring-sky-200",
  approved: "bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-200",
  implemented: "bg-indigo-50 text-indigo-700 ring-1 ring-inset ring-indigo-200",
  closed: "bg-slate-100 text-slate-500 ring-1 ring-inset ring-slate-200",
  rejected: "bg-rose-50 text-rose-700 ring-1 ring-inset ring-rose-200",
};

export default function ChangeOrders() {
  const { props: data } = usePage() as any;
  const changes: ChangeOrder[] = data?.changes?.data || [];
  const meta = data?.changes?.meta || {};

  const totalChanges = changes.length;
  const draftChanges = changes.filter((c: ChangeOrder) => c.status === "draft").length;
  const underReviewChanges = changes.filter((c: ChangeOrder) => c.status === "under_review").length;
  const approvedChanges = changes.filter((c: ChangeOrder) => c.status === "approved").length;

  const totalChangesStr = String(totalChanges);
  const draftChangesStr = String(draftChanges);
  const underReviewChangesStr = String(underReviewChanges);
  const approvedChangesStr = String(approvedChanges);

  return (
    <AppLayout title="Change Orders">
      <PageHeader
        title="Engineering Change Orders"
        subtitle="Engineering change order management"
        breadcrumbs={[{ label: "Engineering", href: "/engineering/change-orders" }, { label: "Change Orders" }]}
        actions={
          <Link
            href="/engineering/change-orders/create"
            className="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-factory-50 text-factory-600 font-medium text-sm hover:bg-factory-100 transition-colors"
          >
            <Plus size={14} /> New Change Order
          </Link>
        }
      />

      <div className="mb-6 grid grid-cols-2 gap-4 md:grid-cols-4 lg:grid-cols-4">
        <Card>
          <CardHeader>
            <CardTitle className="text-base font-semibold text-slate-900">Total Change Orders</CardTitle>
          </CardHeader>
          <CardContent className="p-5 pt-0">
            <p className="text-2xl font-semibold text-slate-900">{totalChangesStr}</p>
          </CardContent>
        </Card>
        <Card>
          <CardHeader>
            <CardTitle className="text-base font-semibold text-slate-900">Draft</CardTitle>
          </CardHeader>
          <CardContent className="p-5 pt-0">
            <p className="text-2xl font-semibold text-slate-900">{draftChangesStr}</p>
          </CardContent>
        </Card>
        <Card>
          <CardHeader>
            <CardTitle className="text-base font-semibold text-slate-900">Under Review</CardTitle>
          </CardHeader>
          <CardContent className="p-5 pt-0">
            <p className="text-2xl font-semibold text-slate-900">{underReviewChangesStr}</p>
          </CardContent>
        </Card>
        <Card>
          <CardHeader>
            <CardTitle className="text-base font-semibold text-slate-900">Approved</CardTitle>
          </CardHeader>
          <CardContent className="p-5 pt-0">
            <p className="text-2xl font-semibold text-slate-900">{approvedChangesStr}</p>
          </CardContent>
        </Card>
      </div>

      <DataTable
        title="Change Orders"
        columns={[
          { key: "change_number", label: "Change #" },
          { key: "title", label: "Title" },
          { key: "change_type", label: "Type", format: (value: string) => value.replace(/_/g, " ") },
          { key: "status", label: "Status", format: (value: string) => (
            <Badge className={STATUS_BADGE[value] || "bg-slate-100 text-slate-600"}>
              {value.replace(/_/g, " ")}
            </Badge>
          )},
          { key: "requested_date", label: "Requested Date" },
          { key: "approved_date", label: "Approved Date" },
          { key: "requested_by", label: "Requested By" },
        ]}
        data={changes}
        meta={meta}
        rowActions={[
          { label: "View", icon: <Shield size={14} />, variant: "ghost" },
          { label: "Edit", icon: <Activity size={14} />, variant: "outline" },
        ]}
        emptyMessage="No change orders found."
      />
    </AppLayout>
  );
}
