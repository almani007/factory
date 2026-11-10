/**
 * Brixel FactoryOS — Quality Page
 * Quality inspections and pass/fail tracking.
 */
import { Head } from '@inertiajs/react';
import { Plus, Shield, AlertCircle, Clock } from 'lucide-react';

const INSPECTIONS = [
  { id: 1, ref: 'INS-0001', type: 'Incoming', item: 'Cotton Fabric', status: 'Passed', date: '2026-11-10' },
  { id: 2, ref: 'INS-0002', type: 'In-Process', item: 'Stitching Line A', status: 'Failed', date: '2026-11-09' },
  { id: 3, ref: 'INS-0003', type: 'Final', item: 'Dress Shirt', status: 'Pending', date: '2026-11-10' },
];

export default function Quality() {
  return (
    <div>
      <Head title="Quality Control — Brixel FactoryOS" />
      <div className="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
          <h1 className="text-2xl font-bold text-gray-900">Quality Control</h1>
          <p className="text-gray-600">Incoming, in-process, and final product inspections</p>
        </div>
        <button className="flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">
          <Plus size={16} /> New Inspection
        </button>
      </div>

      <div className="bg-white rounded-xl border shadow-sm overflow-hidden">
        <table className="w-full text-left">
          <thead className="bg-gray-50">
            <tr>
              <th className="px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Ref</th>
              <th className="px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Type</th>
              <th className="px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Item</th>
              <th className="px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Status</th>
              <th className="px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Date</th>
            </tr>
          </thead>
          <tbody className="divide-y divide-gray-100">
            {INSPECTIONS.map((i) => (
              <tr key={i.id} className="hover:bg-gray-50">
                <td className="px-6 py-4 text-sm font-mono text-gray-900">{i.ref}</td>
                <td className="px-6 py-4 text-sm text-gray-600">{i.type}</td>
                <td className="px-6 py-4 text-sm text-gray-900">{i.item}</td>
                <td className="px-6 py-4">
                  <span className={`px-2 py-1 text-xs font-semibold rounded ${
                    i.status === 'Passed' ? 'bg-green-50 text-green-700' :
                    i.status === 'Failed' ? 'bg-red-50 text-red-700' :
                    'bg-yellow-100 text-yellow-700'
                  }`}>
                    {i.status}
                  </span>
                </td>
                <td className="px-6 py-4 text-sm text-gray-600">{i.date}</td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </div>
  );
}