import * as React from "react";
/**
 * Brixel FactoryOS — Page Routing Map
 * Maps route names to feature page components.
 */

import Dashboard from "@/features/dashboard/Dashboard";
import Inventory from "@/features/inventory/Inventory";
import Production from "@/features/production/Production";
import Quality from "@/features/quality/Quality";
import Costing from "@/features/costing/Costing";
import Purchasing from "@/features/purchasing/Purchasing";
import Sales from "@/features/sales/Sales";
import Dispatch from "@/features/dispatch/Dispatch";
import Accounting from "@/features/accounting/Accounting";
import Tenancy from "@/features/tenancy/Tenancy";

export const PAGES: Record<string, React.ComponentType> = {
  dashboard: Dashboard,
  inventory: Inventory,
  production: Production,
  quality: Quality,
  costing: Costing,
  purchasing: Purchasing,
  sales: Sales,
  dispatch: Dispatch,
  accounting: Accounting,
  tenancy: Tenancy,
};

export default PAGES;