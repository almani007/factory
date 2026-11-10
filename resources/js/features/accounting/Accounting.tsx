/**
 * Brixel FactoryOS — Accounting Page
 * Journal entries and financial reports.
 */
import { Head } from '@inertiajs/react';
import { Plus, CreditCard, BarChart, Search } from 'lucide.react';

export default function Accounting() {
  return (
    <div>
      <Head title="Accounting — Brixel FactoryOS" />
      <div className="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
          <h1 className="text-2xl font-bold text-gray-900">Accounting & Finance</h1>
          <p className="text-gray-600">Double-entry ledger, journal entries, and financial reports</p>
        </div>
        <button className="flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">
          <Plus size={16} /> New Journal Entry
        </button>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
        <div className="bg-white p-6 rounded-xl border shadow-sm">
          <p className="text-xs text-gray-500 uppercase">Trial Balance</p>
          <p className="text-2xl font-bold">Rs. 2.1M</p>
          <p className="text-xs text-green-600 mt-1">Balanced</p>
        </div>
        <div className="bg-white p-6 rounded-xl border shadow-sm">
          <p className="text-xs text-gray-500 uppercase">Accounts Receivable</p>
          <p className="text-2xl font-bold">Rs. 480,000</p>
          <p className="text-xs text-gray-600 mt-1">Outstanding</p>
        </div>
        <div className="bg-white p-6 rounded-xl border shadow-sm">
          <p className="text-xs text-gray-500 uppercase">Accounts Payable</p>
          <p className="text-2xl font-bold">Rs. 133,500</p>
          <p className="text-xs text-gray-600 mt-1">Outstanding</p>
        </div>
      </div>
    </div>
  );
}