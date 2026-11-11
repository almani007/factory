/**
 * Brixel FactoryOS — Supplier Contracts
 * Supplier contract management and tracking.
 */

import { Head, Link, router, usePage } from "@inertiajs/react";
import { AppLayout } from "@/components/layout";
import { PageHeader } from "@/components/layout";
import { DataTable } from "@/components/DataTable";
import { StatStrip } from "@/components/StatStrip";
import { Plus, Shield, Users } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";

interface Contract {
  id: number;
  contract_number: string;
  title: string;
  supplier_name: string;
  contract_type: string;
  start_date: string;
  end_date: string;
  status: string;
  total_value: number;
}

const STATUS_BADGE: Record<string, string> = {
  draft: "bg-slate-100 text-slate-500 ring-1 ring-inset ring-slate-200",
  active: "bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-200",
  expired: "bg-rose-50 text-rose-700 ring-1 ring-inset ring-rose-200",
  renewed: "bg-indigo-50 text-indigo-700 ring-1 ring-inset ring-indigo-200",
  terminated: "bg-slate-100 text-slate-500 ring-1 ring-inset ring-slate-200",
};

export default function Contracts() {
  const { props: data } = usePage() as any;
  const contracts: Contract[] = data?.contracts?.data || [];
  const meta = data?.contracts?.meta || {};
  const summary = data?.summary || {
    total_contracts: 0,
    active_contracts: 0,
    expiring_soon: 0,
  };

  return (
    <AppLayout title="Supplier Contracts">
      <PageHeader
        title="Supplier Contracts"
        subtitle="Supplier contract management and tracking"
        breadcrumbs={[{ label: "Purchasing", href: "/purchasing" }, { label: "Contracts" }]}
      >
        <Button onClick={() => router.visit("/purchasing/contracts/create")}>
          <Plus size={16} /> New Contract
        </Button>
      </PageHeader>

      <StatStrip
        items={[
          { label: "Total Contracts", value: summary.total_contracts, hint: "All contracts" },
          { label: "Active", value: summary.active_contracts, hint: "Currently active", tone: "success" },
          { label: "Expiring Soon", value: summary.expiring_soon, hint: "Expires within 30 days", tone: summary.expiring_soon > 0 ? "warning" : "success" },
        ]}
      />

      <DataTable
        title="Supplier Contracts"
        columns={[
          { key: "contract_number", label: "Contract #" },
          { key: "title", label: "Title" },
          { key: "supplier_name", label: "Supplier" },
          { key: "contract_type", label: "Type", format: (value: string) => value.replace(/_/g, " ") },
          { key: "start_date", label: "Start Date" },
          { key: "end_date", label: "End Date" },
          { key: "total_value", label: "Value", align: "right", format: (v: number) => `Rs. ${Number(v).toLocaleString(undefined, { maximumFractionDigits: 2 })}` },
          { key: "status", label: "Status", format: (value: string) => (
            <Badge className={STATUS_BADGE[value] || "bg-slate-100 text-slate-600"}>
              {value.replace(/_/g, " ")}
            </Badge>
          )},
        ]}
        data={contracts}
        meta={meta}
        rowActions={[
          { label: "View", icon: <Users size={14} />, variant: "ghost" },
          { label: "Edit", icon: <Shield size={14} />, variant: "outline" },
        ]}
      />
    </AppLayout>
  );
}
