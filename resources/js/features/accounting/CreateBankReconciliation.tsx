/**
 * Brixel FactoryOS — New Bank Reconciliation
 */

import { Link, useForm, usePage } from "@inertiajs/react";
import { AppLayout } from "@/components/layout";
import { PageHeader } from "@/components/layout";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { Banknote, ArrowLeft, AlertTriangle } from "lucide-react";
import { cn } from "@/lib/utils";

interface BankAccount {
  id: number;
  code: string;
  name: string;
  book_balance: number;
}

export default function CreateBankReconciliation() {
  const { props } = usePage() as any;
  const accounts: BankAccount[] = props?.accounts || [];
  const suggestedReference: string = props?.suggested_reference || "";
  const defaultDate: string = props?.default_date || new Date().toISOString().slice(0, 10);

  const { data, setData, post, processing, errors } = useForm({
    reference: suggestedReference,
    account_id: accounts.length > 0 ? String(accounts[0].id) : "",
    statement_date: defaultDate,
    book_balance: accounts.length > 0 ? String(accounts[0].book_balance ?? 0) : "0",
    bank_balance: "",
    status: "open",
  });

  const selectAccount = (value: string) => {
    const account = accounts.find((a) => String(a.id) === value);
    setData({
      ...data,
      account_id: value,
      book_balance: account ? String(account.book_balance ?? 0) : data.book_balance,
    });
  };

  const submit = (event: React.FormEvent) => {
    event.preventDefault();
    post("/accounting/bank-reconciliation", { preserveScroll: true });
  };

  return (
    <AppLayout title="New Bank Reconciliation">
      <PageHeader
        title="New Bank Reconciliation"
        subtitle="Compare a bank statement against the book balance of a bank account"
        breadcrumbs={[{ label: "Accounting", href: "/accounting" }, { label: "Bank Reconciliation", href: "/accounting/bank-reconciliation" }, { label: "New" }]}
      >
        <Link href="/accounting/bank-reconciliation" className="inline-flex h-9 items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50"><ArrowLeft size={16} /> Back</Link>
      </PageHeader>

      {accounts.length === 0 && (
        <div className="mb-6 flex items-start gap-2 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
          <AlertTriangle size={18} className="mt-0.5 shrink-0" />
          <div>No active bank accounts found. Flag an account as a bank account in the{" "}
            <Link href="/accounting/chart-of-accounts" className="font-semibold underline">Chart of Accounts</Link> first.
          </div>
        </div>
      )}

      <form onSubmit={submit} className="max-w-2xl space-y-6">
        <Card>
          <CardHeader><CardTitle className="text-base font-semibold text-slate-900">Reconciliation Details</CardTitle></CardHeader>
          <CardContent className="space-y-4">
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Reference</label>
                <Input value={data.reference} onChange={(e) => setData("reference", e.currentTarget.value)} placeholder={suggestedReference || "BR-2026-0001"} className={cn("w-full", errors.reference ? "border-rose-500" : "")} />
                {errors.reference && <p className="text-xs text-rose-600">{errors.reference}</p>}
              </div>
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Statement Date</label>
                <Input type="date" value={data.statement_date} onChange={(e) => setData("statement_date", e.currentTarget.value)} className={cn("w-full", errors.statement_date ? "border-rose-500" : "")} />
                {errors.statement_date && <p className="text-xs text-rose-600">{errors.statement_date}</p>}
              </div>
            </div>

            <div className="space-y-1">
              <label className="text-sm font-medium text-slate-700">Bank Account</label>
              <Select value={data.account_id} onValueChange={selectAccount}>
                <SelectTrigger className="w-full"><SelectValue placeholder="Select bank account" /></SelectTrigger>
                <SelectContent>
                  {accounts.map((a) => (
                    <SelectItem key={a.id} value={String(a.id)}>{a.code} — {a.name}</SelectItem>
                  ))}
                </SelectContent>
              </Select>
              {errors.account_id && <p className="text-xs text-rose-600">{errors.account_id}</p>}
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Book Balance</label>
                <Input type="number" step="0.01" value={data.book_balance} onChange={(e) => setData("book_balance", e.currentTarget.value)} className={cn("w-full", errors.book_balance ? "border-rose-500" : "")} />
                <p className="text-xs text-slate-500">Prefilled from posted ledger activity.</p>
                {errors.book_balance && <p className="text-xs text-rose-600">{errors.book_balance}</p>}
              </div>
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Bank Statement Balance</label>
                <Input type="number" step="0.01" value={data.bank_balance} onChange={(e) => setData("bank_balance", e.currentTarget.value)} placeholder="0.00" className={cn("w-full", errors.bank_balance ? "border-rose-500" : "")} />
                {errors.bank_balance && <p className="text-xs text-rose-600">{errors.bank_balance}</p>}
              </div>
            </div>

            <div className="space-y-1">
              <label className="text-sm font-medium text-slate-700">Status</label>
              <Select value={data.status} onValueChange={(value) => setData("status", value)}>
                <SelectTrigger className="w-full"><SelectValue /></SelectTrigger>
                <SelectContent>
                  <SelectItem value="open">Open</SelectItem>
                  <SelectItem value="pending">Pending</SelectItem>
                  <SelectItem value="reconciled">Reconciled</SelectItem>
                </SelectContent>
              </Select>
            </div>

            <div className="flex items-center justify-between rounded-lg bg-slate-50 px-4 py-3">
              <span className="text-sm font-medium text-slate-600">Difference (bank − book)</span>
              <span className={cn("text-sm font-semibold tabular-nums", Math.abs(Number(data.bank_balance || 0) - Number(data.book_balance || 0)) < 0.01 ? "text-emerald-700" : "text-rose-700")}>
                Rs. {(Number(data.bank_balance || 0) - Number(data.book_balance || 0)).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
              </span>
            </div>
          </CardContent>
        </Card>

        <div className="flex justify-end gap-2">
          <Link href="/accounting/bank-reconciliation" className="inline-flex h-10 items-center rounded-lg border border-slate-200 bg-white px-4 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50">Cancel</Link>
          <Button type="submit" disabled={processing || accounts.length === 0}>{processing ? "Creating…" : <><Banknote size={16} /> Create Reconciliation</>}</Button>
        </div>
      </form>
    </AppLayout>
  );
}
