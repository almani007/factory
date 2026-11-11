/**
 * Brixel FactoryOS — Workflow Rules
 * Business rules engine for automated workflow triggers.
 */

import { Head, Link, router, usePage } from "@inertiajs/react";
import { AppLayout } from "@/components/layout";
import { PageHeader } from "@/components/layout";
import { DataTable } from "@/components/DataTable";
import { Plus, Shield, Activity } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";

interface WorkflowRule {
  id: number;
  rule_name: string;
  workflow_type: string;
  condition: string;
  action: string;
  status: string;
  created_at: string;
}

const STATUS_BADGE: Record<string, string> = {
  active: "bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-200",
  inactive: "bg-slate-100 text-slate-500 ring-1 ring-inset ring-slate-200",
  draft: "bg-slate-100 text-slate-500 ring-1 ring-inset ring-slate-200",
  testing: "bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-200",
};

export default function WorkflowRules() {
  const { props: data } = usePage() as any;
  const rules = data?.rules?.data || [];

  return (
    <AppLayout title="Workflow Rules">
      <PageHeader
        title="Workflow Rules Engine"
        subtitle="Business rules engine for automated workflow triggers"
        breadcrumbs={[{ label: "Workflow", href: "/workflow/rules" }, { label: "Rules" }]}
      >
        <Button onClick={() => router.visit("/workflow/approvals")}>
          <Plus size={16} /> New Rule
        </Button>
      </PageHeader>

      <DataTable
        title="Workflow Rules"
        columns={[
          { key: "rule_name", label: "Rule Name" },
          { key: "workflow_type", label: "Type", format: (value: string) => value.replace(/_/g, " ") },
          { key: "condition", label: "Condition" },
          { key: "action", label: "Action" },
          { key: "status", label: "Status", format: (value: string) => (
            <Badge className={STATUS_BADGE[value] || "bg-slate-100 text-slate-600"}>
              {value.replace(/_/g, " ")}
            </Badge>
          )},
          { key: "created_at", label: "Created" },
        ]}
        data={rules}
        rowActions={[
          { label: "View", icon: <Shield size={14} />, variant: "ghost" },
          { label: "Edit", icon: <Activity size={14} />, variant: "outline" },
        ]}
      />
    </AppLayout>
  );
}
