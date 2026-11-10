/**
 * Brixel FactoryOS — Production Page
 * Production order management and work orders.
 */
import { Head } from '@inertiajs/react';
import { Plus, Play, Pause, Stop, Search } from 'lucide-react';

const ORDERS = [
  { id: 1, number: 'PO-2026-001', item: 'Cotton Shirt', qty: 1000, status: 'In Production', progress: 65 },
  { id: 2, number: 'PO-2026-002', item: 'Formal Trousers', qty: 500, status: 'Material Reserved', progress: 0 },
  { id: 3, number: 'PO-2026-003', item: 'Dress Shirt', qty: 800, status: 'Quality Inspection', progress: 100 },
];

const STATUS_COLORS: Record<string, string> = {
  'Draft': 'bg-gray-100 text-gray-700',
  'Pending Approval': 'bg-yellow-100 text-yellow-700',
  'Approved': 'bg-blue-100 text-blue-700',
  'Material Reserved': 'bg-indigo-100 text-indigo-700',
  'In Production': 'bg-blue-100 text-blue-700',
  'Quality Inspection': 'bg-purple-100 text-purple-700',
  'Completed': 'bg-green-100 text-green-700',
  'Closed': 'bg-gray-100 text-gray-700',
  'Cancelled': 'bg-red-100 text-red-700',
};

export default function Production() {
  return (
    <div>
      <Head title="Production — Brixel FactoryOS" />
      <div className="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
          <h1 className="text-2xl font-bold text-gray-900">Production Orders</h1>
          <p className="text-gray-600">Work orders, material issues, and production tracking</p>
        </div>
        <button className="flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">
          <Plus size={16} /> New Production Order
        </button>
      </div>

      <div className="bg-white rounded-xl border shadow-sm overflow-hidden">
        <table className="w-full text-left">
          <thead className="bg-gray-50">
            <tr>
              <th className="px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Order #</th>
              <th className="px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Product</th>
              <th className="px-6 py-3 text-xs font-semibold text-gray-500 uppercase text-right">Planned Qty</th>
              <th className="px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Status</th>
              <th className="px-6 py-3 text-xs font-semibold text-gray-500 uppercase text-right">Progress</th>
            </tr>
          </thead>
          <tbody className="divide-y divide-gray-100">
            {ORDERS.map((o) => (
              <tr key={o.id} className="hover:bg-gray-50">
                <td className="px-6 py-4 text-sm font-mono text-gray-900">{o.number}</td>
                <td className="px-6 py-4 text-sm text-gray-900">{o.item}</td>
                <td className="px-6 py-4 text-sm text-right text-gray-600">{o.qty.toLocaleString()}</td>
                <td className="px-6 py-4">
                  <span className={`px-2 py-1 text-xs font-semibold rounded ${STATUS_COLORS[o.status]}`}>
                    {o.status}
                  </span>
                </td>
                <td className="px-6 py-4 text-sm text-right">
                  <div className="w-32 bg-gray-200 rounded-full h-2">
                    <div className="bg-blue-600 h-2 rounded-full" style={{ width: `${o.progress}%` }} />
                  </div>
                  <span className="text-xs text-gray-500">{o.progress}%</span>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </div>
  );
}