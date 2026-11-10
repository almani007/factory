/**
 * Brixel FactoryOS — Tenancy Page
 * Multi-tenant management.
 */

import { Head, Link, usePage } from "@inertiajs/react";
import { AppLayout } from "@/components/layout";
import { PageHeader } from "@/components/layout";
import { DataTable } from "@/components/DataTable";
import { StatStrip } from "@/components/StatStrip";
import { Plus } from "lucide-react";
import { Button } from "@/components/ui/button";

interface Tenant {
  id: number;
  name: string;
  slug: string;
  email: string;
  status: string;
  plan: string;
}

export default function Tenancy() {
  const { props: data } = usePage() as any;
  const tenants: Tenant[] = data?.tenants?.data || [];
  const meta = data?.tenants?.meta || {};
  const summary = data?.tenancy_summary || {
    total: 0,
    active: 0,
    suspended: 0,
  };

  return (
    <AppLayout title="Tenancy Management">
      <PageHeader
        title="Tenancy Management"
        subtitle="Companies, tenants and access isolation"
      >
        <Button>
          <Plus size={16} /> Onboard Tenant
        </Button>
      </PageHeader>

      <StatStrip
        items={[
          { label: "Tenants", value: summary.total, hint: "Provisioned workspaces" },
          { label: "Active", value: summary.active, hint: "Billing normally", tone: "success" },
          { label: "Suspended", value: summary.suspended, hint: "Access restricted", tone: summary.suspended > 0 ? "danger" : "success" },
        ]}
      />

      <DataTable
        title="Tenants"
        columns={[
          { key: "name", label: "Tenant" },
          { key: "slug", label: "Slug" },
          { key: "email", label: "Email" },
          { key: "plan", label: "Plan" },
          { key: "status", label: "Status" },
        ]}
        data={tenants}
        meta={meta}
      />
    </AppLayout>
  );
}