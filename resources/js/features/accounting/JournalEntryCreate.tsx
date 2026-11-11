/**
 * Brixel FactoryOS — Create Journal Entry
 * Double-entry journal entry form with dynamic line items.
 */

import { Head, Link, router, usePage, useForm } from "@inertiajs/react";
import { AppLayout } from "@/components/layout";
import { PageHeader } from "@/components/layout";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Minus, Plus } from "lucide-react";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { cn } from "@/lib/utils";

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

export default function JournalEntryCreate() {
  const { data: formData, errors, setData, recentErrors, isPending, finished, reset } =
    useForm<FormData>({
      reference: "",
      document_date: "",
      description: "",
      lines: [EMPTY_LINE],
    });

  const accounts = [
    { id: "1", code: "1010", name: "Cash on Hand", type: "asset" },
    { id: "2", code: "1020", name: "Bank Account", type: "asset" },
    { id: "3", code: "1100", name: "Accounts Receivable", type: "asset" },
    { id: "4", code: "1200", name: "Raw Materials Inventory", type: "asset" },
    { id: "5", code: "1300", name: "Finished Goods Inventory", type: "asset" },
    { id: "6", code: "2010", name: "Accounts Payable", type: "liability" },
    { id: "7", code: "2100", name: "Sales Tax Payable", type: "liability" },
    { id: "8", code: "3010", name: "Owner's Equity", type: "equity" },
    { id: "9", code: "4010", name: "Sales Revenue", type: "revenue" },
    { id: "10", code: "5010", name: "Cost of Goods Sold", type: "expense" },
    { id: "11", code: "5020", name: "Salaries Expense", type: "expense" },
    { id: "12", code: "5030", name: "Rent Expense", type: "expense" },
    { id: "13", code: "5040", name: "Utilities Expense", type: "expense" },
  ];

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
    setData("lines", lines);
  };

  const totalDebit = formData.lines.reduce((sum, l) => sum + Number(l.debit_amount || 0), 0);
  const totalCredit = formData.lines.reduce((sum, l) => sum + Number(l.credit_amount || 0), 0);
  const isBalanced = totalDebit === totalCredit && totalDebit > 0;

  const submit = (event: React.FormEvent) => {
    event.preventDefault();
    router.post("/accounting/journal-entries/create", {
      ...formData,
      lines: formData.lines.map((l) => ({
        account_id: Number(l.account_id),
        debit_amount: Number(l.debit_amount || 0),
        credit_amount: Number(l.credit_amount || 0),
        description: l.description,
      })),
    });
  };

  return (
    <AppLayout title="Create Journal Entry">
      <PageHeader
        title="Create Journal Entry"
        subtitle="Post a double-entry journal entry to the ledger"
        breadcrumbs={[{ label: "Accounting", href: "/accounting" }, { label: "Journal Entries" }, { label: "Create" }]}
      >
        <Link
          href="/accounting"
          className="inline-flex h-9 items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 text-sm font-medium text-slate-700 shadow-sm transition-colors hover:bg-slate-50"
        >
          ← Back to Journal Entries
        </Link>
      </PageHeader>

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
                  placeholder="JE-2024-0001"
                  className={cn("w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-factory-500 focus:outline-none focus:ring-1 focus:ring-factory-500", errors.reference ? "border-rose-500 focus:ring-rose-500" : "border-slate-300")}
                />
                {errors.reference && <p className="text-xs text-rose-600">{errors.reference}</p>}
              </div>
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Document Date</label>
                <Input
                  type="date"
                  value={formData.document_date}
                  onChange={(e) => setData("document_date", e.currentTarget.value)}
                  className={cn("w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-factory-500 focus:outline-none focus:ring-1 focus:ring-factory-500", errors.document_date ? "border-rose-500 focus:ring-rose-500" : "border-slate-300")}
                />
                {errors.document_date && <p className="text-xs text-rose-600">{errors.document_date}</p>}
              </div>
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Description</label>
                <Input
                  value={formData.description}
                  onChange={(e) => setData("description", e.currentTarget.value)}
                  placeholder="Enter description"
                  className={cn("w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-factory-500 focus:outline-none focus:ring-1 focus:ring-factory-500", errors.description ? "border-rose-500 focus:ring-rose-500" : "border-slate-300")}
                />
                {errors.description && <p className="text-xs text-rose-600">{errors.description}</p>}
              </div>
            </div>

            <div className="space-y-1">
              <label className="text-sm font-medium text-slate-700">Journal Lines</label>
              <p className="text-xs text-slate-500">Enter at least two lines. Total debits must equal total credits.</p>
              <div className="border border-slate-200 rounded-lg overflow-hidden">
                {formData.lines.map((line, index) => (
                  <div
                    key={line.id}
                    className={`border-b border-slate-100 last:border-b-0 flex items-center gap-3 p-4 ${index % 2 === 0 ? "bg-slate-50" : "bg-white"}`}
                  >
                    <div className="flex-1">
                      <Select onValueChange={(value) => updateLine(index, "account_id", value)}>
                        <SelectTrigger className="w-full">
                          <SelectValue placeholder="Select account" />
                        </SelectTrigger>
                        <SelectContent>
                          {accounts.map((acc) => (
                            <SelectItem key={acc.id} value={acc.id}>
                              {acc.code} — {acc.name} ({acc.type})
                            </SelectItem>
                          ))}
                        </SelectContent>
                      </Select>
                      <p className="text-xs text-slate-500 mt-1">
                        {line.account_name || "Account"}
                      </p>
                    </div>
                    <div className="flex items-center gap-2">
                      <div className="w-24">
                        <label className="block text-[10px] font-medium uppercase tracking-wider text-slate-400 mb-1">Debit</label>
                        <Input
                          value={line.debit_amount}
                          onChange={(e) => updateLine(index, "debit_amount", e.currentTarget.value)}
                          placeholder="0.00"
                        />
                      </div>
                      <div className="w-24">
                        <label className="block text-[10px] font-medium uppercase tracking-wider text-slate-400 mb-1">Credit</label>
                        <Input
                          value={line.credit_amount}
                          onChange={(e) => updateLine(index, "credit_amount", e.currentTarget.value)}
                          placeholder="0.00"
                        />
                      </div>
                      <div className="w-24">
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
                        className="rounded-lg p-2 text-slate-400 hover:bg-rose-50 hover:text-rose-600 disabled:opacity-40 disabled:cursor-not-allowed"
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
              <div className="flex justify-between pt-3 border-t border-slate-200">
                <span className="text-sm font-medium text-slate-600">Total Debit</span>
                <span className="text-sm font-semibold tabular-nums text-slate-900">Rs. {totalDebit.toLocaleString(undefined, { maximumFractionDigits: 2 })}</span>
              </div>
              <div className="flex justify-between pt-1">
                <span className="text-sm font-medium text-slate-600">Total Credit</span>
                <span className="text-sm font-semibold tabular-nums text-slate-900">Rs. {totalCredit.toLocaleString(undefined, { maximumFractionDigits: 2 })}</span>
              </div>
              {!isBalanced && formData.lines.length > 0 && (
                <p className="mt-2 text-xs text-rose-600">Debits and credits must be equal before posting.</p>
              )}
            </div>
          </CardContent>
        </Card>

        <div className="flex justify-end">
          <Button
            type="submit"
            disabled={isPending || !isBalanced}
            className={!isBalanced ? "opacity-50 cursor-not-allowed" : ""}
          >
            {isPending ? "Posting…" : "Post Journal Entry"}
          </Button>
        </div>

        {finished && (
          <div className="rounded-lg bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
            Journal entry posted successfully.
          </div>
        )}
      </form>
    </AppLayout>
  );
}
