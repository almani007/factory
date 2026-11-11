/**
 * Brixel FactoryOS — Webhooks
 * Webhook management for third-party integrations.
 */

import { Head, Link, router, usePage } from "@inertiajs/react";
import { AppLayout } from "@/components/layout";
import { PageHeader } from "@/components/layout";
import { DataTable } from "@/components/DataTable";
import { Plus, Shield, Activity } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";

interface Webhook {
  id: number;
  name: string;
  url: string;
  events: string;
  status: string;
  last_triggered: string;
  created_at: string;
}

const STATUS_BADGE: Record<string, string> = {
  active: "bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-200",
  inactive: "bg-slate-100 text-slate-500 ring-1 ring-inset ring-slate-200",
  error: "bg-rose-50 text-rose-700 ring-1 ring-inset ring-rose-200",
  testing: "bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-200",
};

export default function Webhooks() {
  const { props: data } = usePage() as any;
  const webhooks = data?.webhooks?.data || [];

  return (
    <AppLayout title="Webhooks">
      <PageHeader
        title="Webhook Management"
        subtitle="Third-party integration webhooks and event notifications"
        breadcrumbs={[{ label: "Integrations", href: "/integrations/webhooks" }, { label: "Webhooks" }]}
      >
        <Button onClick={() => router.visit("/integrations/webhooks/create")}>
          <Plus size={16} /> New Webhook
        </Button>
      </PageHeader>

      <DataTable
        title="Webhooks"
        columns={[
          { key: "name", label: "Webhook Name" },
          { key: "url", label: "URL" },
          { key: "events", label: "Events" },
          { key: "status", label: "Status", format: (value: string) => (
            <Badge className={STATUS_BADGE[value] || "bg-slate-100 text-slate-600"}>
              {value.replace(/_/g, " ")}
            </Badge>
          )},
          { key: "last_triggered", label: "Last Triggered" },
          { key: "created_at", label: "Created" },
        ]}
        data={webhooks}
        rowActions={[
          { label: "View", icon: <Shield size={14} />, variant: "ghost" },
          { label: "Edit", icon: <Activity size={14} />, variant: "outline" },
        ]}
      />
    </AppLayout>
  );
}
