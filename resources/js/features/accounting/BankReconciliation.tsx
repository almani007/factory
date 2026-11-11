/**
 * Brixel FactoryOS — Bank Reconciliation
 * Reconcile bank statements with book balances.
 */

import { Head, Link, router, usePage } from "@inertiajs/react";
import { AppLayout } from "@/components/layout";
import { PageHeader } from "@/components/layout";
import { DataTable } from "@/components/DataTable";
import { Plus, Eye, Edit, Banknote } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";

interface Reconciliation {
  id: number;
  reference: string;
  account_name: string;
  statement_date: string;
  book_balance: number;
  bank_balance: number;
  status: string;
  status_label: string;
  reconciled_date: string;
}

const STATUS_BADGE: Record<string, string> = {
  reconciled: "bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-200",
  pending: "bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-200",
  open: "bg-slate-100 text-slate-500 ring-1 ring-inset ring-slate-200",
};

export default function BankReconciliation() {
  const { props: data } = usePage() as any;
  const reconciliations: Reconciliation[] = data?.reconciliations?.data || [];
  const meta = data?.reconciliations?.meta || {};

  return (
    <AppLayout title="Bank Reconciliation">
      <PageHeader
        title="Bank Reconciliation"
        subtitle="Reconcile bank statements with book balances"
        breadcrumbs={[{ label: "Accounting", href: "/accounting" }, { label: "Bank Reconciliation" }]}
      >
        <Button onClick={() => router.visit("/accounting/bank-reconciliation/create")}>
          <Plus size={16} /> New Reconciliation
        </Button>
      </PageHeader>

      <DataTable
        title="Bank Reconciliations"
        columns={[
          { key: "reference", label: "Reference" },
          { key: "account_name", label: "Bank Account" },
          { key: "statement_date", label: "Statement Date" },
          { key: "book_balance", label: "Book Balance", align: "right", format: (v: number) => `Rs. ${Number(v).toLocaleString(undefined, { maximumFractionDigits: 2 })}` },
          { key: "bank_balance", label: "Bank Balance", align: "right", format: (v: number) => `Rs. ${Number(v).toLocaleString(undefined, { maximumFractionDigits: 2 })}` },
          { key: "status", label: "Status", format: (value: string) => (
            <Badge className={STATUS_BADGE[value] || "bg-slate-100 text-slate-600"}>
              {value.replace(/_/g, " ")}
            </Badge>
          )},
          { key: "reconciled_date", label: "Reconciled On" },
        ]}
        data={reconciliations}
        meta={meta}
        rowActions={[
          { label: "View", icon: <Eye size={14} />, variant: "ghost" },
          { label: "Edit", icon: <Edit size={14} />, variant: "outline" },
        ]}
      />
    </AppLayout>
  );
}
