/**
 * Brixel FactoryOS — Barcode Generator
 * Generate barcodes for items and assets.
 */

import { Head, Link, router, usePage, useForm } from "@inertiajs/react";
import { AppLayout } from "@/components/layout";
import { PageHeader } from "@/components/layout";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Barcode, ArrowLeft } from "lucide-react";
import { cn } from "@/lib/utils";

export default function BarcodeGenerator() {
  const { data: formData, setData, errors, isPending } =
    useForm<{
      barcode_data: string;
      barcode_type: string;
      width: string;
      height: string;
      format: string;
    }>({
      barcode_data: "",
      barcode_type: "code128",
      width: "200",
      height: "100",
      format: "png",
    });

  const BARCODE_TYPES = [
    { value: "code128", label: "Code 128" },
    { value: "code39", label: "Code 39" },
    { value: "ean13", label: "EAN-13" },
    { value: "ean8", label: "EAN-8" },
    { value: "upc", label: "UPC" },
    { value: "qr", label: "QR Code" },
  ];

  const FORMATS = [
    { value: "png", label: "PNG" },
    { value: "svg", label: "SVG" },
    { value: "jpeg", label: "JPEG" },
  ];

  const submit = (event: React.FormEvent) => {
    event.preventDefault();
    router.post("/barcode-generator/generate", formData, {
      onSuccess: () => {},
    });
  };

  return (
    <AppLayout title="Barcode Generator">
      <PageHeader
        title="Barcode Generator"
        subtitle="Generate barcodes for items and assets"
        breadcrumbs={[
          { label: "Tools", href: "/barcode-generator" },
          { label: "Barcode Generator" },
        ]}
        actions={
          <Link
            href="/barcode-generator"
            className="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-slate-200 bg-white px-3 text-sm font-medium text-slate-700 shadow-sm transition-colors hover:bg-slate-50"
          >
            <ArrowLeft size={14} /> Back to Generator
          </Link>
        }
      />

      <div className="max-w-2xl">
        <Card>
          <CardHeader>
            <CardTitle className="text-base font-semibold text-slate-900">Barcode Generator</CardTitle>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Barcode Data</label>
                <Input
                  value={formData.barcode_data}
                  onChange={(e) => setData("barcode_data", e.currentTarget.value)}
                  placeholder="Enter data to encode"
                  className={cn("w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-factory-500 focus:outline-none focus:ring-1 focus:ring-factory-500", errors.barcode_data ? "border-rose-500" : "")}
                />
                {errors.barcode_data && <p className="text-xs text-rose-600">{errors.barcode_data}</p>}
              </div>
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Barcode Type</label>
                <Select onValueChange={(value) => setData("barcode_type", value)}>
                  <SelectTrigger className="w-full">
                    <SelectValue placeholder={formData.barcode_type} />
                  </SelectTrigger>
                  <SelectContent>
                    {BARCODE_TYPES.map((t) => (
                      <SelectItem key={t.value} value={t.value}>{t.label}</SelectItem>
                    ))}
                  </SelectContent>
                </Select>
                {errors.barcode_type && <p className="text-xs text-rose-600">{errors.barcode_type}</p>}
              </div>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Width (px)</label>
                <Input
                  type="number"
                  value={formData.width}
                  onChange={(e) => setData("width", e.currentTarget.value)}
                  placeholder="200"
                  className={cn("w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-factory-500 focus:outline-none focus:ring-1 focus:ring-factory-500", errors.width ? "border-rose-500" : "")}
                />
                {errors.width && <p className="text-xs text-rose-600">{errors.width}</p>}
              </div>
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Height (px)</label>
                <Input
                  type="number"
                  value={formData.height}
                  onChange={(e) => setData("height", e.currentTarget.value)}
                  placeholder="100"
                  className={cn("w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-factory-500 focus:outline-none focus:ring-1 focus:ring-factory-500", errors.height ? "border-rose-500" : "")}
                />
                {errors.height && <p className="text-xs text-rose-600">{errors.height}</p>}
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
                    <Barcode size={16} />
                    Generate Barcode
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
              <div className="w-32 h-16 border border-slate-300 flex items-center justify-center text-xs text-slate-400">
                Barcode Preview
              </div>
              <p className="mt-2 text-sm text-slate-500">Generated barcode will appear here</p>
            </div>
          </CardContent>
        </Card>
      </div>
    </AppLayout>
  );
}
