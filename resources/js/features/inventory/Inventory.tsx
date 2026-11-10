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
        <Button className="bg-factory-600 text-white hover:bg-factory-500">
          <Plus size={16} /> New Movement
        </Button>
      </PageHeader>

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <Card className="p-4">
          <CardHeader>
            <CardTitle>Available Stock</CardTitle>
          </CardHeader>
          <CardContent>
            <p className="text-2xl font-bold text-gray-900">1,240 items</p>
            <p className="text-xs text-gray-500">Across all warehouses</p>
          </CardContent>
        </Card>
        <Card className="p-4">
          <CardHeader>
            <CardTitle>Low Stock Alerts</CardTitle>
          </CardHeader>
          <CardContent>
            <p className="text-2xl font-bold text-red-600">3</p>
            <p className="text-xs text-gray-500">Items below reorder level</p>
          </CardContent>
        </Card>
        <Card className="p-4">
          <CardHeader>
            <CardTitle>Stock Value</CardTitle>
          </CardHeader>
          <CardContent>
            <p className="text-2xl font-bold text-gray-900">Rs. 4.2M</p>
            <p className="text-xs text-gray-500">Total inventory value</p>
          </CardContent>
        </Card>
      </div>

      <DataTable
        title="Stock Movements"
        columns={columns}
        data={movements}
        createUrl="/inventory/movements/create"
        meta={meta}
      />
    </AppLayout>
  );
}