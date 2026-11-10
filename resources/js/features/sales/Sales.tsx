/**
 * Brixel FactoryOS — Sales Page
 * Customers and sales orders.
 */

import { Head, Link, usePage } from "@inertiajs/react";
import { AppLayout } from "@/components/layout";
import { PageHeader } from "@/components/layout";
import { DataTable } from "@/components/DataTable";
import { Plus } from "lucide-react";
import { Button } from "@/components/ui/button";

interface Customer {
  id: number;
  name: string;
  code: string;
  contact_person: string;
  email: string;
  credit_limit: number;
}

export default function Sales() {
  const { data } = usePage() as any;
  const customers: Customer[] = data?.customers?.data || [];
  const meta = data?.customers?.meta || {};

  return (
    <AppLayout title="Sales & Orders">
      <PageHeader
        title="Sales & Orders"
        subtitle="Customers, sales orders, and order tracking"
      >
        <Button className="bg-factory-600 text-white hover:bg-factory-500">
          <Plus size={16} /> New Sales Order
        </Button>
      </PageHeader>

      <DataTable
        title="Customers"
        columns={[
          { key: "name", label: "Customer" },
          { key: "code", label: "Code" },
          { key: "contact_person", label: "Contact" },
          { key: "email", label: "Email" },
          { key: "credit_limit", label: "Credit Limit" },
        ]}
        data={customers}
        createUrl="/customers/create"
        meta={meta}
      />
    </AppLayout>
  );
}