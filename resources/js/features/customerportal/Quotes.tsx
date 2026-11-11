/**
 * Brixel FactoryOS — Customer Portal Quotes
 * Customer quote listing page.
 */

import { Head, Link, router, usePage } from "@inertiajs/react";
import { AppLayout } from "@/components/layout";
import { PageHeader } from "@/components/layout";
import { DataTable } from "@/components/DataTable";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import { Check } from "lucide-react";

interface Quote {
  id: number;
  quote_number: string;
  status: string;
  quote_date: string;
  valid_until: string;
  total_amount: number;
}

const STATUS_BADGE: Record<string, string> = {
  draft: "bg-slate-100 text-slate-500",
  sent: "bg-amber-50 text-amber-700",
  accepted: "bg-emerald-50 text-emerald-700",
  rejected: "bg-rose-50 text-rose-700",
  expired: "bg-slate-100 text-slate-500",
};

export default function CustomerQuotes() {
  const { props: data } = usePage() as any;
  const quotes: Quote[] = data?.quotes?.data || [];
  const meta = data?.quotes?.meta || {};

  const acceptQuote = (id: number) => {
    router.post(`/customer/quotes/${id}/accept`, {}, {
      onSuccess: () => router.visit("/customer/quotes"),
    });
  };

  return (
    <AppLayout title="Customer Quotes">
      <PageHeader
        title="My Quotes"
        subtitle="View and accept sales quotes"
        breadcrumbs={[{ label: "Customer Portal", href: "/customer/dashboard" }, { label: "My Quotes" }]}
      />

      <DataTable
        title="Quotes"
        columns={[
          { key: "quote_number", label: "Quote #" },
          { key: "status", label: "Status", format: (value: string) => (
            <Badge className={STATUS_BADGE[value] || "bg-slate-100 text-slate-600"}>
              {value.replace(/_/g, " ")}
            </Badge>
          )},
          { key: "quote_date", label: "Quote Date" },
          { key: "valid_until", label: "Valid Until" },
          { key: "total_amount", label: "Total", align: "right", format: (v: number) => `Rs. ${Number(v).toLocaleString(undefined, { maximumFractionDigits: 2 })}` },
        ]}
        data={quotes}
        meta={meta}
        rowActions={quotes.map((q) => q.status === "sent" ? {
          label: "Accept",
          icon: <Check size={14} />,
          variant: "default" as const,
          onClick: () => acceptQuote(q.id),
        } : {
          label: "View",
          icon: <Plus size={14} />,
          variant: "ghost" as const,
        } as any)}
        emptyMessage="No quotes found."
      />
    </AppLayout>
  );
}
