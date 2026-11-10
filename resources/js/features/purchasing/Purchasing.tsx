/**
 * Brixel FactoryOS — Purchasing Page
 * Suppliers, purchase orders, and GRN.
 */
import { Head } from '@inertiajs/react';
import { Plus, Truck, Search } from 'lucide-react';

export default function Purchasing() {
  return (
    <div>
      <Head title="Purchasing — Brixel FactoryOS" />
      <div className="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
          <h1 className="text-2xl font-bold text-gray-900">Purchasing</h1>
          <p className="text-gray-600">Suppliers, purchase orders, and goods receipts</p>
        </div>
        <button className="flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">
          <Plus size={16} /> New Purchase Order
        </button>
      </div>

      <div className="bg-white rounded-xl border shadow-sm overflow-hidden">
        <div className="px-6 py-4 border-b">
          <h3 className="text-sm font-semibold text-gray-900">Recent Purchase Orders</h3>
        </div>
        <table className="w-full text-left">
          <thead className="bg-gray-50">
            <tr>
              <th className="px-6 py-3 text-xs font-semibold text-gray-500 uppercase">PO #</th>
              <th className="px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Supplier</th>
              <th className="px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Date</th>
              <th className="px-6 py-3 text-xs font-semibold text-gray-500 uppercase text-right">Amount</th>
              <th className="px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Status</th>
            </tr>
          </thead>
          <tbody className="divide-y divide-gray-100">
            <tr className="hover:bg-gray-50">
              <td className="px-6 py-4 text-sm font-mono text-gray-900">PO-2026-001</td>
              <td className="px-6 py-4 text-sm text-gray-900">Textile Mills Ltd</td>
              <td className="px-6 py-4 text-sm text-gray-600">2026-11-05</td>
              <td className="px-6 py-4 text-sm text-right text-gray-600">Rs. 125,000</td>
              <td className="px-6 py-4">
                <span className="px-2 py-1 text-xs font-semibold bg-green-50 text-green-700 rounded">Received</span>
              </td>
            </tr>
            <tr className="hover:bg-gray-50">
              <td className="px-6 py-4 text-sm font-mono text-gray-900">PO-2026-002</td>
              <td className="px-6 py-4 text-sm text-gray-900">Button Suppliers Inc</td>
              <td className="px-6 py-4 text-sm text-gray-600">2026-11-08</td>
              <td className="px-6 py-4 text-sm text-right text-gray-600">Rs. 8,500</td>
              <td className="px-6 py-4">
                <span className="px-2 py-1 text-xs font-semibold bg-yellow-100 text-yellow-700 rounded">Pending</span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  );
}