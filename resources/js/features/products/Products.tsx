/**
 * Brixel FactoryOS — Products / Items
 * Product master data, SKUs, categories, and inventory items.
 */

import { Head, Link, router, usePage } from "@inertiajs/react";
import { AppLayout } from "@/components/layout";
import { PageHeader } from "@/components/layout";
import { DataTable } from "@/components/DataTable";
import { StatStrip } from "@/components/StatStrip";
import { Plus, Eye, Edit, Package } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";

interface Product {
  id: number;
  sku: string;
  name: string;
  category: string;
  unit_of_measure: string;
  default_purchase_price: number;
  default_sales_price: number;
  reorder_level: number;
  status: string;
}

export default function Products() {
  const { props: data } = usePage() as any;
  const products: Product[] = data?.products?.data || [];
  const meta = data?.products?.meta || {};
  const summary = data?.product_summary || { total: 0, active: 0 };

  return (
    <AppLayout title="Products / Items">
      <PageHeader
        title="Products / Items"
        subtitle="Product master data, SKUs, categories, and inventory items"
        breadcrumbs={[{ label: "Products", href: "/products" }]}
      >
        <Button onClick={() => router.visit("/products/create")}>
          <Plus size={16} /> New Product
        </Button>
      </PageHeader>

      <StatStrip
        items={[
          { label: "Total Products", value: summary.total, hint: "All SKUs" },
          { label: "Active", value: summary.active, hint: "Available for use", tone: "success" },
        ]}
      />

      <DataTable
        title="Products"
        columns={[
          { key: "sku", label: "SKU" },
          { key: "name", label: "Product Name" },
          { key: "category", label: "Category" },
          { key: "unit_of_measure", label: "UOM" },
          { key: "default_purchase_price", label: "Purchase Price", align: "right", format: (v: number) => `Rs. ${Number(v).toLocaleString(undefined, { maximumFractionDigits: 2 })}` },
          { key: "default_sales_price", label: "Sales Price", align: "right", format: (v: number) => `Rs. ${Number(v).toLocaleString(undefined, { maximumFractionDigits: 2 })}` },
          { key: "reorder_level", label: "Reorder Level" },
          { key: "status", label: "Status", format: (value: string) => (
            <Badge className={value === "active" ? "bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-200" : "bg-slate-100 text-slate-500 ring-1 ring-inset ring-slate-200"}>
              {value}
            </Badge>
          )},
        ]}
        data={products}
        meta={meta}
        rowActions={[
          { label: "View", icon: <Eye size={14} />, variant: "ghost" },
          { label: "Edit", icon: <Edit size={14} />, variant: "outline" },
        ]}
      />
    </AppLayout>
  );
}
