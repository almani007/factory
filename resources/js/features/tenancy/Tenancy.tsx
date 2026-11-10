/**
 * Brixel FactoryOS — Tenancy Page
 * Multi-tenant management.
 */

import { Head, Link, usePage } from "@inertiajs/react";
import { AppLayout } from "@/components/layout";
import { PageHeader } from "@/components/layout";
import { DataTable } from "@/components/DataTable";
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
  const { data } = usePage() as any;
  const tenants: Tenant[] = data?.tenants?.data || [];
  const meta = data?.tenants?.meta || {};

  return (
    <AppLayout title="Tenancy Management">
      <PageHeader
        title="Tenancy Management"
        subtitle="Companies, tenants and access isolation"
      >
        <Button className="bg-factory-600 text-white hover:bg-factory-500">
          <Plus size={16} /> Onboard Tenant
        </Button>
      </PageHeader>

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
        createUrl="/tenants/create"
        meta={meta}
      />
    </AppLayout>
  );
}