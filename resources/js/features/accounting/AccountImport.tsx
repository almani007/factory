/**
 * Brixel FactoryOS — Chart of Accounts CSV Import
 */
import * as React from "react";
import { Link, router, usePage } from "@inertiajs/react";
import { AppLayout } from "@/components/layout";
import { PageHeader } from "@/components/layout";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";
import { ArrowLeft, Download, Upload, CheckCircle2, AlertTriangle, FileSpreadsheet } from "lucide-react";

export default function AccountImport() {
  const { props } = usePage() as any;
  const preview: any[] | null = props?.preview || null;
  const summary = props?.summary || null;
  const importToken = props?.import_token || null;
  const errors = props?.errors || {};
  const [file, setFile] = React.useState<File | null>(null);
  const [busy, setBusy] = React.useState(false);

  const runPreview = () => {
    if (!file) return;
    const fd = new FormData();
    fd.append("file", file);
    setBusy(true);
    router.post("/accounting/chart-of-accounts/import/preview", fd, {
      forceFormData: true,
      preserveState: true,
      preserveScroll: true,
      onFinish: () => setBusy(false),
    });
  };

  const commit = () => {
    if (!importToken) return;
    if (!window.confirm(`Import ${summary?.valid ?? 0} validated account(s)?`)) return;
    setBusy(true);
    router.post("/accounting/chart-of-accounts/import/commit", { token: importToken }, {
      onFinish: () => setBusy(false),
    });
  };

  return (
    <AppLayout title="Import Chart of Accounts">
      <PageHeader
        title="Import Chart of Accounts"
        subtitle="Upload a CSV, preview validation, then import accounts in bulk"
        breadcrumbs={[{ label: "Accounting", href: "/accounting" }, { label: "Chart of Accounts", href: "/accounting/chart-of-accounts" }, { label: "Import" }]}
      >
        <div className="flex flex-wrap items-center gap-2">
          <Link href="/accounting/chart-of-accounts/import/template" className="inline-flex h-9 items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50"><Download size={14}/> Download Template</Link>
          <Link href="/accounting/chart-of-accounts" className="inline-flex h-9 items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50"><ArrowLeft size={16}/> Back</Link>
        </div>
      </PageHeader>

      <Card className="mb-6 max-w-3xl">
        <CardHeader><CardTitle className="text-base font-semibold text-slate-900">Upload CSV</CardTitle></CardHeader>
        <CardContent className="space-y-4">
          <p className="text-sm text-slate-600">
            Required columns: <span className="font-mono text-xs">code</span>, <span className="font-mono text-xs">name</span>, <span className="font-mono text-xs">type</span>.
            Optional: <span className="font-mono text-xs">parent_code</span>, <span className="font-mono text-xs">group_code</span>,
            <span className="font-mono text-xs">allow_direct_posting</span>, <span className="font-mono text-xs">is_cash_account</span>,
            <span className="font-mono text-xs">is_bank_account</span>, <span className="font-mono text-xs">is_active</span>.
          </p>
          <div className="flex flex-col gap-3 sm:flex-row sm:items-center">
            <label className="inline-flex h-10 cursor-pointer items-center gap-2 rounded-lg border border-dashed border-slate-300 bg-slate-50 px-4 text-sm font-medium text-slate-700 hover:bg-slate-100">
              <FileSpreadsheet size={16} /> {file ? file.name : "Choose CSV file"}
              <input type="file" accept=".csv,text/csv" className="hidden" onChange={(e) => setFile(e.currentTarget.files?.[0] || null)} />
            </label>
            <Button onClick={runPreview} disabled={!file || busy}><Upload size={16}/> {busy ? "Validating…" : "Validate & Preview"}</Button>
          </div>
          {errors.file && <p className="text-xs text-rose-600">{errors.file}</p>}
        </CardContent>
      </Card>

      {preview && (
        <>
          <div className="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6 max-w-3xl">
            <Card><CardContent className="p-5"><p className="text-xs font-medium text-slate-500">Rows</p><p className="text-2xl font-semibold text-slate-900 tabular-nums">{summary?.total ?? 0}</p></CardContent></Card>
            <Card><CardContent className="p-5"><p className="text-xs font-medium text-slate-500">Valid</p><p className="text-2xl font-semibold text-emerald-700 tabular-nums">{summary?.valid ?? 0}</p></CardContent></Card>
            <Card><CardContent className="p-5"><p className="text-xs font-medium text-slate-500">Invalid</p><p className="text-2xl font-semibold text-rose-700 tabular-nums">{summary?.invalid ?? 0}</p></CardContent></Card>
          </div>

          <div className="rounded-xl border border-slate-200 bg-white shadow-sm overflow-hidden">
            <div className="flex flex-col gap-3 border-b border-slate-200/60 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
              <div>
                <h2 className="text-base font-semibold tracking-tight text-slate-900">Validation Preview</h2>
                <p className="mt-0.5 text-xs text-slate-500">Only valid rows will be imported.</p>
              </div>
              <Button onClick={commit} disabled={!importToken || (summary?.valid ?? 0) === 0 || busy}><Upload size={16}/> Import {summary?.valid ?? 0} Valid Row(s)</Button>
            </div>
            <div className="overflow-x-auto">
              <table className="min-w-full divide-y divide-slate-100 text-sm">
                <thead className="bg-slate-50">
                  <tr>
                    {["Row", "Code", "Name", "Type", "Parent", "Group", "Status", "Issues"].map((h) => (
                      <th key={h} className="px-4 py-2.5 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">{h}</th>
                    ))}
                  </tr>
                </thead>
                <tbody className="divide-y divide-slate-100">
                  {preview.map((r: any) => (
                    <tr key={`${r.row}-${r.code}`} className={r.valid ? "hover:bg-slate-50" : "bg-rose-50/40"}>
                      <td className="px-4 py-2 text-slate-500 tabular-nums">{r.row}</td>
                      <td className="px-4 py-2 font-mono text-xs font-semibold text-slate-800">{r.code || "—"}</td>
                      <td className="px-4 py-2 text-slate-800">{r.name || "—"}</td>
                      <td className="px-4 py-2 capitalize text-slate-700">{r.type || "—"}</td>
                      <td className="px-4 py-2 text-slate-600">{r.parent_code || "—"}</td>
                      <td className="px-4 py-2 text-slate-600">{r.group_code || "—"}</td>
                      <td className="px-4 py-2">
                        {r.valid
                          ? <Badge className="bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-200"><CheckCircle2 size={12} className="mr-1 inline"/> Valid</Badge>
                          : <Badge className="bg-rose-50 text-rose-700 ring-1 ring-inset ring-rose-200"><AlertTriangle size={12} className="mr-1 inline"/> Invalid</Badge>}
                      </td>
                      <td className="px-4 py-2 text-xs text-rose-700">{r.errors?.length ? r.errors.join(" ") : "—"}</td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          </div>
        </>
      )}
    </AppLayout>
  );
}
