/**
 * Brixel FactoryOS — Dashboard Page
 * Real-time overview of manufacturing operations.
 */

import { Head, Link, usePage } from "@inertiajs/react";
import { AppLayout } from "@/components/layout";
import { PageHeader } from "@/components/layout";
import { KPICard } from "@/components/KPICard";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";
import { Factory, Truck, Shield, CreditCard, Clock, AlertCircle } from "lucide-react";

export default function Dashboard() {
  const { props: data } = usePage() as any;
  const stats = data?.stats || {
    production_today: 0,
    raw_material_value: 0,
    orders_delayed: 0,
    finished_goods: 0,
    quality_rejection_rate: 0,
    actual_unit_cost: 0,
  };

  const plannedText = stats.production_today ? stats.production_today.toLocaleString() + ' units' : '0 units';
  const materialText = stats.raw_material_value ? 'Rs. ' + stats.raw_material_value.toLocaleString() : 'Rs. 0';
  const delayedText = stats.orders_delayed ? stats.orders_delayed.toString() : '0';
  const finishedText = stats.finished_goods ? stats.finished_goods.toLocaleString() + ' units' : '0 units';
  const rejectionText = stats.quality_rejection_rate ? stats.quality_rejection_rate.toString() + '%' : '0%';
  const unitCostText = stats.actual_unit_cost ? 'Rs. ' + stats.actual_unit_cost.toString() : 'Rs. 0';

  return (
    <AppLayout title="Dashboard">
      <PageHeader
        title="Factory Dashboard"
        subtitle="Real-time overview of manufacturing operations"
      />

      {/* KPI Grid */}
      <div className="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-8">
        <KPICard title="Production Today" value={plannedText} icon={<Factory size={20} />} />
        <KPICard title="Raw Material Value" value={materialText} icon={<Truck size={20} />} />
        <KPICard title="Orders Delayed" value={delayedText} icon={<AlertCircle size={20} />} />
        <KPICard title="Finished Goods" value={finishedText} icon={<Clock size={20} />} />
        <KPICard title="Quality Rejections" value={rejectionText} icon={<Shield size={20} />} />
        <KPICard title="Actual Unit Cost" value={unitCostText} icon={<CreditCard size={20} />} />
      </div>

      {/* Quick Actions */}
      <div className="grid grid-cols-1 lg:grid-cols-4 gap-4">
        <Card className="p-6">
          <CardHeader>
            <CardTitle>Quick Actions</CardTitle>
          </CardHeader>
          <CardContent className="space-y-3">
            <Link
              href="/production-orders"
              className="w-full px-4 py-3 text-left bg-factory-50 border border-factory-100 rounded-lg text-sm font-medium text-factory-700 hover:bg-factory-100 transition-colors"
            >
              New Production Order
            </Link>
            <Link
              href="/purchase-orders"
              className="w-full px-4 py-3 text-left border border-gray-200 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors"
            >
              Create Purchase Order
            </Link>
            <Link
              href="/quality/inspections"
              className="w-full px-4 py-3 text-left border border-gray-200 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors"
            >
              Record Quality Inspection
            </Link>
            <Link
              href="/accounting/journal-entries"
              className="w-full px-4 py-3 text-left border border-gray-200 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors"
            >
              Post Journal Entry
            </Link>
          </CardContent>
        </Card>

        {/* Recent Activity */}
        <Card className="p-6">
          <CardHeader>
            <CardTitle>Recent Activity</CardTitle>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="flex items-start gap-3">
              <div className="w-2 h-2 bg-green-500 rounded-full mt-2" />
              <div className="text-sm">
                <p className="text-gray-900">GRN-0001 received</p>
                <p className="text-xs text-gray-500">Cotton Fabric - 500 meters</p>
              </div>
            </div>
            <div className="flex items-start gap-3">
              <div className="w-2 h-2 bg-blue-500 rounded-full mt-2" />
              <div className="text-sm">
                <p className="text-gray-900">PO-2026-001 approved</p>
                <p className="text-xs text-gray-500">Textile Mills Ltd - Rs. 125,000</p>
              </div>
            </div>
            <div className="flex items-start gap-3">
              <div className="w-2 h-2 bg-yellow-500 rounded-full mt-2" />
              <div className="text-sm">
                <p className="text-gray-900">Quality inspection pending</p>
                <p className="text-xs text-gray-500">Production order PO-2026-003</p>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>
    </AppLayout>
  );
}