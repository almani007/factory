/**
 * Brixel FactoryOS — Print Report
 * Print-friendly reports.
 */

import { Head, Link, usePage } from "@inertiajs/react";
import { AppLayout } from "@/components/layout";
import { PageHeader } from "@/components/layout";

export default function PrintReport() {
  const { props: data } = usePage() as any;
  const reportType: string = data?.report_type || "";
  const reportData: any[] = data?.data || [];

  const reportLabels: Record<string, string> = {
    production: "Production Report",
    inventory: "Inventory Report",
    sales: "Sales Report",
  };

  return (
    <AppLayout title="Print Report">
      <PageHeader
        title="Print Report"
        subtitle={reportLabels[reportType] || "Report"}
        breadcrumbs={[{ label: "Reports", href: "/reports" }, { label: "Print Report" }]}
        actions={
          <button
            onClick={() => window.print()}
            className="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-factory-50 text-factory-600 font-medium text-sm hover:bg-factory-100 transition-colors"
          >
            <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2zM17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2zM17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2zM17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2z" /></svg>
            Print
          </button>
        }
      />

      <div className="max-w-4xl">
        <Card>
          <CardHeader>
            <CardTitle className="text-base font-semibold text-slate-900">
              {reportLabels[reportType] || "Report"}
            </CardTitle>
            <CardDescription className="text-sm text-slate-500">
              Generated on {new Date().toLocaleDateString()}
            </CardDescription>
          </CardHeader>
          <CardContent className="p-6">
            <div className="overflow-x-auto">
              <table className="w-full text-sm">
                <thead>
                  <tr className="border-b border-slate-200 bg-slate-50">
                    {Object.keys(reportData[0] || {}).map((key) => (
                      <th key={key} className="px-4 py-3 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">
                        {key.replace(/_/g, " ").toUpperCase()}
                      </th>
                    ))}
                  </tr>
                </thead>
                <tbody className="divide-y divide-slate-200">
                  {reportData.map((row: any) => (
                    <tr key={row.id} className="hover:bg-slate-50/50">
                      {Object.values(row).map((value: any) => (
                        <td key={String(value)} className="px-4 py-3 text-sm text-slate-600">
                          {typeof value === "number" ? Number(value).toLocaleString() : value}
                        </td>
                      ))}
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          </CardContent>
        </Card>
      </div>
    </AppLayout>
  );
}
