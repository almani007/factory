/**
 * Brixel FactoryOS — Maintenance Tools
 * Tool management and calibration tracking.
 */

import { Head, Link, router, usePage } from "@inertiajs/react";
import { AppLayout } from "@/components/layout";
import { PageHeader } from "@/components/layout";
import { DataTable } from "@/components/DataTable";
import { StatStrip } from "@/components/StatStrip";
import { Plus, Wrench, Activity } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";

interface Tool {
  id: number;
  tool_code: string;
  tool_name: string;
  tool_type: string;
  status: string;
  purchase_date: string;
  last_calibration: string;
  next_calibration: string;
  location: string;
}

const STATUS_BADGE: Record<string, string> = {
  active: "bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-200",
  inactive: "bg-slate-100 text-slate-500 ring-1 ring-inset ring-slate-200",
  maintenance: "bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-200",
  retired: "bg-rose-50 text-rose-700 ring-1 ring-inset ring-rose-200",
};

export default function ToolManagement() {
  const { props: data } = usePage() as any;
  const tools: Tool[] = data?.tools?.data || [];
  const meta = data?.tools?.meta || {};

  const totalTools = tools.length;
  const activeTools = tools.filter((t: Tool) => t.status === "active").length;
  const maintenanceTools = tools.filter((t: Tool) => t.status === "maintenance").length;

  const totalToolsStr = String(totalTools);
  const activeToolsStr = String(activeTools);
  const mtsStr = String(maintenanceTools);

  return (
    <AppLayout title="Tool Management">
      <PageHeader
        title="Tool Management"
        subtitle="Tool tracking and calibration management"
        breadcrumbs={[{ label: "Maintenance", href: "/maintenance" }, { label: "Tools" }]}
      >
        <Button onClick={() => router.visit("/maintenance/tools/create")}>
          <Plus size={16} /> New Tool
        </Button>
      </PageHeader>

      <StatStrip
        items={[
          { label: "Total Tools", value: totalToolsStr, hint: "All tools" },
          { label: "Active Tools", value: activeToolsStr, hint: "Active tools" },
          { label: "Due for Calibration", value: mtsStr, hint: "Needs calibration" },
        ]}
      />

      <DataTable
        title="Tools"
        columns={[
          { key: "tool_code", label: "Tool Code" },
          { key: "tool_name", label: "Tool Name" },
          { key: "tool_type", label: "Type", format: (value: string) => value.replace(/_/g, " ") },
          { key: "purchase_date", label: "Purchase Date" },
          { key: "last_calibration", label: "Last Calibration" },
          { key: "next_calibration", label: "Next Calibration" },
          { key: "location", label: "Location" },
          { key: "status", label: "Status", format: (value: string) => (
            <Badge className={STATUS_BADGE[value] || "bg-slate-100 text-slate-600"}>
              {value.replace(/_/g, " ")}
            </Badge>
          )},
        ]}
        data={tools}
        meta={meta}
        rowActions={[
          { label: "View", icon: <Wrench size={14} />, variant: "ghost" },
          { label: "Edit", icon: <Activity size={14} />, variant: "outline" },
        ]}
        emptyMessage="No tools found."
      />
    </AppLayout>
  );
}
