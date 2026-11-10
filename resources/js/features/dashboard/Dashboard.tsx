/**
 * Brixel FactoryOS — Dashboard Page
 * Manufacturing management overview with key KPIs.
 */
import { Head } from '@inertiajs/react';
import { BarChart, Truck, Factory, Shield, CreditCard, AlertCircle, Clock } from 'lucide-react';

const KPI_CARDS = [
  { title: 'Production Today', value: '1,240 units', icon: Factory, change: '+12%', trend: 'up' },
  { title: 'Raw Material Value', value: 'Rs. 4.2M', icon: Truck, change: '-3%', trend: 'down' },
  { title: 'Orders Delayed', value: '4', icon: AlertCircle, change: '+2', trend: 'up' },
  { title: 'Finished Goods', value: '890 units', icon: Clock, change: '+8%', trend: 'up' },
  { title: 'Quality Rejections', value: '2.1%', icon: Shield, change: '-0.4%', trend: 'down' },
  { title: 'Actual Unit Cost', value: 'Rs. 149', icon: CreditCard, change: '+10.4%', trend: 'up' },
];

export default function Dashboard() {
  return (
    <div>
      <Head title="Dashboard — Brixel FactoryOS" />
      <div className="mb-6">
        <h1 className="text-2xl font-bold text-gray-900">Factory Dashboard</h1>
        <p className="text-gray-600">Real-time overview of manufacturing operations</p>
      </div>

      {/* KPI Grid */}
      <div className="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-8">
        {KPI_CARDS.map((card) => (
          <div key={card.title} className="bg-white p-6 rounded-xl border shadow-sm">
            <div className="flex items-center justify-between mb-2">
              <div>
                <p className="text-sm text-gray-500">{card.title}</p>
                <p className="text-2xl font-bold">{card.value}</p>
              </div>
              <div className="p-2 bg-blue-50 rounded-lg text-blue-600">
                <card.icon size={20} />
              </div>
            </div>
            <div className={`text-xs font-medium ${card.trend === 'up' ? 'text-red-600' : 'text-green-600'}`}>
              {card.change}
            </div>
          </div>
        ))}
      </div>

      {/* Quick Actions */}
      <div className="grid grid-cols-1 lg:grid-cols-4 gap-4">
        <div className="bg-white p-6 rounded-xl border shadow-sm">
          <h3 className="text-sm font-semibold text-gray-900 mb-4">Quick Actions</h3>
          <div className="space-y-3">
            <button className="w-full px-4 py-3 text-left bg-blue-50 border border-blue-100 rounded-lg text-sm font-medium text-blue-700 hover:bg-blue-100 transition-colors">
              New Production Order
            </button>
            <button className="w-full px-4 py-3 text-left bg-white border border-gray-200 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">
              Create Purchase Order
            </button>
            <button className="w-full px-4 py-3 text-left bg-white border border-gray-200 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">
              Record Quality Inspection
            </button>
            <button className="w-full px-4 py-3 text-left bg-white border border-gray-200 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">
              Post Journal Entry
            </button>
          </div>
        </div>
      </div>
    </div>
  );
}