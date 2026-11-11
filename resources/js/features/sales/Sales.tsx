/**
 * Brixel FactoryOS — Sales Page
 * Customers and sales orders.
 */

import { Head, Link, router, usePage } from "@inertiajs/react";
import { AppLayout } from "@/components/layout";
import { PageHeader } from "@/components/layout";
import { DataTable } from "@/components/DataTable";
import { StatStrip } from "@/components/StatStrip";
import { Plus, Eye, Edit } from "lucide-react";
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
  const { props: data } = usePage() as any;
  const customers: Customer[] = data?.customers?.data || [];
  const meta = data?.customers?.meta || {};
  const summary = data?.sales_summary || {
    total: 0,
    open_orders: 0,
    receivable: "Rs. 0",
  };

  return (
    <AppLayout title="Sales & Orders">
      <PageHeader
        title="Sales & Orders"
        subtitle="Customers, sales orders, and order tracking"
      >
        <Button onClick={() => router.visit("/sales/create")}>
          <Plus size={16} /> New Sales Order
        </Button>
      </PageHeader>

      <StatStrip
        items={[
          { label: "Customers", value: summary.total, hint: "Active accounts" },
          { label: "Open sales orders", value: summary.open_orders, hint: "Awaiting fulfilment", tone: "warning" },
          { label: "Receivables", value: summary.receivable, hint: "Outstanding invoices", tone: "danger" },
        ]}
      />

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
        meta={meta}
        rowActions={[
          { label: "View", icon: <Eye size={14} />, variant: "ghost" },
          { label: "Edit", icon: <Edit size={14} />, variant: "outline" },
        ]}
      />
    </AppLayout>
  );
}