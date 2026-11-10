/**
 * Brixel FactoryOS — Settings Page
 * Tenant-wide application preferences.
 */

import { usePage } from "@inertiajs/react";
import { AppLayout } from "@/components/layout";
import { PageHeader } from "@/components/layout";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Save } from "lucide-react";

export default function Settings() {
  const { props } = usePage() as any;
  const tenant = props?.tenant || {};
  const settings = props?.settings || {};

  return (
    <AppLayout title="Settings">
      <PageHeader
        title="Settings"
        subtitle="Company profile, localisation and manufacturing defaults"
      >
        <Button>
          <Save size={16} /> Save Changes
        </Button>
      </PageHeader>

      <div className="grid gap-6 lg:grid-cols-2">
        <Card>
          <CardHeader className="p-5 pb-2">
            <CardTitle className="text-base font-semibold text-slate-900">Company Profile</CardTitle>
          </CardHeader>
          <CardContent className="space-y-4">
            <Field label="Company Name" value={tenant.company_name || ""} placeholder="Acme Manufacturing" />
            <Field label="Legal / Tax ID" value={tenant.tax_id || ""} placeholder="NTN-1234567" />
            <Field label="Primary Email" value={tenant.email || ""} placeholder="operations@example.com" />
            <Field label="Phone" value={tenant.phone || ""} placeholder="+92 300 0000000" />
          </CardContent>
        </Card>

        <Card>
          <CardHeader className="p-5 pb-2">
            <CardTitle className="text-base font-semibold text-slate-900">Localisation</CardTitle>
          </CardHeader>
          <CardContent className="space-y-4">
            <Field label="Currency" value={settings.currency || "PKR"} placeholder="PKR" />
            <Field label="Timezone" value={settings.timezone || "Asia/Karachi"} placeholder="Asia/Karachi" />
            <Field label="Locale" value={settings.locale || "en"} placeholder="en" />
            <Field label="Fiscal Year Start" value={settings.fiscal_year_start || "07-01"} placeholder="07-01" />
          </CardContent>
        </Card>

        <Card>
          <CardHeader className="p-5 pb-2">
            <CardTitle className="text-base font-semibold text-slate-900">Manufacturing Defaults</CardTitle>
          </CardHeader>
          <CardContent className="space-y-4">
            <Field label="Default Warehouse" value={settings.default_warehouse || ""} placeholder="Main Store" />
            <Field label="Costing Method" value={settings.costing_method || "weighted_average"} placeholder="weighted_average" />
            <Field label="Quality Release Gate" value={settings.quality_gate || "enabled"} placeholder="enabled" />
            <Field label="Shift Pattern" value={settings.shift_pattern || "3 x 8 hours"} placeholder="3 x 8 hours" />
          </CardContent>
        </Card>

        <Card>
          <CardHeader className="p-5 pb-2">
            <CardTitle className="text-base font-semibold text-slate-900">Notifications</CardTitle>
          </CardHeader>
          <CardContent className="space-y-4">
            <Field label="Low Stock Alerts" value={settings.low_stock_alerts || "enabled"} placeholder="enabled" />
            <Field label="Delayed Order Alerts" value={settings.delayed_order_alerts || "enabled"} placeholder="enabled" />
            <Field label="Quality Failure Alerts" value={settings.quality_alerts || "enabled"} placeholder="enabled" />
            <Field label="Digest Recipients" value={settings.digest_recipients || ""} placeholder="ops@example.com" />
          </CardContent>
        </Card>
      </div>
    </AppLayout>
  );
}

function Field({
  label,
  value,
  placeholder,
}: {
  label: string;
  value: string;
  placeholder?: string;
}) {
  return (
    <label className="block">
      <span className="mb-1 block text-sm font-medium text-slate-700">{label}</span>
      <input
        type="text"
        defaultValue={value}
        placeholder={placeholder}
        className="w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-factory-500 focus:outline-none focus:ring-2 focus:ring-factory-500/30"
      />
    </label>
  );
}
