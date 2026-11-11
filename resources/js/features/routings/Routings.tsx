/**
 * Brixel FactoryOS — Routings
 * Production routing definitions with operations and work centers.
 */

import { Head, Link, router, usePage } from "@inertiajs/react";
import { AppLayout } from "@/components/layout";
import { PageHeader } from "@/components/layout";
import { DataTable } from "@/components/DataTable";
import { Plus, Eye, Edit, Cpu } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";

interface Routing {
  id: number;
  code: string;
  item_name: string;
  version: string;
  status: string;
  status_label: string;
}

const STATUS_BADGE: Record<string, string> = {
  active: "bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-200",
  draft: "bg-slate-100 text-slate-500 ring-1 ring-inset ring-slate-200",
  obsolete: "bg-rose-50 text-rose-700 ring-1 ring-inset ring-rose-200",
};

export default function Routings() {
  const { props: data } = usePage() as any;
  const routings: Routing[] = data?.routings?.data || [];
  const meta = data?.routings?.meta || {};

  return (
    <AppLayout title="Routings">
      <PageHeader
        title="Routings"
        subtitle="Production routing definitions with operations and work centers"
        breadcrumbs={[{ label: "Manufacturing", href: "/routings" }, { label: "Routings" }]}
      >
        <Button onClick={() => router.visit("/routings/create")}>
          <Plus size={16} /> New Routing
        </Button>
      </PageHeader>

      <DataTable
        title="Routings"
        columns={[
          { key: "code", label: "Routing Code" },
          { key: "item_name", label: "Product" },
          { key: "version", label: "Version" },
          { key: "status", label: "Status", format: (value: string) => (
            <Badge className={STATUS_BADGE[value] || "bg-slate-100 text-slate-600"}>
              {value}
            </Badge>
          )},
        ]}
        data={routings}
        meta={meta}
        rowActions={[
          { label: "View", icon: <Eye size={14} />, variant: "ghost" },
          { label: "Edit", icon: <Edit size={14} />, variant: "outline" },
        ]}
      />
    </AppLayout>
  );
}
