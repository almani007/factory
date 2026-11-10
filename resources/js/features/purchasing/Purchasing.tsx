/**
 * Brixel FactoryOS — Purchasing Page
 * Suppliers, purchase orders, and GRN.
 */

import { Head, Link, usePage } from "@inertiajs/react";
import { AppLayout } from "@/components/layout";
import { PageHeader } from "@/components/layout";
import { DataTable } from "@/components/DataTable";
import { Plus } from "lucide-react";
import { Button } from "@/components/ui/button";

interface Supplier {
  id: number;
  name: string;
  code: string;
  contact_person: string;
  email: string;
  status: string;
}

export default function Purchasing() {
  const { props: data } = usePage() as any;
  const suppliers: Supplier[] = data?.suppliers?.data || [];
  const meta = data?.suppliers?.meta || {};

  return (
    <AppLayout title="Purchasing">
      <PageHeader
        title="Purchasing"
        subtitle="Suppliers, purchase orders, and goods receipts"
      >
        <Button className="bg-factory-600 text-white hover:bg-factory-500">
          <Plus size={16} /> New Purchase Order
        </Button>
      </PageHeader>

      <DataTable
        title="Suppliers"
        columns={[
          { key: "name", label: "Supplier" },
          { key: "code", label: "Code" },
          { key: "contact_person", label: "Contact" },
          { key: "email", label: "Email" },
          { key: "status", label: "Status" },
        ]}
        data={suppliers}
        createUrl="/suppliers/create"
        meta={meta}
      />
    </AppLayout>
  );
}