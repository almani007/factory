/**
 * Brixel FactoryOS — SMS Logs
 * SMS sending logs.
 */

import { Head, Link, usePage } from "@inertiajs/react";
import { AppLayout } from "@/components/layout";
import { PageHeader } from "@/components/layout";
import { DataTable } from "@/components/DataTable";
import { FileText } from "lucide-react";
import { Badge } from "@/components/ui/badge";

interface SMSLog {
  id: number;
  recipient: string;
  message: string;
  status: string;
  sent_at: string;
}

const STATUS_BADGE: Record<string, string> = {
  sent: "bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-200",
  failed: "bg-rose-50 text-rose-700 ring-1 ring-inset ring-rose-200",
  pending: "bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-200",
};

export default function SMSLogs() {
  const { props: data } = usePage() as any;
  const logs: SMSLog[] = data?.logs?.data || [];
  const meta = data?.logs?.meta || {};

  return (
    <AppLayout title="SMS Logs">
      <PageHeader
        title="SMS Logs"
        subtitle="SMS sending history and delivery status"
        breadcrumbs={[{ label: "Communications", href: "/communications/sms-templates" }, { label: "SMS Logs" }]}
      />

      <DataTable
        title="SMS Logs"
        columns={[
          { key: "recipient", label: "Recipient" },
          { key: "message", label: "Message" },
          { key: "status", label: "Status", format: (value: string) => (
            <Badge className={STATUS_BADGE[value] || "bg-slate-100 text-slate-600"}>
              {value.replace(/_/g, " ")}
            </Badge>
          )},
          { key: "sent_at", label: "Sent At" },
        ]}
        data={logs}
        meta={meta}
        emptyMessage="No SMS logs found."
      />
    </AppLayout>
  );
}
