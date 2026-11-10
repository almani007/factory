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

  return (
    <AppLayout title="Accounting & Finance">
      <PageHeader
        title="Accounting & Finance"
        subtitle="Double-entry ledger, journal entries, and financial reports"
      >
        <Button className="bg-factory-600 text-white hover:bg-factory-500">
          <Plus size={16} /> New Journal Entry
        </Button>
      </PageHeader>

      <div className="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
        <Card className="p-6">
          <CardHeader>
            <CardTitle>Trial Balance</CardTitle>
          </CardHeader>
          <CardContent>
            <p className="text-2xl font-bold text-gray-900">Rs. 2.1M</p>
            <p className="text-xs text-green-600 mt-1">Balanced</p>
          </CardContent>
        </Card>
        <Card className="p-6">
          <CardHeader>
            <CardTitle>Accounts Receivable</CardTitle>
          </CardHeader>
          <CardContent>
            <p className="text-2xl font-bold text-gray-900">Rs. 480,000</p>
            <p className="text-xs text-gray-600 mt-1">Outstanding</p>
          </CardContent>
        </Card>
        <Card className="p-6">
          <CardHeader>
            <CardTitle>Accounts Payable</CardTitle>
          </CardHeader>
          <CardContent>
            <p className="text-2xl font-bold text-gray-900">Rs. 133,500</p>
            <p className="text-xs text-gray-600 mt-1">Outstanding</p>
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
        createUrl="/accounting/journal-entries/create"
        meta={meta}
      />
    </AppLayout>
  );
}