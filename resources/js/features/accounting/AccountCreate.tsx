/**
 * Brixel FactoryOS — Create Account
 * Add a new account to the chart of accounts.
 */

import { Head, Link, router, useForm } from "@inertiajs/react";
import { AppLayout } from "@/components/layout";
import { PageHeader } from "@/components/layout";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { FileText, ArrowLeft } from "lucide-react";
import { cn } from "@/lib/utils";

export default function AccountCreate() {
  const { data: formData, setData, errors, isPending } =
    useForm<{ code: string; name: string; type: string; allow_direct_posting: boolean; is_cash_account: boolean; is_bank_account: boolean }>({
      code: "",
      name: "",
      type: "asset",
      allow_direct_posting: true,
      is_cash_account: false,
      is_bank_account: false,
    });

  const submit = (event: React.FormEvent) => {
    event.preventDefault();
    router.post("/accounting/chart-of-accounts", formData, {
      onSuccess: () => router.visit("/accounting/chart-of-accounts"),
    });
  };

  return (
    <AppLayout title="Create Account">
      <PageHeader
        title="Create Account"
        subtitle="Add a new account to the chart of accounts"
        breadcrumbs={[{ label: "Accounting", href: "/accounting" }, { label: "Chart of Accounts" }, { label: "New Account" }]}
      >
        <Link href="/accounting/chart-of-accounts" className="inline-flex h-9 items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 text-sm font-medium text-slate-700 shadow-sm transition-colors hover:bg-slate-50">
          <ArrowLeft size={16} /> Back to Chart of Accounts
        </Link>
      </PageHeader>

      <form onSubmit={submit} className="space-y-6 max-w-2xl">
        <Card>
          <CardHeader>
            <CardTitle className="text-base font-semibold text-slate-900">Account Details</CardTitle>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Account Code</label>
                <Input
                  value={formData.code}
                  onChange={(e) => setData("code", e.currentTarget.value)}
                  placeholder="1010"
                  className={cn("w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-factory-500 focus:outline-none focus:ring-1 focus:ring-factory-500", errors.code ? "border-rose-500" : "")}
                />
                {errors.code && <p className="text-xs text-rose-600">{errors.code}</p>}
              </div>
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Account Name</label>
                <Input
                  value={formData.name}
                  onChange={(e) => setData("name", e.currentTarget.value)}
                  placeholder="Cash on Hand"
                  className={cn("w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-factory-500 focus:outline-none focus:ring-1 focus:ring-factory-500", errors.name ? "border-rose-500" : "")}
                />
                {errors.name && <p className="text-xs text-rose-600">{errors.name}</p>}
              </div>
            </div>

            <div className="space-y-1">
              <label className="text-sm font-medium text-slate-700">Account Type</label>
              <Select onValueChange={(value) => setData("type", value)}>
                <SelectTrigger className="w-full">
                  <SelectValue placeholder={formData.type} />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="asset">Asset</SelectItem>
                  <SelectItem value="liability">Liability</SelectItem>
                  <SelectItem value="equity">Equity</SelectItem>
                  <SelectItem value="revenue">Revenue</SelectItem>
                  <SelectItem value="expense">Expense</SelectItem>
                </SelectContent>
              </Select>
              {errors.type && <p className="text-xs text-rose-600">{errors.type}</p>}
            </div>

            <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
              <label className="flex items-center gap-2 cursor-pointer">
                <input
                  type="checkbox"
                  checked={formData.allow_direct_posting}
                  onChange={(e) => setData("allow_direct_posting", e.currentTarget.checked)}
                  className="rounded border-slate-300 text-factory-600 focus:ring-factory-500"
                />
                <span className="text-sm text-slate-700">Allow direct posting</span>
              </label>
              <label className="flex items-center gap-2 cursor-pointer">
                <input
                  type="checkbox"
                  checked={formData.is_cash_account}
                  onChange={(e) => setData("is_cash_account", e.currentTarget.checked)}
                  className="rounded border-slate-300 text-factory-600 focus:ring-factory-500"
                />
                <span className="text-sm text-slate-700">Cash account</span>
              </label>
              <label className="flex items-center gap-2 cursor-pointer">
                <input
                  type="checkbox"
                  checked={formData.is_bank_account}
                  onChange={(e) => setData("is_bank_account", e.currentTarget.checked)}
                  className="rounded border-slate-300 text-factory-600 focus:ring-factory-500"
                />
                <span className="text-sm text-slate-700">Bank account</span>
              </label>
            </div>
          </CardContent>
        </Card>

        <div className="flex justify-end">
          <Button type="submit" disabled={isPending}>
            {isPending ? "Creating…" : <><FileText size={16} /> Create Account</>}
          </Button>
        </div>
      </form>
    </AppLayout>
  );
}
