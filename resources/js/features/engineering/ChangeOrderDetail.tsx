/**
 * Brixel FactoryOS — Engineering Change Order Detail
 * View change order details.
 */

import { Head, Link, router, usePage } from "@inertiajs/react";
import { AppLayout } from "@/components/layout";
import { PageHeader } from "@/components/layout";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Shield, ArrowLeft } from "lucide-react";
import { Badge } from "@/components/ui/badge";

interface ChangeOrder {
  id: number;
  change_number: string;
  title: string;
  change_type: string;
  description: string;
  status: string;
  requested_date: string;
  approved_date: string;
  requested_by: string;
}

const STATUS_BADGE: Record<string, string> = {
  draft: "bg-slate-100 text-slate-500",
  submitted: "bg-amber-50 text-amber-700",
  under_review: "bg-sky-50 text-sky-700",
  approved: "bg-emerald-50 text-emerald-700",
  implemented: "bg-indigo-50 text-indigo-700",
  closed: "bg-slate-100 text-slate-500",
  rejected: "bg-rose-50 text-rose-700",
};

export default function ChangeOrderDetail() {
  const { props: data } = usePage() as any;
  const change: ChangeOrder = data?.change;

  return (
    <AppLayout title="Change Order Detail">
      <PageHeader
        title="Change Order #{change?.change_number}"
        subtitle="Change order details"
        breadcrumbs={[
          { label: "Engineering", href: "/engineering/change-orders" },
          { label: "Change Orders", href: "/engineering/change-orders" },
          { label: `Change #${change?.change_number}` },
        ]}
        actions={
          <Link
            href="/engineering/change-orders"
            className="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-slate-200 bg-white px-3 text-sm font-medium text-slate-700 shadow-sm transition-colors hover:bg-slate-50"
          >
            <ArrowLeft size={14} /> Back to Change Orders
          </Link>
        }
      />

      <Card>
        <CardHeader>
          <CardTitle className="text-base font-semibold text-slate-900">Change Order Details</CardTitle>
        </CardHeader>
        <CardContent className="p-5 pt-0 space-y-4">
          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div className="space-y-1">
              <label className="text-sm font-medium text-slate-700">Change Number</label>
              <p className="text-sm font-medium text-slate-900">{change?.change_number}</p>
            </div>
            <div className="space-y-1">
              <label className="text-sm font-medium text-slate-700">Title</label>
              <p className="text-sm font-medium text-slate-900">{change?.title}</p>
            </div>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div className="space-y-1">
              <label className="text-sm font-medium text-slate-700">Change Type</label>
              <p className="text-sm font-medium text-slate-900">{change?.change_type?.replace(/_/g, " ")}</p>
            </div>
            <div className="space-y-1">
              <label className="text-sm font-medium text-slate-700">Status</label>
              <Badge className={STATUS_BADGE[change?.status || ""] || "bg-slate-100 text-slate-600"}>
                {change?.status?.replace(/_/g, " ")}
              </Badge>
            </div>
          </div>

          <div className="space-y-1">
            <label className="text-sm font-medium text-slate-700">Requested By</label>
            <p className="text-sm font-medium text-slate-900">{change?.requested_by}</p>
          </div>

          <div className="space-y-1">
            <label className="text-sm font-medium text-slate-700">Requested Date</label>
            <p className="text-sm font-medium text-slate-900">{change?.requested_date}</p>
          </div>

          <div className="space-y-1">
            <label className="text-sm font-medium text-slate-700">Approved Date</label>
            <p className="text-sm font-medium text-slate-900">{change?.approved_date || "Not yet approved"}</p>
          </div>

          <div className="space-y-1">
            <label className="text-sm font-medium text-slate-700">Description</label>
            <p className="text-sm text-slate-600">{change?.description}</p>
          </div>
        </CardContent>
      </Card>
    </AppLayout>
  );
}
