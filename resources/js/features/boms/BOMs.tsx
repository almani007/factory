/**
 * Brixel FactoryOS — BOMs
 * Bill of Materials definitions for manufactured products.
 */

import { Head, Link, router, usePage } from "@inertiajs/react";
import { AppLayout } from "@/components/layout";
import { PageHeader } from "@/components/layout";
import { DataTable } from "@/components/DataTable";
import { Plus, Eye, Edit, LayoutGrid } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";

interface BOM {
  id: number;
  code: string;
  item_name: string;
  version: string;
  status: string;
  status_label: string;
  effective_from: string;
}

const STATUS_BADGE: Record<string, string> = {
  active: "bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-200",
  draft: "bg-slate-100 text-slate-500 ring-1 ring-inset ring-slate-200",
  obsolete: "bg-rose-50 text-rose-700 ring-1 ring-inset ring-rose-200",
};

export default function BOMs() {
  const { props: data } = usePage() as any;
  const boms: BOM[] = data?.boms?.data || [];
  const meta = data?.boms?.meta || {};

  return (
    <AppLayout title="Bills of Materials">
      <PageHeader
        title="Bills of Materials"
        subtitle="BOM definitions for manufactured products"
        breadcrumbs={[{ label: "Manufacturing", href: "/boms" }, { label: "BOMs" }]}
      >
        <Button onClick={() => router.visit("/boms/create")}>
          <Plus size={16} /> New BOM
        </Button>
      </PageHeader>

      <DataTable
        title="BOMs"
        columns={[
          { key: "code", label: "BOM Code" },
          { key: "item_name", label: "Product" },
          { key: "version", label: "Version" },
          { key: "status", label: "Status", format: (value: string) => (
            <Badge className={STATUS_BADGE[value] || "bg-slate-100 text-slate-600"}>
              {value}
            </Badge>
          )},
          { key: "effective_from", label: "Effective From" },
        ]}
        data={boms}
        meta={meta}
        rowActions={[
          { label: "View", icon: <Eye size={14} />, variant: "ghost" },
          { label: "Edit", icon: <Edit size={14} />, variant: "outline" },
        ]}
      />
    </AppLayout>
  );
}
