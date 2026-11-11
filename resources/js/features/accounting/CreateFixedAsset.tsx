/**
 * Brixel FactoryOS — Create Fixed Asset
 * Add a new fixed asset to the register.
 */

import { Link, usePage, useForm } from "@inertiajs/react";
import { AppLayout } from "@/components/layout";
import { PageHeader } from "@/components/layout";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { Building2, ArrowLeft } from "lucide-react";
import { cn } from "@/lib/utils";

interface Account { id: number; code: string; name: string; }

export default function CreateFixedAsset() {
  const { props: data } = usePage() as any;
  const accounts: Account[] = data?.accounts || [];

  const { data: formData, setData, errors, processing, post } =
    useForm<{ asset_code: string; asset_name: string; asset_category: string; account_id: string; purchase_date: string; purchase_cost: string; salvage_value: string; useful_life_years: string; location: string; status: string }>({
      asset_code: "",
      asset_name: "",
      asset_category: "",
      account_id: "",
      purchase_date: "",
      purchase_cost: "",
      salvage_value: "",
      useful_life_years: "5",
      location: "",
      status: "active",
    });

  const submit = (event: React.FormEvent) => {
    event.preventDefault();
    post("/accounting/fixed-assets", { preserveScroll: true });
  };

  return (
    <AppLayout title="Create Fixed Asset">
      <PageHeader
        title="Create Fixed Asset"
        subtitle="Add a new fixed asset to the register"
        breadcrumbs={[{ label: "Accounting", href: "/accounting" }, { label: "Fixed Assets" }, { label: "New Asset" }]}
      >
        <Link href="/accounting/fixed-assets" className="inline-flex h-9 items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 text-sm font-medium text-slate-700 shadow-sm transition-colors hover:bg-slate-50">
          <ArrowLeft size={16} /> Back to Fixed Assets
        </Link>
      </PageHeader>

      <form onSubmit={submit} className="space-y-6 max-w-2xl">
        <Card>
          <CardHeader>
            <CardTitle className="text-base font-semibold text-slate-900">Asset Details</CardTitle>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Asset Code</label>
                <Input
                  value={formData.asset_code}
                  onChange={(e) => setData("asset_code", e.currentTarget.value)}
                  placeholder="FA-0001"
                  className={cn("w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-factory-500 focus:outline-none focus:ring-1 focus:ring-factory-500", errors.asset_code ? "border-rose-500" : "")}
                />
                {errors.asset_code && <p className="text-xs text-rose-600">{errors.asset_code}</p>}
              </div>
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Asset Name</label>
                <Input
                  value={formData.asset_name}
                  onChange={(e) => setData("asset_name", e.currentTarget.value)}
                  placeholder="CNC Machine"
                  className={cn("w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-factory-500 focus:outline-none focus:ring-1 focus:ring-factory-500", errors.asset_name ? "border-rose-500" : "")}
                />
                {errors.asset_name && <p className="text-xs text-rose-600">{errors.asset_name}</p>}
              </div>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Category</label>
                <Input
                  value={formData.asset_category}
                  onChange={(e) => setData("asset_category", e.currentTarget.value)}
                  placeholder="Machinery"
                  className="w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-factory-500 focus:outline-none focus:ring-1 focus:ring-factory-500"
                />
              </div>
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Account</label>
                <Select onValueChange={(value) => setData("account_id", value)}>
                  <SelectTrigger className="w-full">
                    <SelectValue placeholder="Select GL account" />
                  </SelectTrigger>
                  <SelectContent>
                    {accounts.map((a) => (
                      <SelectItem key={a.id} value={String(a.id)}>{a.code} — {a.name}</SelectItem>
                    ))}
                  </SelectContent>
                </Select>
                {errors.account_id && <p className="text-xs text-rose-600">{errors.account_id}</p>}
              </div>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Purchase Date</label>
                <Input
                  type="date"
                  value={formData.purchase_date}
                  onChange={(e) => setData("purchase_date", e.currentTarget.value)}
                  className={cn("w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-factory-500 focus:outline-none focus:ring-1 focus:ring-factory-500", errors.purchase_date ? "border-rose-500" : "")}
                />
                {errors.purchase_date && <p className="text-xs text-rose-600">{errors.purchase_date}</p>}
              </div>
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Purchase Cost</label>
                <Input
                  type="number"
                  step="0.01"
                  value={formData.purchase_cost}
                  onChange={(e) => setData("purchase_cost", e.currentTarget.value)}
                  placeholder="0.00"
                  className={cn("w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-factory-500 focus:outline-none focus:ring-1 focus:ring-factory-500", errors.purchase_cost ? "border-rose-500" : "")}
                />
                {errors.purchase_cost && <p className="text-xs text-rose-600">{errors.purchase_cost}</p>}
              </div>
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Status</label>
                <Select value={formData.status} onValueChange={(value) => setData("status", value)}>
                  <SelectTrigger className="w-full">
                    <SelectValue placeholder={formData.status} />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="active">Active</SelectItem>
                    <SelectItem value="disposed">Disposed</SelectItem>
                    <SelectItem value="retired">Retired</SelectItem>
                  </SelectContent>
                </Select>
                {errors.status && <p className="text-xs text-rose-600">{errors.status}</p>}
              </div>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Salvage Value</label>
                <Input
                  type="number"
                  step="0.01"
                  value={formData.salvage_value}
                  onChange={(e) => setData("salvage_value", e.currentTarget.value)}
                  placeholder="0.00"
                  className={cn("w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-factory-500 focus:outline-none focus:ring-1 focus:ring-factory-500", errors.salvage_value ? "border-rose-500" : "")}
                />
                {errors.salvage_value && <p className="text-xs text-rose-600">{errors.salvage_value}</p>}
              </div>
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Useful Life (years)</label>
                <Input
                  type="number"
                  min="1"
                  max="60"
                  value={formData.useful_life_years}
                  onChange={(e) => setData("useful_life_years", e.currentTarget.value)}
                  className={cn("w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-factory-500 focus:outline-none focus:ring-1 focus:ring-factory-500", errors.useful_life_years ? "border-rose-500" : "")}
                />
                {errors.useful_life_years && <p className="text-xs text-rose-600">{errors.useful_life_years}</p>}
              </div>
            </div>

            <div className="space-y-1">
              <label className="text-sm font-medium text-slate-700">Location</label>
              <Input
                value={formData.location}
                onChange={(e) => setData("location", e.currentTarget.value)}
                placeholder="Shop Floor A"
                className="w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-factory-500 focus:outline-none focus:ring-1 focus:ring-factory-500"
              />
            </div>
          </CardContent>
        </Card>

        <div className="flex justify-end">
          <Button type="submit" disabled={processing}>
            {processing ? "Creating…" : <><Building2 size={16} /> Create Asset</>}
          </Button>
        </div>
      </form>
    </AppLayout>
  );
}
