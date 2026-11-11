/**
 * Brixel FactoryOS — Layout Components
 * Premium enterprise SaaS shell with glassmorphism, role-aware navigation.
 */

import * as React from "react";
import { Head, Link, router, usePage } from "@inertiajs/react";
import { Button } from "@/components/ui/button";
import {
  LayoutDashboard,
  Package,
  Truck,
  Factory,
  ShieldCheck,
  Receipt,
  ShoppingCart,
  Send,
  BookOpen,
  Settings,
  Menu,
  X,
  Bell,
  Search,
  Plus,
  FileText,
  PieChart,
  CreditCard,
  Users,
  User,
  Building,
  Wrench,
  PackageCheck,
  LayoutGrid,
  BarChart,
  Cpu,
  ShieldAlert,
  RotateCcw,
  Shirt,
  Banknote,
  Clock,
  GanttChart,
  Bot,
  AlertCircle,
  Building2,
  Calendar,
  Award,
  FileType,
  Copy,
  CheckCircle2,
  Activity,
  Radio,
  Settings2,
  X,
  AlertTriangle,
  Info,
  Shield,
  Layout,
  Monitor,
  BarChart3,
  Layers,
  Target,
  HardHat,
  UserCog,
  BuildingLibrary,
  Briefcase,
  TrendingUp,
  FolderTree,
  Clock as ClockIcon,
  Users as UsersIcon,
  Package as PackageIcon,
  Truck as TruckIcon,
} from "lucide-react";
import { cn } from "@/lib/utils";
import NotificationBell from "@/components/NotificationBell";
import { useEffect, useState } from "react";

const NAV_GROUPS: Array<{
  label: string;
  items: Array<{
    name: string;
    href: string;
    icon: React.ElementType;
    roles?: string[];
  }>;
}> = [
  {
    label: "Overview",
    items: [{ name: "Dashboard", href: "/dashboard", icon: LayoutDashboard }],
  },
  {
    label: "Operations",
    items: [
      { name: "Inventory", href: "/inventory", icon: Package },
      { name: "Stock Transfers", href: "/inventory/transfers", icon: Shirt },
      { name: "Batches / Lots", href: "/inventory/batches", icon: Bot },
      { name: "Serial Numbers", href: "/inventory/serials", icon: Target },
      { name: "Cross-docking", href: "/inventory/cross-docking", icon: Truck },
      { name: "Kitting", href: "/inventory/kitting", icon: Package },
      { name: "Consignment", href: "/inventory/consignment", icon: Truck },
      { name: "Purchasing", href: "/purchasing", icon: ShoppingCart },
      { name: "RFQs", href: "/purchasing/rfq", icon: FileText },
      { name: "Contracts", href: "/purchasing/contracts", icon: Building2 },
      { name: "Production", href: "/production", icon: Factory },
      { name: "Work Orders", href: "/production/work-orders", icon: ClipboardList },
      { name: "Production Schedule", href: "/production/schedule", icon: GanttChart },
      { name: "APS Planning", href: "/aps", icon: Activity },
      { name: "APS Gantt", href: "/aps/gantt", icon: GanttChart },
      { name: "APS Capacity", href: "/aps/capacity", icon: BarChart3 },
      { name: "MRP Planning", href: "/mrp", icon: Activity },
      { name: "Quality", href: "/quality", icon: ShieldCheck },
      { name: "NCR Reports", href: "/quality/non-conformance", icon: AlertCircle },
      { name: "CAPA", href: "/quality/capa", icon: ShieldAlert },
      { name: "SPC Control Charts", href: "/quality/spc", icon: Activity },
      { name: "Costing", href: "/costing", icon: Receipt },
      { name: "Activity-Based Costing", href: "/costing/activity-based", icon: Layers },
    ],
  },
  {
    label: "Commercial",
    items: [
      { name: "Sales", href: "/sales", icon: Truck },
      { name: "Sales Quotes", href: "/sales/quotes", icon: FileText },
      { name: "Customer Invoices", href: "/sales/invoices", icon: Banknote },
      { name: "Dispatch", href: "/dispatch", icon: Send },
      { name: "Supplier Invoices", href: "/purchasing/supplier-invoices", icon: Banknote },
      { name: "Vendor Scorecards", href: "/purchasing/vendor-scorecards", icon: Award },
      { name: "CPQ", href: "/sales/cpq", icon: FileText },
      { name: "Commissions", href: "/sales/commissions", icon: TrendingUp },
    ],
  },
  {
    label: "Finance",
    items: [
      { name: "Accounting", href: "/accounting", icon: BookOpen },
      { name: "Chart of Accounts", href: "/accounting/chart-of-accounts", icon: FileText },
      { name: "Account Groups", href: "/accounting/account-groups", icon: FolderTree },
      { name: "Journal Entries", href: "/accounting", icon: BookOpen },
      { name: "Accounting Periods", href: "/accounting/periods", icon: Calendar },
      { name: "Trial Balance", href: "/accounting/trial-balance", icon: PieChart },
      { name: "Profit & Loss", href: "/accounting/profit-loss", icon: CreditCard },
      { name: "Balance Sheet", href: "/accounting/balance-sheet", icon: CreditCard },
      { name: "Cash Flow", href: "/accounting/cash-flow", icon: Banknote },
      { name: "Bank Reconciliation", href: "/accounting/bank-reconciliation", icon: Banknote },
      { name: "Fixed Assets", href: "/accounting/fixed-assets", icon: Building2 },
    ],
  },
  {
    label: "Resources",
    items: [
      { name: "HR / Employees", href: "/hr", icon: Users },
      { name: "Time & Attendance", href: "/hr/time-attendance", icon: ClockIcon },
      { name: "Leave Management", href: "/hr/leave", icon: Calendar },
      { name: "Shift Management", href: "/hr/shifts", icon: ClockIcon },
      { name: "Payroll", href: "/hr/payroll", icon: Banknote },
      { name: "Performance Reviews", href: "/hr/performance", icon: Shield },
      { name: "Training", href: "/hr/training", icon: BookOpen },
      { name: "Training Matrix", href: "/hr/training-matrix", icon: Users },
      { name: "Maintenance", href: "/maintenance", icon: Wrench },
      { name: "Preventive Maintenance", href: "/maintenance/preventive", icon: RotateCcw },
      { name: "Products / Items", href: "/products", icon: PackageCheck },
      { name: "Warehouses", href: "/warehouses", icon: Building },
    ],
  },
  {
    label: "Supply Chain",
    items: [
      { name: "Goods Receipts", href: "/supply-chain/goods-receipts", icon: Truck },
      { name: "Purchase Requisitions", href: "/supply-chain/purchase-requisitions", icon: ClipboardList },
    ],
  },
  {
    label: "Manufacturing",
    items: [
      { name: "BOMs", href: "/boms", icon: LayoutGrid },
      { name: "Routings", href: "/routings", icon: Cpu },
    ],
  },
  {
    label: "Reporting",
    items: [
      { name: "Reports", href: "/reports", icon: BarChart3 },
      { name: "Custom Reports", href: "/reports/custom", icon: FileType },
      { name: "Analytics", href: "/analytics", icon: Target },
    ],
  },
  {
    label: "Documents",
    items: [
      { name: "Document Management", href: "/documents", icon: Copy },
    ],
  },
  {
    label: "Compliance",
    items: [
      { name: "Audit Trail", href: "/compliance/audit-trail", icon: Shield },
    ],
  },
  {
    label: "Workflow",
    items: [
      { name: "Approvals", href: "/workflow/approvals", icon: Activity },
      { name: "Rules", href: "/workflow/rules", icon: Layers },
    ],
  },
  {
    label: "Integrations",
    items: [
      { name: "Webhooks", href: "/integrations/webhooks", icon: Copy },
    ],
  },
  {
    label: "Administration",
    items: [
      { name: "Tenancy", href: "/tenancy", icon: Settings, roles: ["super_admin", "admin"] },
      { name: "Users", href: "/rbac/users", icon: UserCog, roles: ["super_admin", "admin"] },
      { name: "Roles", href: "/rbac/roles", icon: Shield, roles: ["super_admin", "admin"] },
      { name: "Settings", href: "/settings", icon: Settings },
    ],
  },
];

interface AppLayoutProps {
  children: React.ReactNode;
  title?: string;
}

export function AppLayout({ children, title }: AppLayoutProps) {
  const { url, props } = usePage() as any;
  const [mobileOpen, setMobileOpen] = useState(false);

  const path = (url || "/").split("?")[0];
  const isActive = (href: string) =>
    path === href || path.startsWith(`${href}/`);

  const currentUser = props?.user || {
    name: "Admin User",
    email: "admin@factoryos.local",
    role: "Super Admin",
  };
  const tenant = props?.tenant || {
    name: "Admin Workspace",
    plan: null,
  };
  const initials = currentUser.name
    .split(" ")
    .filter(Boolean)
    .slice(0, 2)
    .map((part: string) => part.charAt(0).toUpperCase())
    .join("");

  const currentUserRole = currentUser.role?.toLowerCase().replace(/\s+/g, "_") || "viewer";

  const roleConfig: Record<string, { label: string; color: string; bg: string; border: string }> = {
    super_admin: { label: "Super Admin", color: "text-purple-600", bg: "bg-purple-50", border: "border-purple-200" },
    admin: { label: "Admin", color: "text-blue-600", bg: "bg-blue-50", border: "border-blue-200" },
    manager: { label: "Manager", color: "text-emerald-600", bg: "bg-emerald-50", border: "border-emerald-200" },
    operator: { label: "Operator", color: "text-amber-600", bg: "bg-amber-50", border: "border-amber-200" },
    viewer: { label: "Viewer", color: "text-slate-600", bg: "bg-slate-50", border: "border-slate-200" },
  };

  const roleInfo = roleConfig[currentUserRole] || roleConfig.viewer;

  const filteredGroups = NAV_GROUPS.map((group) => ({
    ...group,
    items: group.items.filter(
      (item) => !item.roles || item.roles.includes(currentUserRole)
    ),
  })).filter((group) => group.items.length > 0);

  const searchInventory = (event: React.FormEvent<HTMLFormElement>) => {
    event.preventDefault();
    const term = String(new FormData(event.currentTarget).get("q") || "").trim();
    router.visit(term ? `/inventory?q=${encodeURIComponent(term)}` : "/inventory");
  };

  return (
    <div className="min-h-screen shell-bg text-slate-900">
      <Head title={title ? `${title} — Brixel FactoryOS` : "Brixel FactoryOS"} />

      {/* Top bar — glass */}
      <header className="fixed inset-x-0 top-0 z-40 flex h-16 items-center gap-3 border-b border-slate-200/60 bg-white/70 px-4 backdrop-blur-md supports-[backdrop-filter]:bg-white/80 lg:px-6 shadow-sm shadow-slate-200/30">
        <button
          type="button"
          onClick={() => setMobileOpen(true)}
          className="rounded-xl p-2 text-slate-600 hover:bg-slate-100 hover:text-slate-900 transition-all lg:hidden"
          aria-label="Open navigation"
        >
          <Menu size={20} />
        </button>

        <Link href="/dashboard" className="flex items-center gap-2.5">
          <span className="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-factory-600 to-factory-900 text-sm font-bold text-white shadow-sm shadow-factory-500/25 shadow-ring-factory-500/10">
            B
          </span>
          <span className="hidden flex-col leading-none sm:flex">
            <span className="text-[15px] font-semibold tracking-tight text-slate-900">
              Brixel FactoryOS
            </span>
            <span className="mt-1 text-[11px] font-medium uppercase tracking-wider text-factory-600">
              Manufacturing ERP
            </span>
          </span>
        </Link>

        <div className="ml-auto flex items-center gap-2 sm:gap-3">
          <form
            onSubmit={searchInventory}
            className="hidden items-center gap-2 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm md:flex"
          >
            <Search
              size={15}
              className="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"
            />
            <input
              type="search"
              name="q"
              placeholder="Search inventory…"
              className="h-9 w-52 rounded-lg border border-slate-200 bg-slate-50 pl-9 pr-3 text-sm text-slate-700 placeholder:text-slate-400 focus:border-factory-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-factory-500"
            />
          </form>

          <NotificationBell
            unreadCount={props?.unread_count ?? 0}
            notifications={props?.notifications ?? []}
          />

          <div className="flex items-center gap-2.5 border-l border-slate-200 pl-3 sm:pl-4">
            <div className="hidden flex-col items-end leading-tight sm:flex">
              <span className="text-sm font-medium text-slate-900">{currentUser.name}</span>
              <span className="text-[11px] font-medium uppercase tracking-wide text-slate-400">
                {currentUser.role?.replace(/_/g, " ")}
              </span>
            </div>
            <span className="flex h-9 w-9 items-center justify-center rounded-full bg-slate-900 text-sm font-semibold text-white shadow-md shadow-ring-factory-500/20">
              {initials || "A"}
            </span>
          </div>
        </div>
      </header>

      {/* Mobile scrim */}
      {mobileOpen && (
        <button
          type="button"
          aria-label="Close navigation"
          onClick={() => setMobileOpen(false)}
          className="fixed inset-0 z-40 bg-slate-900/40 backdrop-blur-sm lg:hidden transition-opacity"
        />
      )}

      {/* Sidebar */}
      <aside
        className={cn(
          "fixed bottom-0 left-0 top-16 z-50 w-64 border-r border-slate-200 bg-white/80 backdrop-blur-sm transition-transform duration-300 ease-out lg:translate-x-0",
          mobileOpen ? "translate-x-0" : "-translate-x-full"
        )}
      >
        <div className="flex h-full flex-col">
          <nav className="flex-1 overflow-y-auto px-3 py-4">
            {NAV_GROUPS.map((group) => (
              <div key={group.label} className="mb-5">
                <p className="mb-2 px-3 text-[10px] font-semibold uppercase tracking-widest text-slate-400">
                  {group.label}
                </p>
                <div className="space-y-0.5">
                  {group.items.map((item) => {
                    const Icon = item.icon;
                    const active = isActive(item.href);
                    return (
                      <Link
                        key={item.name}
                        href={item.href}
                        onClick={() => setMobileOpen(false)}
                        className={cn(
                          "group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-all duration-200",
                          active
                            ? "bg-factory-50 text-factory-700 ring-1 ring-inset ring-factory-200/60"
                            : "text-slate-600 hover:bg-slate-50 hover:text-slate-900"
                        )}
                      >
                        <Icon
                          size={17}
                          strokeWidth={2}
                          className={cn(
                            active ? "text-factory-600" : "text-slate-400 group-hover:text-slate-600"
                          )}
                        />
                        <span className="truncate">{item.name}</span>
                        {active && (
                          <span className="ml-auto h-1.5 w-1.5 rounded-full bg-factory-600" />
                        )}
                      </Link>
                    );
                  })}
                </div>
              </div>
            ))}
          </nav>

          <div className="border-t border-slate-200 p-3">
            <div className="flex items-center gap-3 rounded-xl bg-slate-50 px-3 py-2.5 ring-1 ring-inset ring-slate-200/60">
              <span className={`flex h-8 w-8 items-center justify-center rounded-full ${roleInfo.bg} ${roleInfo.color}`}>
                {initials || "AD"}
              </span>
              <div className="min-w-0">
                <p className="truncate text-xs font-semibold text-slate-800">{tenant.name}</p>
                <p className="truncate text-[11px] capitalize text-slate-500">
                  {tenant.plan ? `${tenant.plan} plan` : "Production environment"}
                </p>
              </div>
            </div>
          </div>
        </div>
      </aside>

      {/* Content */}
      <main className="pt-16 lg:pl-64">
        <div className="mx-auto w-full max-w-[1440px] px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
          {children}
        </div>
      </main>

      {/* Toast Notifications */}
      <ToastNotifications />
    </div>
  );
}

interface PageHeaderProps {
  title: string;
  subtitle?: string;
  breadcrumbs?: Array<{ label: string; href: string }>;
  actions?: React.ReactNode;
}

export function PageHeader({ title, subtitle, breadcrumbs, actions }: PageHeaderProps) {
  return (
    <div className="mb-6">
      <Head title={title} />
      <div className="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
          {breadcrumbs && breadcrumbs.length > 0 && (
            <div className="flex items-center gap-1.5 text-xs text-slate-500 mb-2">
              {breadcrumbs.map((crumb, i) => (
                <React.Fragment key={crumb.label}>
                  {i > 0 && <span>/</span>}
                  <Link href={crumb.href} className="hover:text-factory-600 transition-colors">
                    {crumb.label}
                  </Link>
                </React.Fragment>
              ))}
            </div>
          )}
          <h1 className="text-2xl font-bold text-slate-900 tracking-tight">{title}</h1>
          {subtitle && <p className="mt-1 text-sm text-slate-500">{subtitle}</p>}
        </div>
        {actions && <div className="flex items-center gap-3">{actions}</div>}
      </div>
    </div>
  );
}

function ToastNotifications() {
  const { flash } = usePage() as any;
  const [toasts, setToasts] = useState<Array<{
    id: string;
    title: string;
    description?: string;
    type: "success" | "error" | "warning" | "info";
  }>>([]);

  React.useEffect(() => {
    if (flash?.success) {
      const id = Math.random().toString(36).substr(2, 9);
      setToasts((prev) => [...prev, { id, title: flash.success, type: "success" }]);
    }
    if (flash?.error) {
      const id = Math.random().toString(36).substr(2, 9);
      setToasts((prev) => [...prev, { id, title: flash.error, type: "error" }]);
    }
    if (flash?.warning) {
      const id = Math.random().toString(36).substr(2, 9);
      setToasts((prev) => [...prev, { id, title: flash.warning, type: "warning" }]);
    }
    if (flash?.info) {
      const id = Math.random().toString(36).substr(2, 9);
      setToasts((prev) => [...prev, { id, title: flash.info, type: "info" }]);
    }
  }, [flash]);

  const dismiss = (id: string) => {
    setToasts((prev) => prev.filter((t) => t.id !== id));
  };

  const TYPE_CONFIG: Record<string, { icon: React.ReactNode; bg: string; text: string; border: string }> = {
    success: { icon: <CheckCircle2 size={18} className="text-emerald-600" />, bg: "bg-emerald-50", text: "text-emerald-800", border: "border-emerald-200" },
    error: { icon: <X size={18} className="text-rose-600" />, bg: "bg-rose-50", text: "text-rose-800", border: "border-rose-200" },
    warning: { icon: <AlertTriangle size={18} className="text-amber-600" />, bg: "bg-amber-50", text: "text-amber-800", border: "border-amber-200" },
    info: { icon: <Info size={18} className="text-blue-600" />, bg: "bg-blue-50", text: "text-blue-800", border: "border-blue-200" },
  };

  return (
    <div className="fixed top-6 right-6 z-50 flex flex-col gap-2 pointer-events-none">
      {toasts.map((toast) => {
        const config = TYPE_CONFIG[toast.type] ?? TYPE_CONFIG.info;
        return (
          <div
            key={toast.id}
            className={cn(
              "flex items-start gap-3 rounded-xl border p-4 shadow-lg pointer-events-auto animate-in slide-in-from-top-4 sm:duration-300",
              config.bg,
              config.border,
            )}
          >
            <div className={`mt-0.5 shrink-0 ${config.text}`}>{config.icon}</div>
            <div className="min-w-0 flex-1">
              <h4 className="text-sm font-semibold text-slate-900">{toast.title}</h4>
              {toast.description && <p className="mt-0.5 text-xs text-slate-600">{toast.description}</p>}
            </div>
            <button
              type="button"
              onClick={() => dismiss(toast.id)}
              className="rounded-md p-1 text-slate-400 hover:bg-slate-200 hover:text-slate-600 transition-colors"
              aria-label="Dismiss"
            >
              <X size={14} />
            </button>
          </div>
        );
      })}
    </div>
  );
}
