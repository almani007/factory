/**
 * Brixel FactoryOS — Inventory Page
 * Stock movement ledger and inventory management.
 */
import { Head } from '@inertiajs/react';
import { Plus, Search, Filter, Download } from 'lucide-react';

const MOVEMENTS = [
  { id: 1, reference: 'GRN-0001', type: 'Goods Receipt', item: 'Cotton Fabric', warehouse: 'WH-01', qty: 500, date: '2026-11-10', status: 'Posted' },
  { id: 2, reference: 'ISS-0002', type: 'Material Issue', item: 'Thread', warehouse: 'WH-01', qty: -250, date: '2026-11-09', status: 'Posted' },
  { id: 3, reference: 'ADJ-0003', type: 'Stock Adjustment', item: 'Buttons', warehouse: 'WH-02', qty: 50, date: '2026-11-08', status: 'Posted' },
];

export default function Inventory() {
  return (
    <div>
      <Head title="Inventory — Brixel FactoryOS" />
      <div className="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
          <h1 className="text-2xl font-bold text-gray-900">Inventory Management</h1>
          <p className="text-gray-600">Stock movements, balances, and warehouse operations</p>
        </div>
        <button className="flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">
          <Plus size={16} /> New Movement
        </button>
      </div>

      {/* Filters */}
      <div className="mb-6 flex gap-2">
        <button className="px-3 py-2 border border-gray-200 rounded-lg text-sm text-gray-600 hover:bg-gray-50">
          <Search size={16} className="mr-2" /> Filter
        </button>
        <button className="px-3 py-2 border border-gray-200 rounded-lg text-sm text-gray-600 hover:bg-gray-50">
          <Download size={16} className="mr-2" /> Export CSV
        </button>
      </div>

      {/* Stock Movements Table */}
      <div className="bg-white rounded-xl border shadow-sm overflow-hidden">
        <table className="w-full text-left">
          <thead className="bg-gray-50">
            <tr>
              <th className="px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Reference</th>
              <th className="px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Type</th>
              <th className="px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Item</th>
              <th className="px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Warehouse</th>
              <th className="px-6 py-3 text-xs font-semibold text-gray-500 uppercase text-right">Quantity</th>
              <th className="px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Date</th>
              <th className="px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Status</th>
            </tr>
          </thead>
          <tbody className="divide-y divide-gray-100">
            {MOVEMENTS.map((m) => (
              <tr key={m.id} className="hover:bg-gray-50">
                <td className="px-6 py-4 text-sm text-gray-900">{m.reference}</td>
                <td className="px-6 py-4 text-sm text-gray-600">{m.type}</td>
                <td className="px-6 py-4 text-sm text-gray-900">{m.item}</td>
                <td className="px-6 py-4 text-sm text-gray-600">{m.warehouse}</td>
                <td className="px-6 py-4 text-sm text-right font-mono">
                  {m.qty > 0 ? '+' : ''}{m.qty}
                </td>
                <td className="px-6 py-4 text-sm text-gray-600">{m.date}</td>
                <td className="px-6 py-4">
                  <span className="px-2 py-1 text-xs font-semibold bg-green-50 text-green-700 rounded">
                    {m.status}
                  </span>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </div>
  );
}