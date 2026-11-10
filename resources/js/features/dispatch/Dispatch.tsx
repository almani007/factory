/**
 * Brixel FactoryOS — Dispatch Page
 * Dispatch orders and delivery tracking.
 */
import { Head } from '@inertiajs/react';
import { Plus, Truck, Monitor } from 'lucide.react';

export default function Dispatch() {
  return (
    <div>
      <Head title="Dispatch — Brixel FactoryOS" />
      <div className="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
          <h1 className="text-2xl font-bold text-gray-900">Dispatch & Logistics</h1>
          <p className="text-gray-600">Delivery planning, dispatch orders, and proof of delivery</p>
        </div>
        <button className="flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">
          <Plus size={16} /> New Dispatch
        </button>
      </div>

      <div className="bg-white rounded-xl border shadow-sm overflow-hidden">
        <table className="w-full text-left">
          <thead className="bg-gray-50">
            <tr>
              <th className="px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Dispatch #</th>
              <th className="px-6 py-3 text-xs font-semibold text-gray-500 uppercase">SO #</th>
              <th className="px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Driver</th>
              <th className="px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Date</th>
              <th className="px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Status</th>
            </tr>
          </thead>
          <tbody className="divide-y divide-gray-100">
            <tr className="hover:bg-gray-50">
              <td className="px-6 py-4 text-sm font-mono text-gray-900">DISP-001</td>
              <td className="px-6 py-4 text-sm text-gray-900">SO-2026-001</td>
              <td className="px-6 py-4 text-sm text-gray-600">Ahmed Khan</td>
              <td className="px-6 py-4 text-sm text-gray-600">2026-11-09</td>
              <td className="px-6 py-4">
                <span className="px-2 py-1 text-xs font-semibold bg-blue-100 text-blue-700 rounded">In Transit</span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  );
}