/**
 * Brixel FactoryOS — Inventory Page
 * Stock movements ledger and inventory management.
 */

import { Head, Link, usePage, useForm } from "@inertiajs/react";
import { AppLayout } from "@/components/layout";
import { PageHeader } from "@/components/layout";
import { DataTable } from "@/components/DataTable";
import { Plus, Search, Filter, Download } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";

interface InventoryMovement {
  id: number;
  reference: string;
  movement_type: string;
  item_name: string;
  warehouse_name: string;
  quantity: number;
  transaction_date: string;
  status: string;
}

export default function Inventory() {
  const { props: data } = usePage() as any;
  const movements: InventoryMovement[] = data?.movements?.data || [];
  const meta = data?.movements?.meta || {};
  const summary = data?.inventory_summary || {
    on_hand: "0",
    value: "Rs. 0",
    low_stock: 0,
  };

  const columns = [
    { key: "reference", label: "Reference" },
    { key: "movement_type", label: "Type" },
    { key: "item_name", label: "Item" },
    { key: "warehouse_name", label: "Warehouse" },
    { key: "quantity", label: "Quantity" },
    { key: "transaction_date", label: "Date" },
    { key: "status", label: "Status" },
  ];

  return (
    <AppLayout title="Inventory Management">
      <PageHeader
        title="Inventory Management"
        subtitle="Stock movements, balances, and warehouse operations"
      >
        <Button>
          <Plus size={16} /> New Movement
        </Button>
      </PageHeader>

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <Card>
          <CardHeader className="p-5 pb-2">
            <CardTitle className="text-sm font-medium text-slate-500">Available Stock</CardTitle>
          </CardHeader>
          <CardContent className="p-5 pt-0">
            <p className="text-2xl font-semibold tracking-tight text-slate-900 tabular-nums">{summary.on_hand} units</p>
            <p className="text-xs text-slate-500">Across all warehouses</p>
          </CardContent>
        </Card>
        <Card>
          <CardHeader className="p-5 pb-2">
            <CardTitle className="text-sm font-medium text-slate-500">Low Stock Alerts</CardTitle>
          </CardHeader>
          <CardContent className="p-5 pt-0">
            <p className="text-2xl font-semibold tracking-tight text-rose-600 tabular-nums">{summary.low_stock}</p>
            <p className="text-xs text-slate-500">Items below reorder level</p>
          </CardContent>
        </Card>
        <Card>
          <CardHeader className="p-5 pb-2">
            <CardTitle className="text-sm font-medium text-slate-500">Stock Value</CardTitle>
          </CardHeader>
          <CardContent className="p-5 pt-0">
            <p className="text-2xl font-semibold tracking-tight text-slate-900 tabular-nums">{summary.value}</p>
            <p className="text-xs text-slate-500">Total inventory value</p>
          </CardContent>
        </Card>
      </div>

      <DataTable
        title="Stock Movements"
        columns={columns}
        data={movements}
        meta={meta}
      />
    </AppLayout>
  );
}