/**
 * Brixel FactoryOS — Create Journal Entry
 * Double-entry journal entry form with dynamic line items.
 */

import { Link, useForm, usePage } from "@inertiajs/react";
import { AppLayout } from "@/components/layout";
import { PageHeader } from "@/components/layout";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Minus, Plus, Scale, AlertTriangle } from "lucide-react";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { cn } from "@/lib/utils";

interface AccountOption {
  id: string;
  code: string;
  name: string;
  type: string;
}

interface JournalLine {
  id: number;
  account_id: string;
  account_name: string;
  debit_amount: string;
  credit_amount: string;
  description: string;
}

interface FormData {
  reference: string;
  document_date: string;
  posting_date: string;
  accounting_book_id: string;
  currency: string;
  status: string;
  description: string;
  lines: JournalLine[];
}

const EMPTY_LINE: JournalLine = {
  id: 0,
  account_id: "",
  account_name: "",
  debit_amount: "",
  credit_amount: "",
  description: "",
};

const TYPE_LABEL: Record<string, string> = {
  asset: "Asset",
  liability: "Liability",
  equity: "Equity",
  revenue: "Revenue",
  expense: "Expense",
};

export default function JournalEntryCreate() {
  const { props } = usePage() as any;
  const accounts: AccountOption[] = props?.accounts || [];
  const books: any[] = props?.books || [];
  const periods: any[] = props?.periods || [];
  const suggestedReference: string = props?.suggested_reference || "";
  const defaultDate: string = props?.default_date || new Date().toISOString().slice(0, 10);

  const { data: formData, setData, post, processing, errors } = useForm<FormData>({
    reference: suggestedReference,
    document_date: defaultDate,
    posting_date: defaultDate,
    accounting_book_id: books.length > 0 ? String(books[0].id) : "",
    currency: books.length > 0 ? books[0].base_currency || "PKR" : "PKR",
    status: "posted",
    description: "",
    lines: [{ ...EMPTY_LINE, id: Date.now() }, { ...EMPTY_LINE, id: Date.now() + 1 }],
  });

  const addLine = () => {
    setData("lines", [...formData.lines, { ...EMPTY_LINE, id: Date.now() }]);
  };

  const removeLine = (index: number) => {
    if (formData.lines.length > 1) {
      setData("lines", formData.lines.filter((_, i) => i !== index));
    }
  };

  const updateLine = (index: number, field: keyof JournalLine, value: string) => {
    const lines = [...formData.lines];
    lines[index] = { ...lines[index], [field]: value };
    if (field === "account_id") {
      const account = accounts.find((a) => String(a.id) === String(value));
      lines[index].account_name = account ? `${account.code} — ${account.name}` : "";
    }
    setData("lines", lines);
  };

  const totalDebit = formData.lines.reduce((sum, l) => sum + Number(l.debit_amount || 0), 0);
  const totalCredit = formData.lines.reduce((sum, l) => sum + Number(l.credit_amount || 0), 0);
  const difference = Math.round((totalDebit - totalCredit) * 10000) / 10000;
  const isBalanced = Math.abs(difference) < 0.0001 && totalDebit > 0;
  const selectedBook = books.find((b) => String(b.id) === String(formData.accounting_book_id));
  const activePeriod = periods.find(
    (p) => formData.posting_date >= p.start_date && formData.posting_date <= p.end_date
  );

  const submit = (event: React.FormEvent) => {
    event.preventDefault();
    post("/accounting/journal-entries", {
      preserveScroll: true,
    });
  };

  return (
    <AppLayout title="Create Journal Entry">
      <PageHeader
        title="Create Journal Entry"
        subtitle="Post a balanced double-entry journal entry to the ledger"
        breadcrumbs={[{ label: "Accounting", href: "/accounting" }, { label: "Journal Entries", href: "/accounting" }, { label: "Create" }]}
      >
        <Link
          href="/accounting"
          className="inline-flex h-9 items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 text-sm font-medium text-slate-700 shadow-sm transition-colors hover:bg-slate-50"
        >
          ← Back to Journal Entries
        </Link>
      </PageHeader>

      {accounts.length === 0 && (
        <div className="mb-6 flex items-start gap-3 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
          <AlertTriangle size={18} className="mt-0.5 shrink-0" />
          <div>
            No accounts allow direct posting. Add an active account with “Allow direct posting” enabled in the{" "}
            <Link href="/accounting/chart-of-accounts" className="font-semibold underline">Chart of Accounts</Link> before creating entries.
          </div>
        </div>
      )}

      <form onSubmit={submit} className="space-y-6">
        <Card>
          <CardHeader>
            <CardTitle className="text-base font-semibold text-slate-900">Entry Details</CardTitle>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Reference</label>
                <Input
                  value={formData.reference}
                  onChange={(e) => setData("reference", e.currentTarget.value)}
                  placeholder={suggestedReference || "JE-2024-0001"}
                  className={cn("w-full rounded-md border bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:outline-none focus:ring-1", errors.reference ? "border-rose-500 focus:ring-rose-500" : "border-slate-300 focus:border-factory-500 focus:ring-factory-500")}
                />
                <p className="text-xs text-slate-500">Leave blank to auto-generate a unique reference.</p>
                {errors.reference && <p className="text-xs text-rose-600">{errors.reference}</p>}
              </div>
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Document Date</label>
                <Input
                  type="date"
                  value={formData.document_date}
                  onChange={(e) => setData("document_date", e.currentTarget.value)}
                  className={cn("w-full rounded-md border bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:outline-none focus:ring-1", errors.document_date ? "border-rose-500 focus:ring-rose-500" : "border-slate-300 focus:border-factory-500 focus:ring-factory-500")}
                />
                {errors.document_date && <p className="text-xs text-rose-600">{errors.document_date}</p>}
              </div>
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Posting Date</label>
                <Input
                  type="date"
                  value={formData.posting_date}
                  onChange={(e) => setData("posting_date", e.currentTarget.value)}
                  className={cn("w-full rounded-md border bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:outline-none focus:ring-1", errors.posting_date ? "border-rose-500 focus:ring-rose-500" : "border-slate-300 focus:border-factory-500 focus:ring-factory-500")}
                />
                <p className="text-xs text-slate-500">{activePeriod ? `Period: ${activePeriod.name}` : "No open period covers this date."}</p>
                {errors.posting_date && <p className="text-xs text-rose-600">{errors.posting_date}</p>}
              </div>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Accounting Book</label>
                <Select
                  value={formData.accounting_book_id}
                  onValueChange={(value) => {
                    setData("accounting_book_id", value);
                    const book = books.find((b) => String(b.id) === String(value));
                    if (book?.base_currency) setData("currency", book.base_currency);
                  }}
                >
                  <SelectTrigger className="w-full"><SelectValue placeholder={books.length ? "Select book" : "Default book"} /></SelectTrigger>
                  <SelectContent>
                    {books.map((b) => (
                      <SelectItem key={b.id} value={String(b.id)}>{b.name} ({b.code})</SelectItem>
                    ))}
                  </SelectContent>
                </Select>
                {books.length === 0 && <p className="text-xs text-slate-500">A default book will be created automatically.</p>}
              </div>
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Currency</label>
                <Input
                  value={formData.currency}
                  maxLength={3}
                  onChange={(e) => setData("currency", e.currentTarget.value.toUpperCase())}
                  className="w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm uppercase text-slate-900 shadow-sm focus:border-factory-500 focus:outline-none focus:ring-1 focus:ring-factory-500"
                />
                <p className="text-xs text-slate-500">{selectedBook ? `Base: ${selectedBook.base_currency}` : "Base currency"}</p>
              </div>
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Status</label>
                <Select value={formData.status} onValueChange={(value) => setData("status", value)}>
                  <SelectTrigger className="w-full"><SelectValue /></SelectTrigger>
                  <SelectContent>
                    <SelectItem value="posted">Post immediately</SelectItem>
                    <SelectItem value="draft">Save as draft</SelectItem>
                  </SelectContent>
                </Select>
              </div>
              <div className="space-y-1 md:col-span-1">
                <label className="text-sm font-medium text-slate-700">Description</label>
                <Input
                  value={formData.description}
                  onChange={(e) => setData("description", e.currentTarget.value)}
                  placeholder="Enter description"
                  className="w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-factory-500 focus:outline-none focus:ring-1 focus:ring-factory-500"
                />
                {errors.description && <p className="text-xs text-rose-600">{errors.description}</p>}
              </div>
            </div>

            <div className="space-y-1">
              <label className="text-sm font-medium text-slate-700">Journal Lines</label>
              <p className="text-xs text-slate-500">Enter at least two lines. Each line has either a debit or a credit. Totals must balance.</p>
              <div className="border border-slate-200 rounded-lg overflow-hidden">
                {formData.lines.map((line, index) => (
                  <div
                    key={line.id}
                    className={`border-b border-slate-100 last:border-b-0 flex flex-col gap-3 p-4 lg:flex-row lg:items-center ${index % 2 === 0 ? "bg-slate-50" : "bg-white"}`}
                  >
                    <div className="flex-1">
                      <Select value={line.account_id} onValueChange={(value) => updateLine(index, "account_id", value)}>
                        <SelectTrigger className="w-full">
                          <SelectValue placeholder="Select account" />
                        </SelectTrigger>
                        <SelectContent>
                          {accounts.map((acc) => (
                            <SelectItem key={acc.id} value={String(acc.id)}>
                              {acc.code} — {acc.name} ({TYPE_LABEL[acc.type] || acc.type})
                            </SelectItem>
                          ))}
                        </SelectContent>
                      </Select>
                      <p className="text-xs text-slate-500 mt-1">
                        {line.account_name || "Choose an active posting account"}
                      </p>
                    </div>
                    <div className="flex flex-wrap items-center gap-2">
                      <div className="w-28">
                        <label className="block text-[10px] font-medium uppercase tracking-wider text-slate-400 mb-1">Debit</label>
                        <Input
                          type="number"
                          min="0"
                          step="0.01"
                          value={line.debit_amount}
                          onChange={(e) => {
                            updateLine(index, "debit_amount", e.currentTarget.value);
                            if (Number(e.currentTarget.value) > 0 && line.credit_amount) {
                              updateLine(index, "credit_amount", "");
                            }
                          }}
                          placeholder="0.00"
                        />
                      </div>
                      <div className="w-28">
                        <label className="block text-[10px] font-medium uppercase tracking-wider text-slate-400 mb-1">Credit</label>
                        <Input
                          type="number"
                          min="0"
                          step="0.01"
                          value={line.credit_amount}
                          onChange={(e) => {
                            updateLine(index, "credit_amount", e.currentTarget.value);
                            if (Number(e.currentTarget.value) > 0 && line.debit_amount) {
                              updateLine(index, "debit_amount", "");
                            }
                          }}
                          placeholder="0.00"
                        />
                      </div>
                      <div className="w-40">
                        <label className="block text-[10px] font-medium uppercase tracking-wider text-slate-400 mb-1">Memo</label>
                        <Input
                          value={line.description}
                          onChange={(e) => updateLine(index, "description", e.currentTarget.value)}
                          placeholder="Memo"
                        />
                      </div>
                      <button
                        type="button"
                        onClick={() => removeLine(index)}
                        disabled={formData.lines.length <= 1}
                        className="mt-4 rounded-lg p-2 text-slate-400 hover:bg-rose-50 hover:text-rose-600 disabled:opacity-40 disabled:cursor-not-allowed"
                        title="Remove line"
                      >
                        <Minus size={16} />
                      </button>
                    </div>
                  </div>
                ))}
                <div className="bg-slate-50 px-4 py-2 border-t border-slate-200">
                  <button type="button" onClick={addLine} className="text-xs font-medium text-factory-600 hover:text-factory-700 flex items-center gap-1">
                    <Plus size={12} /> Add line
                  </button>
                </div>
              </div>

              <div className="mt-3 space-y-1 rounded-lg border border-slate-200 bg-white p-3">
                <div className="flex justify-between">
                  <span className="text-sm font-medium text-slate-600">Total Debit</span>
                  <span className="text-sm font-semibold tabular-nums text-slate-900">Rs. {totalDebit.toLocaleString(undefined, { maximumFractionDigits: 2 })}</span>
                </div>
                <div className="flex justify-between">
                  <span className="text-sm font-medium text-slate-600">Total Credit</span>
                  <span className="text-sm font-semibold tabular-nums text-slate-900">Rs. {totalCredit.toLocaleString(undefined, { maximumFractionDigits: 2 })}</span>
                </div>
                <div className="flex justify-between border-t border-slate-100 pt-1">
                  <span className="flex items-center gap-1 text-sm font-medium text-slate-600"><Scale size={13} /> Difference</span>
                  <span className={`text-sm font-semibold tabular-nums ${isBalanced ? "text-emerald-700" : "text-rose-700"}`}>
                    Rs. {Math.abs(difference).toLocaleString(undefined, { maximumFractionDigits: 2 })}
                    {isBalanced && totalDebit > 0 ? " (balanced)" : ""}
                  </span>
                </div>
              </div>

              {!isBalanced && totalDebit > 0 && (
                <p className="mt-2 text-xs text-rose-600">Debits and credits must be equal before the entry can be saved.</p>
              )}
              {(errors as any).lines && <p className="mt-2 text-xs text-rose-600">{(errors as any).lines}</p>}
            </div>
          </CardContent>
        </Card>

        <div className="flex flex-wrap items-center justify-end gap-2">
          <Link
            href="/accounting"
            className="inline-flex h-10 items-center rounded-lg border border-slate-200 bg-white px-4 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50"
          >
            Cancel
          </Link>
          <Button
            type="submit"
            disabled={processing || !isBalanced || accounts.length === 0}
            className={!isBalanced || accounts.length === 0 ? "opacity-50 cursor-not-allowed" : ""}
          >
            {processing ? "Saving…" : formData.status === "draft" ? "Save Draft" : "Post Journal Entry"}
          </Button>
        </div>
      </form>
    </AppLayout>
  );
}
