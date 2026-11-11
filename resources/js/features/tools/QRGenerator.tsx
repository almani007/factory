/**
 * Brixel FactoryOS — QR Generator
 * Generate QR codes for items and assets.
 */

import { Head, Link, router, usePage, useForm } from "@inertiajs/react";
import { AppLayout } from "@/components/layout";
import { PageHeader } from "@/components/layout";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { QrCode, ArrowLeft } from "lucide-react";
import { cn } from "@/lib/utils";

export default function QRGenerator() {
  const { data: formData, setData, errors, isPending } =
    useForm<{
      qr_data: string;
      qr_type: string;
      size: string;
      format: string;
    }>({
      qr_data: "",
      qr_type: "text",
      size: "200",
      format: "png",
    });

    const QR_TYPES = [
    { value: "text", label: "Text" },
    { value: "url", label: "URL" },
    { value: "email", label: "Email" },
    { value: "phone", label: "Phone" },
    { value: "sms", label: "SMS" },
    { value: "wifi", label: "WiFi" },
  ];

  const FORMATS = [
    { value: "png", label: "PNG" },
    { value: "svg", label: "SVG" },
    { value: "jpeg", label: "JPEG" },
  ];

  const submit = (event: React.FormEvent) => {
    event.preventDefault();
    router.post("/qr-generator/generate", formData, {
      onSuccess: () => {},
    });
  };

  return (
    <AppLayout title="QR Code Generator">
      <PageHeader
        title="QR Code Generator"
        subtitle="Generate QR codes for items and assets"
        breadcrumbs={[
          { label: "Tools", href: "/qr-generator" },
          { label: "QR Generator" },
        ]}
        actions={
          <Link
            href="/qr-generator"
            className="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-slate-200 bg-white px-3 text-sm font-medium text-slate-700 shadow-sm transition-colors hover:bg-slate-50"
          >
            <ArrowLeft size={14} /> Back to Generator
          </Link>
        }
      />

      <div className="max-w-2xl">
        <Card>
          <CardHeader>
            <CardTitle className="text-base font-semibold text-slate-900">QR Code Generator</CardTitle>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">QR Data</label>
                <Input
                  value={formData.qr_data}
                  onChange={(e) => setData("qr_data", e.currentTarget.value)}
                  placeholder="Enter data to encode"
                  className={cn("w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-factory-500 focus:outline-none focus:ring-1 focus:ring-factory-500", errors.qr_data ? "border-rose-500" : "")}
                />
                {errors.qr_data && <p className="text-xs text-rose-600">{errors.qr_data}</p>}
              </div>
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">QR Type</label>
                <Select onValueChange={(value) => setData("qr_type", value)}>
                  <SelectTrigger className="w-full">
                    <SelectValue placeholder={formData.qr_type} />
                  </SelectTrigger>
                  <SelectContent>
                    {QR_TYPES.map((t) => (
                      <SelectItem key={t.value} value={t.value}>{t.label}</SelectItem>
                    ))}
                  </SelectContent>
                </Select>
                {errors.qr_type && <p className="text-xs text-rose-600">{errors.qr_type}</p>}
              </div>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Size (px)</label>
                <Input
                  type="number"
                  value={formData.size}
                  onChange={(e) => setData("size", e.currentTarget.value)}
                  placeholder="200"
                  className={cn("w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-factory-500 focus:outline-none focus:ring-1 focus:ring-factory-500", errors.size ? "border-rose-500" : "")}
                />
                {errors.size && <p className="text-xs text-rose-600">{errors.size}</p>}
              </div>
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Format</label>
                <Select onValueChange={(value) => setData("format", value)}>
                  <SelectTrigger className="w-full">
                    <SelectValue placeholder={formData.format} />
                  </SelectTrigger>
                  <SelectContent>
                    {FORMATS.map((f) => (
                      <SelectItem key={f.value} value={f.value}>{f.label}</SelectItem>
                    ))}
                  </SelectContent>
                </Select>
                {errors.format && <p className="text-xs text-rose-600">{errors.format}</p>}
              </div>
            </div>

            <div className="mt-6 flex items-center justify-between">
              <Button type="submit" disabled={isPending}>
                {isPending ? (
                  "Generating…"
                ) : (
                  <>
                    <QrCode size={16} />
                    Generate QR Code
                  </>
                )}
              </Button>
            </div>
          </CardContent>
        </Card>

        <Card className="mt-6">
          <CardHeader>
            <CardTitle className="text-base font-semibold text-slate-900">Preview</CardTitle>
          </CardHeader>
          <CardContent className="p-6 flex items-center justify-center h-64 bg-slate-50 rounded-lg border border-slate-200">
            <div className="flex flex-col items-center justify-center">
              <div className="w-32 h-32 border border-slate-300 flex items-center justify-center text-xs text-slate-400">
                QR Code Preview
              </div>
              <p className="mt-2 text-sm text-slate-500">Generated QR code will appear here</p>
            </div>
          </CardContent>
        </Card>
      </div>
    </AppLayout>
  );
}
