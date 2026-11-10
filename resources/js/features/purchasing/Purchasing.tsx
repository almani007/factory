/**
 * Brixel FactoryOS — Purchasing Page
 * Suppliers, purchase orders, and GRN.
 */

import { Head, Link, usePage } from "@inertiajs/react";
import { AppLayout } from "@/components/layout";
import { PageHeader } from "@/components/layout";
import { DataTable } from "@/components/DataTable";
import { StatStrip } from "@/components/StatStrip";
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
  const summary = data?.purchasing_summary || {
    total: 0,
    active: 0,
    payable: "Rs. 0",
  };

  return (
    <AppLayout title="Purchasing">
      <PageHeader
        title="Purchasing"
        subtitle="Suppliers, purchase orders, and goods receipts"
      >
        <Button>
          <Plus size={16} /> New Purchase Order
        </Button>
      </PageHeader>

      <StatStrip
        items={[
          { label: "Suppliers", value: summary.total, hint: "Approved vendor list" },
          { label: "Active", value: summary.active, hint: "Available to purchase", tone: "success" },
          { label: "Open payables", value: summary.payable, hint: "Bills awaiting payment", tone: "warning" },
        ]}
      />

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
        meta={meta}
      />
    </AppLayout>
  );
}