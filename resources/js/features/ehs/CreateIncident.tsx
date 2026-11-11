/**
 * Brixel FactoryOS — EHS Create Incident
 * Report a new EHS incident.
 */

import { Head, Link, router, usePage, useForm } from "@inertiajs/react";
import { AppLayout } from "@/components/layout";
import { PageHeader } from "@/components/layout";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { AlertTriangle, ArrowLeft } from "lucide-react";
import { cn } from "@/lib/utils";

interface Employee {
  id: number;
  employee_code: string;
  name: string;
}

export default function CreateEHSIncident() {
  const { props: data } = usePage() as any;
  const employees: Employee[] = data?.employees || [];

  const { data: formData, setData, errors, isPending } =
    useForm<{
      incident_number: string;
      incident_type: string;
      employee_id: string;
      incident_date: string;
      severity: string;
      description: string;
    }>({
      incident_number: "",
      incident_type: "",
      employee_id: "",
      incident_date: "",
      severity: "low",
      description: "",
    });

  const INCIDENT_TYPES = [
    { value: "injury", label: "Injury" },
    { value: "near_miss", label: "Near Miss" },
    { value: "property_damage", label: "Property Damage" },
    { value: "environmental", label: "Environmental" },
    { value: "illness", label: "Illness" },
  ];

  const SEVERITIES = [
    { value: "low", label: "Low" },
    { value: "medium", label: "Medium" },
    { value: "high", label: "High" },
    { value: "critical", label: "Critical" },
  ];

  const submit = (event: React.FormEvent) => {
    event.preventDefault();
    router.post("/ehs/incidents", formData, {
      onSuccess: () => router.visit("/ehs/incidents"),
    });
  };

  return (
    <AppLayout title="Report Incident">
      <PageHeader
        title="Report EHS Incident"
        subtitle="Report a new health, safety, or environmental incident"
        breadcrumbs={[
          { label: "EHS", href: "/ehs" },
          { label: "Incidents", href: "/ehs/incidents" },
          { label: "Report Incident" },
        ]}
        actions={
          <Link
            href="/ehs/incidents"
            className="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-slate-200 bg-white px-3 text-sm font-medium text-slate-700 shadow-sm transition-colors hover:bg-slate-50"
          >
            <ArrowLeft size={14} /> Back to Incidents
          </Link>
        }
      />

      <form onSubmit={submit} className="space-y-6 max-w-2xl">
        <Card>
          <CardHeader>
            <CardTitle className="text-base font-semibold text-slate-900">Incident Details</CardTitle>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Incident Number</label>
                <Input
                  value={formData.incident_number}
                  onChange={(e) => setData("incident_number", e.currentTarget.value)}
                  placeholder="EHS-2024-0001"
                  className={cn("w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-factory-500 focus:outline-none focus:ring-1 focus:ring-factory-500", errors.incident_number ? "border-rose-500" : "")}
                />
                {errors.incident_number && <p className="text-xs text-rose-600">{errors.incident_number}</p>}
              </div>
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Incident Type</label>
                <Select onValueChange={(value) => setData("incident_type", value)}>
                  <SelectTrigger className="w-full">
                    <SelectValue placeholder={formData.incident_type} />
                  </SelectTrigger>
                  <SelectContent>
                    {INCIDENT_TYPES.map((t) => (
                      <SelectItem key={t.value} value={t.value}>{t.label}</SelectItem>
                    ))}
                  </SelectContent>
                </Select>
                {errors.incident_type && <p className="text-xs text-rose-600">{errors.incident_type}</p>}
              </div>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Employee</label>
                <Select onValueChange={(value) => setData("employee_id", value)}>
                  <SelectTrigger className="w-full">
                    <SelectValue placeholder="Select employee" />
                  </SelectTrigger>
                  <SelectContent>
                    {employees.map((e) => (
                      <SelectItem key={e.id} value={String(e.id)}>{e.employee_code} — {e.name}</SelectItem>
                    ))}
                  </SelectContent>
                </Select>
                {errors.employee_id && <p className="text-xs text-rose-600">{errors.employee_id}</p>}
              </div>
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Incident Date</label>
                <Input
                  type="date"
                  value={formData.incident_date}
                  onChange={(e) => setData("incident_date", e.currentTarget.value)}
                  className={cn("w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-factory-500 focus:outline-none focus:ring-1 focus:ring-factory-500", errors.incident_date ? "border-rose-500" : "")}
                />
                {errors.incident_date && <p className="text-xs text-rose-600">{errors.incident_date}</p>}
              </div>
            </div>

            <div className="space-y-1">
              <label className="text-sm font-medium text-slate-700">Severity</label>
              <Select onValueChange={(value) => setData("severity", value)}>
                <SelectTrigger className="w-full">
                  <SelectValue placeholder={formData.severity} />
                </SelectTrigger>
                <SelectContent>
                  {SEVERITIES.map((s) => (
                    <SelectItem key={s.value} value={s.value}>{s.label}</SelectItem>
                  ))}
                </SelectContent>
              </Select>
              {errors.severity && <p className="text-xs text-rose-600">{errors.severity}</p>}
            </div>

            <div className="space-y-1">
              <label className="text-sm font-medium text-slate-700">Description</label>
              <Input
                value={formData.description}
                onChange={(e) => setData("description", e.currentTarget.value)}
                placeholder="Describe the incident in detail"
                className={cn("w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-factory-500 focus:outline-none focus:ring-1 focus:ring-factory-500", errors.description ? "border-rose-500" : "")}
              />
              {errors.description && <p className="text-xs text-rose-600">{errors.description}</p>}
            </div>
          </CardContent>
        </Card>

        <div className="flex justify-end">
          <Button type="submit" disabled={isPending}>
            {isPending ? (
              "Submitting…"
            ) : (
              <>
                <AlertTriangle size={16} />
                Report Incident
              </>
            )}
          </Button>
        </div>
      </form>
    </AppLayout>
  );
}
