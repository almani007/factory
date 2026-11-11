/**
 * Brixel FactoryOS — Create Document
 * Upload a new controlled document.
 */

import { Head, Link, router, usePage, useForm } from "@inertiajs/react";
import { AppLayout } from "@/components/layout";
import { PageHeader } from "@/components/layout";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { Copy, ArrowLeft } from "lucide-react";
import { cn } from "@/lib/utils";

interface Employee { id: number; employee_code: string; first_name: string; last_name: string; }

export default function CreateDocument() {
  const { props: data } = usePage() as any;
  const employees: Employee[] = data?.employees || [];

  const { data: formData, setData, errors, isPending } =
    useForm<{ document_number: string; title: string; category: string; file_name: string; version: string; status: string; upload_date: string; expiry_date: string; uploaded_by: string }>({
      document_number: "",
      title: "",
      category: "procedure",
      file_name: "",
      version: "1.0",
      status: "draft",
      upload_date: "",
      expiry_date: "",
      uploaded_by: "",
    });

  const CATEGORIES = [
    { value: "procedure", label: "Procedure" },
    { value: "work_instruction", label: "Work Instruction" },
    { value: "policy", label: "Policy" },
    { value: "specification", label: "Specification" },
    { value: "record", label: "Record" },
    { value: "form", label: "Form" },
  ];

  const submit = (event: React.FormEvent) => {
    event.preventDefault();
    router.post("/documents", formData, {
      onSuccess: () => router.visit("/documents"),
    });
  };

  return (
    <AppLayout title="Create Document">
      <PageHeader
        title="Create Document"
        subtitle="Upload a new controlled document"
        breadcrumbs={[{ label: "Documents", href: "/documents" }, { label: "New Document" }]}
      >
        <Link href="/documents" className="inline-flex h-9 items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 text-sm font-medium text-slate-700 shadow-sm transition-colors hover:bg-slate-50">
          <ArrowLeft size={16} /> Back to Documents
        </Link>
      </PageHeader>

      <form onSubmit={submit} className="space-y-6 max-w-2xl">
        <Card>
          <CardHeader>
            <CardTitle className="text-base font-semibold text-slate-900">Document Details</CardTitle>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Document Number</label>
                <Input
                  value={formData.document_number}
                  onChange={(e) => setData("document_number", e.currentTarget.value)}
                  placeholder="DOC-2024-0001"
                  className={cn("w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-factory-500 focus:outline-none focus:ring-1 focus:ring-factory-500", errors.document_number ? "border-rose-500" : "")}
                />
                {errors.document_number && <p className="text-xs text-rose-600">{errors.document_number}</p>}
              </div>
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Title</label>
                <Input
                  value={formData.title}
                  onChange={(e) => setData("title", e.currentTarget.value)}
                  placeholder="Document title"
                  className={cn("w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-factory-500 focus:outline-none focus:ring-1 focus:ring-factory-500", errors.title ? "border-rose-500" : "")}
                />
                {errors.title && <p className="text-xs text-rose-600">{errors.title}</p>}
              </div>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Category</label>
                <Select onValueChange={(value) => setData("category", value)}>
                  <SelectTrigger className="w-full">
                    <SelectValue placeholder={formData.category} />
                  </SelectTrigger>
                  <SelectContent>
                    {CATEGORIES.map((c) => (
                      <SelectItem key={c.value} value={c.value}>{c.label}</SelectItem>
                    ))}
                  </SelectContent>
                </Select>
                {errors.category && <p className="text-xs text-rose-600">{errors.category}</p>}
              </div>
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Version</label>
                <Input
                  value={formData.version}
                  onChange={(e) => setData("version", e.currentTarget.value)}
                  placeholder="1.0"
                  className="w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-factory-500 focus:outline-none focus:ring-1 focus:ring-factory-500"
                />
              </div>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Upload Date</label>
                <Input
                  type="date"
                  value={formData.upload_date}
                  onChange={(e) => setData("upload_date", e.currentTarget.value)}
                  className={cn("w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-factory-500 focus:outline-none focus:ring-1 focus:ring-factory-500", errors.upload_date ? "border-rose-500" : "")}
                />
                {errors.upload_date && <p className="text-xs text-rose-600">{errors.upload_date}</p>}
              </div>
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Expiry Date</label>
                <Input
                  type="date"
                  value={formData.expiry_date}
                  onChange={(e) => setData("expiry_date", e.currentTarget.value)}
                  className="w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-factory-500 focus:outline-none focus:ring-1 focus:ring-factory-500"
                />
              </div>
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Status</label>
                <Select onValueChange={(value) => setData("status", value)}>
                  <SelectTrigger className="w-full">
                    <SelectValue placeholder={formData.status} />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="draft">Draft</SelectItem>
                    <SelectItem value="active">Active</SelectItem>
                    <SelectItem value="archived">Archived</SelectItem>
                  </SelectContent>
                </Select>
                {errors.status && <p className="text-xs text-rose-600">{errors.status}</p>}
              </div>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">File Name</label>
                <Input
                  value={formData.file_name}
                  onChange={(e) => setData("file_name", e.currentTarget.value)}
                  placeholder="document.pdf"
                  className="w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-factory-500 focus:outline-none focus:ring-1 focus:ring-factory-500"
                />
              </div>
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Uploaded By</label>
                <Select onValueChange={(value) => setData("uploaded_by", value)}>
                  <SelectTrigger className="w-full">
                    <SelectValue placeholder="Select employee" />
                  </SelectTrigger>
                  <SelectContent>
                    {employees.map((e) => (
                      <SelectItem key={e.id} value={String(e.id)}>{e.employee_code} — {e.first_name} {e.last_name}</SelectItem>
                    ))}
                  </SelectContent>
                </Select>
                {errors.uploaded_by && <p className="text-xs text-rose-600">{errors.uploaded_by}</p>}
              </div>
            </div>
          </CardContent>
        </Card>

        <div className="flex justify-end">
          <Button type="submit" disabled={isPending}>
            {isPending ? "Uploading…" : <><Copy size={16} /> Create Document</>}
          </Button>
        </div>
      </form>
    </AppLayout>
  );
}
