/**
 * Brixel FactoryOS — Costing Page
 * Production costing and variance analysis.
 */
import { Head } from '@inertiajs/react';
import { CreditCard, BarChart, AlertTriangle } from 'lucide-react';

const COSTS = [
  { id: 1, order: 'PO-2026-001', product: 'Cotton Shirt', standard: 135, actual: 149, variance: 14, variancePct: 10.4 },
  { id: 2, order: 'PO-2026-002', product: 'Formal Trousers', standard: 85, actual: 92, variance: 7, variancePct: 8.2 },
];

export default function Costing() {
  return (
    <div>
      <Head title="Costing — Brixel FactoryOS" />
      <div className="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
          <h1 className="text-2xl font-bold text-gray-900">Production Costing</h1>
          <p className="text-gray-600">Standard vs actual cost analysis and variance tracking</p>
        </div>
      </div>

      {/* Cost Summary Cards */}
      <div className="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
        <div className="bg-white p-6 rounded-xl border shadow-sm">
          <p className="text-xs text-gray-500 uppercase">Total Material Cost</p>
          <p className="text-2xl font-bold">Rs. 90,000</p>
          <p className="text-xs text-red-600 mt-1">+12.5% vs standard</p>
        </div>
        <div className="bg-white p-6 rounded-xl border shadow-sm">
          <p className="text-xs text-gray-500 uppercase">Total Labour Cost</p>
          <p className="text-2xl font-bold">Rs. 27,000</p>
          <p className="text-xs text-red-600 mt-1">+8.0% vs standard</p>
        </div>
        <div className="bg-white p-6 rounded-xl border shadow-sm">
          <p className="text-xs text-gray-500 uppercase">Machine & Overhead</p>
          <p className="text-2xl font-bold">Rs. 32,000</p>
          <p className="text-xs text-gray-600 mt-1">within tolerance</p>
        </div>
        <div className="bg-white p-6 rounded-xl border shadow-sm">
          <p className="text-xs text-gray-500 uppercase">Avg Unit Cost</p>
          <p className="text-2xl font-bold">Rs. 149</p>
          <p className="text-xs text-red-600 mt-1">+10.4% vs standard</p>
        </div>
      </div>

      <div className="bg-white rounded-xl border shadow-sm overflow-hidden">
        <table className="w-full text-left">
          <thead className="bg-gray-50">
            <tr>
              <th className="px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Order</th>
              <th className="px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Product</th>
              <th className="px-6 py-3 text-xs font-semibold text-gray-500 uppercase text-right">Standard</th>
              <th className="px-6 py-3 text-xs font-semibold text-gray-500 uppercase text-right">Actual</th>
              <th className="px-6 py-3 text-xs font-semibold text-gray-500 uppercase text-right">Variance</th>
              <th className="px-6 py-3 text-xs font-semibold text-gray-500 uppercase text-right">Var %</th>
            </tr>
          </thead>
          <tbody className="divide-y divide-gray-100">
            {COSTS.map((c) => (
              <tr key={c.id} className="hover:bg-gray-50">
                <td className="px-6 py-4 text-sm font-mono text-gray-900">{c.order}</td>
                <td className="px-6 py-4 text-sm text-gray-900">{c.product}</td>
                <td className="px-6 py-4 text-sm text-right text-gray-600">Rs. {c.standard}</td>
                <td className="px-6 py-4 text-sm text-right text-gray-900">Rs. {c.actual}</td>
                <td className="px-6 py-4 text-sm text-right font-mono text-red-600">+Rs. {c.variance}</td>
                <td className="px-6 py-4 text-sm text-right font-mono text-red-600">+{c.variancePct}%</td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </div>
  );
}