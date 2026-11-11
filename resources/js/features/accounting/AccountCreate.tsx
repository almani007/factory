/**
 * Brixel FactoryOS — Create Account
 * Add a new account to the chart of accounts.
 */
import { Link, router, useForm, usePage } from "@inertiajs/react";
import { AppLayout } from "@/components/layout";
import { PageHeader } from "@/components/layout";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { FileText, ArrowLeft } from "lucide-react";
import { cn } from "@/lib/utils";

export default function AccountCreate() {
  const { props } = usePage() as any;
  const parents = props?.parents || [];
  const groups = props?.groups || [];
  const prefill = props?.prefill || null;
  const isDuplicate = Boolean(prefill?.code);

  const { data, setData, errors, processing, post } = useForm({
    code: prefill?.code || "",
    name: prefill?.name || "",
    type: prefill?.type || "asset",
    account_group_id: prefill?.account_group_id ? String(prefill.account_group_id) : "",
    parent_id: prefill?.parent_id ? String(prefill.parent_id) : "",
    allow_direct_posting: prefill?.allow_direct_posting !== false,
    is_cash_account: Boolean(prefill?.is_cash_account),
    is_bank_account: Boolean(prefill?.is_bank_account),
    is_active: prefill?.is_active !== false,
  });

  const submit = (event: React.FormEvent) => {
    event.preventDefault();
    post("/accounting/chart-of-accounts", {
      data: {
        ...data,
        account_group_id: data.account_group_id || null,
        parent_id: data.parent_id || null,
      } as any,
    });
  };

  return (
    <AppLayout title={isDuplicate ? "Duplicate Account" : "Create Account"}>
      <PageHeader
        title={isDuplicate ? "Duplicate Account" : "Create Account"}
        subtitle={isDuplicate ? "Create a new account from an existing template" : prefill ? "Create a child account under the selected parent" : "Add a new account to the chart of accounts"}
        breadcrumbs={[{ label: "Accounting", href: "/accounting" }, { label: "Chart of Accounts", href: "/accounting/chart-of-accounts" }, { label: "New Account" }]}
      >
        <Link href="/accounting/chart-of-accounts" className="inline-flex h-9 items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50">
          <ArrowLeft size={16} /> Back to Chart of Accounts
        </Link>
      </PageHeader>

      <form onSubmit={submit} className="space-y-6 max-w-3xl">
        <Card>
          <CardHeader>
            <CardTitle className="text-base font-semibold text-slate-900">Account Details</CardTitle>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Account Code</label>
                <Input value={data.code} onChange={(e) => setData("code", e.currentTarget.value)} placeholder="e.g. 1010" className={cn(errors.code ? "border-rose-500" : "")} />
                {errors.code && <p className="text-xs text-rose-600">{errors.code}</p>}
              </div>
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Account Name</label>
                <Input value={data.name} onChange={(e) => setData("name", e.currentTarget.value)} placeholder="e.g. Cash on Hand" className={cn(errors.name ? "border-rose-500" : "")} />
                {errors.name && <p className="text-xs text-rose-600">{errors.name}</p>}
              </div>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Account Type</label>
                <Select value={data.type} onValueChange={(value) => setData("type", value)}>
                  <SelectTrigger className="w-full"><SelectValue /></SelectTrigger>
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
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Parent Account</label>
                <Select value={data.parent_id || "none"} onValueChange={(v) => setData("parent_id", v === "none" ? "" : v)}>
                  <SelectTrigger className="w-full"><SelectValue placeholder="None" /></SelectTrigger>
                  <SelectContent>
                    <SelectItem value="none">None</SelectItem>
                    {parents.map((p: any) => <SelectItem key={p.id} value={String(p.id)}>{p.code} — {p.name}</SelectItem>)}
                  </SelectContent>
                </Select>
                {errors.parent_id && <p className="text-xs text-rose-600">{errors.parent_id}</p>}
              </div>
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Account Group</label>
                <Select value={data.account_group_id || "none"} onValueChange={(v) => setData("account_group_id", v === "none" ? "" : v)}>
                  <SelectTrigger className="w-full"><SelectValue placeholder="None" /></SelectTrigger>
                  <SelectContent>
                    <SelectItem value="none">None</SelectItem>
                    {groups.map((g: any) => <SelectItem key={g.id} value={String(g.id)}>{g.code ? `${g.code} — ` : ""}{g.name}</SelectItem>)}
                  </SelectContent>
                </Select>
                {errors.account_group_id && <p className="text-xs text-rose-600">{errors.account_group_id}</p>}
              </div>
            </div>

            <div className="grid grid-cols-2 md:grid-cols-4 gap-3">
              <label className="flex items-center gap-2 cursor-pointer text-sm text-slate-700"><input type="checkbox" checked={data.allow_direct_posting} onChange={e=>setData("allow_direct_posting", e.target.checked)} className="rounded border-slate-300 text-factory-600 focus:ring-factory-500" /> Direct posting</label>
              <label className="flex items-center gap-2 cursor-pointer text-sm text-slate-700"><input type="checkbox" checked={data.is_cash_account} onChange={e=>setData("is_cash_account", e.target.checked)} className="rounded border-slate-300 text-factory-600 focus:ring-factory-500" /> Cash account</label>
              <label className="flex items-center gap-2 cursor-pointer text-sm text-slate-700"><input type="checkbox" checked={data.is_bank_account} onChange={e=>setData("is_bank_account", e.target.checked)} className="rounded border-slate-300 text-factory-600 focus:ring-factory-500" /> Bank account</label>
              <label className="flex items-center gap-2 cursor-pointer text-sm text-slate-700"><input type="checkbox" checked={data.is_active} onChange={e=>setData("is_active", e.target.checked)} className="rounded border-slate-300 text-factory-600 focus:ring-factory-500" /> Active</label>
            </div>
          </CardContent>
        </Card>

        <div className="flex justify-end gap-2">
          <Link href="/accounting/chart-of-accounts" className="inline-flex h-9 items-center rounded-lg border border-slate-200 bg-white px-3 text-sm font-medium text-slate-700 hover:bg-slate-50">Cancel</Link>
          <Button type="submit" disabled={processing}>
            {processing ? "Creating…" : <><FileText size={16} /> Create Account</>}
          </Button>
        </div>
      </form>
    </AppLayout>
  );
}
