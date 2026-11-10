import * as React from "react";
/**
 * Brixel FactoryOS — Layout Components
 * Application shell: fixed top bar, grouped sidebar, content canvas.
 */

import { Head, Link, usePage } from "@inertiajs/react";
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
  Building2,
  Settings,
  Menu,
  X,
  Bell,
  Search,
} from "lucide-react";
import { cn } from "@/lib/utils";

const NAV_GROUPS = [
  {
    label: "Overview",
    items: [{ name: "Dashboard", href: "/dashboard", icon: LayoutDashboard }],
  },
  {
    label: "Operations",
    items: [
      { name: "Inventory", href: "/inventory", icon: Package },
      { name: "Purchasing", href: "/purchasing", icon: ShoppingCart },
      { name: "Production", href: "/production", icon: Factory },
      { name: "Quality", href: "/quality", icon: ShieldCheck },
      { name: "Costing", href: "/costing", icon: Receipt },
    ],
  },
  {
    label: "Commercial",
    items: [
      { name: "Sales", href: "/sales", icon: Truck },
      { name: "Dispatch", href: "/dispatch", icon: Send },
    ],
  },
  {
    label: "Finance",
    items: [{ name: "Accounting", href: "/accounting", icon: BookOpen }],
  },
  {
    label: "Administration",
    items: [
      { name: "Tenancy", href: "/tenancy", icon: Building2 },
      { name: "Settings", href: "/settings", icon: Settings },
    ],
  },
];

interface AppLayoutProps {
  children: React.ReactNode;
  title?: string;
}

export function AppLayout({ children, title }: AppLayoutProps) {
  const { url } = usePage() as any;
  const [mobileOpen, setMobileOpen] = React.useState(false);

  const path = (url || "/").split("?")[0];
  const isActive = (href: string) =>
    path === href || path.startsWith(`${href}/`);

  const currentUser = { name: "Admin User", email: "admin@factoryos.local", role: "Super Admin" };

  return (
    <div className="min-h-screen bg-slate-100/70 text-slate-900">
      <Head title={title ? `${title} — Brixel FactoryOS` : "Brixel FactoryOS"} />

      {/* Top bar */}
      <header className="fixed inset-x-0 top-0 z-40 flex h-16 items-center gap-3 border-b border-slate-200 bg-white/90 px-4 backdrop-blur supports-[backdrop-filter]:bg-white/75 lg:px-6">
        <button
          type="button"
          onClick={() => setMobileOpen(true)}
          className="rounded-lg p-2 text-slate-600 hover:bg-slate-100 hover:text-slate-900 lg:hidden"
          aria-label="Open navigation"
        >
          <Menu size={20} />
        </button>

        <Link href="/dashboard" className="flex items-center gap-2.5">
          <span className="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-sky-500 to-sky-700 text-sm font-bold text-white shadow-sm shadow-sky-600/20">
            B
          </span>
          <span className="hidden flex-col leading-none sm:flex">
            <span className="text-[15px] font-semibold tracking-tight text-slate-900">
              Brixel FactoryOS
            </span>
            <span className="mt-1 text-[11px] font-medium uppercase tracking-wider text-slate-400">
              Manufacturing ERP
            </span>
          </span>
        </Link>

        <div className="ml-auto flex items-center gap-2 sm:gap-3">
          <div className="hidden items-center gap-2 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-400 md:flex">
            <Search size={15} />
            <span className="w-32">Search…</span>
            <kbd className="rounded border border-slate-200 bg-white px-1.5 py-0.5 text-[10px] font-medium text-slate-400">
              ⌘K
            </kbd>
          </div>

          <button
            type="button"
            className="relative rounded-lg p-2 text-slate-600 hover:bg-slate-100 hover:text-slate-900"
            aria-label="Notifications"
          >
            <Bell size={19} />
            <span className="absolute right-1.5 top-1.5 h-2 w-2 rounded-full bg-rose-500 ring-2 ring-white" />
          </button>

          <div className="flex items-center gap-2.5 border-l border-slate-200 pl-3 sm:pl-4">
            <div className="hidden flex-col items-end leading-tight sm:flex">
              <span className="text-sm font-medium text-slate-900">{currentUser.name}</span>
              <span className="text-[11px] font-medium uppercase tracking-wide text-slate-400">
                {currentUser.role}
              </span>
            </div>
            <span className="flex h-9 w-9 items-center justify-center rounded-full bg-slate-900 text-sm font-semibold text-white">
              {currentUser.name.charAt(0)}
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
          className="fixed inset-0 z-40 bg-slate-900/40 backdrop-blur-sm lg:hidden"
        />
      )}

      {/* Sidebar */}
      <aside
        className={cn(
          "fixed bottom-0 left-0 top-16 z-50 w-64 border-r border-slate-200 bg-white transition-transform duration-200 ease-out lg:translate-x-0",
          mobileOpen ? "translate-x-0" : "-translate-x-full"
        )}
      >
        <div className="flex h-full flex-col">
          <nav className="flex-1 overflow-y-auto px-3 py-4">
            {NAV_GROUPS.map((group) => (
              <div key={group.label} className="mb-5">
                <p className="mb-1.5 px-3 text-[10px] font-semibold uppercase tracking-widest text-slate-400">
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
                          "group flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-colors",
                          active
                            ? "bg-sky-50 text-sky-700"
                            : "text-slate-600 hover:bg-slate-50 hover:text-slate-900"
                        )}
                      >
                        <Icon
                          size={17}
                          strokeWidth={2}
                          className={cn(
                            active ? "text-sky-600" : "text-slate-400 group-hover:text-slate-600"
                          )}
                        />
                        <span className="truncate">{item.name}</span>
                        {active && (
                          <span className="ml-auto h-1.5 w-1.5 rounded-full bg-sky-500" />
                        )}
                      </Link>
                    );
                  })}
                </div>
              </div>
            ))}
          </nav>

          <div className="border-t border-slate-200 p-3">
            <div className="flex items-center gap-3 rounded-lg bg-slate-50 px-3 py-2.5">
              <span className="flex h-8 w-8 items-center justify-center rounded-full bg-sky-100 text-xs font-semibold text-sky-700">
                AD
              </span>
              <div className="min-w-0">
                <p className="truncate text-xs font-semibold text-slate-800">Admin Workspace</p>
                <p className="truncate text-[11px] text-slate-500">Production environment</p>
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
    </div>
  );
}

interface PageHeaderProps {
  title: string;
  subtitle?: string;
  actions?: React.ReactNode;
  children?: React.ReactNode;
}

export function PageHeader({ title, subtitle, actions, children }: PageHeaderProps) {
  const aside = actions ?? children;

  return (
    <div className="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
      <div className="min-w-0">
        <h1 className="text-xl font-semibold tracking-tight text-slate-900 sm:text-2xl">
          {title}
        </h1>
        {subtitle && <p className="mt-1 text-sm text-slate-500">{subtitle}</p>}
      </div>
      {aside && <div className="flex flex-wrap items-center gap-2">{aside}</div>}
    </div>
  );
}
