/**
 * Brixel FactoryOS — Accounting Page
 * Journal entries and financial reports.
 */

import { Head, Link, usePage } from "@inertiajs/react";
import { AppLayout } from "@/components/layout";
import { PageHeader } from "@/components/layout";
import { DataTable } from "@/components/DataTable";
import { Plus } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { KPICard } from "@/components/KPICard";

export default function Accounting() {
  const { props: data } = usePage() as any;
  const journalEntries = data?.journal_entries?.data || [];
  const meta = data?.journal_entries?.meta || {};
  const summary = data?.finance_summary || {
    trial_balance: "Rs. 0",
    receivable: "Rs. 0",
    payable: "Rs. 0",
  };

  return (
    <AppLayout title="Accounting & Finance">
      <PageHeader
        title="Accounting & Finance"
        subtitle="Double-entry ledger, journal entries, and financial reports"
      >
        <Button>
          <Plus size={16} /> New Journal Entry
        </Button>
      </PageHeader>

      <div className="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
        <Card>
          <CardHeader className="p-5 pb-2">
            <CardTitle className="text-sm font-medium text-slate-500">Trial Balance</CardTitle>
          </CardHeader>
          <CardContent className="p-5 pt-0">
            <p className="text-2xl font-semibold tracking-tight text-slate-900 tabular-nums">{summary.trial_balance}</p>
            <p className="text-xs text-green-600 mt-1">Balanced</p>
          </CardContent>
        </Card>
        <Card>
          <CardHeader className="p-5 pb-2">
            <CardTitle className="text-sm font-medium text-slate-500">Accounts Receivable</CardTitle>
          </CardHeader>
          <CardContent className="p-5 pt-0">
            <p className="text-2xl font-semibold tracking-tight text-slate-900 tabular-nums">{summary.receivable}</p>
            <p className="text-xs text-slate-600 mt-1">Outstanding</p>
          </CardContent>
        </Card>
        <Card>
          <CardHeader className="p-5 pb-2">
            <CardTitle className="text-sm font-medium text-slate-500">Accounts Payable</CardTitle>
          </CardHeader>
          <CardContent className="p-5 pt-0">
            <p className="text-2xl font-semibold tracking-tight text-slate-900 tabular-nums">{summary.payable}</p>
            <p className="text-xs text-slate-600 mt-1">Outstanding</p>
          </CardContent>
        </Card>
      </div>

      <DataTable
        title="Journal Entries"
        columns={[
          { key: "reference", label: "Reference" },
          { key: "document_date", label: "Date" },
          { key: "status", label: "Status" },
          { key: "description", label: "Description" },
        ]}
        data={journalEntries}
        meta={meta}
      />
    </AppLayout>
  );
}