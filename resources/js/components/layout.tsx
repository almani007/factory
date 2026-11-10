/**
 * Brixel FactoryOS — Layout Components
 */

import { Head, Link, usePage } from "@inertiajs/react";
import {
  Layout,
  Package,
  Truck,
  Factory,
  Shield,
  CreditCard,
  Settings,
  User,
  Menu,
  X,
  ChevronDown,
  Bell,
  LogOut,
} from "lucide-react";
import { cn } from "@/lib/utils";

const NAV_ITEMS = [
  { name: "Dashboard", href: "/dashboard", icon: Layout },
  { name: "Inventory", href: "/inventory", icon: Package },
  { name: "Purchasing", href: "/purchasing", icon: Truck },
  { name: "Production", href: "/production", icon: Factory },
  { name: "Quality", href: "/quality", icon: Shield },
  { name: "Costing", href: "/costing", icon: CreditCard },
  { name: "Sales", href: "/sales", icon: CreditCard },
  { name: "Dispatch", href: "/dispatch", icon: Truck },
  { name: "Accounting", href: "/accounting", icon: CreditCard },
  { name: "Settings", href: "/settings", icon: Settings },
];

interface AppLayoutProps {
  children: React.ReactNode;
  title?: string;
}

export function AppLayout({ children, title }: AppLayoutProps) {
  const { url } = usePage() as any;
  const [sidebarOpen, setSidebarOpen] = React.useState(true);
  const [userMenuOpen, setUserMenuOpen] = React.useState(false);

  const currentUser = url?.split("/").includes("login")
    ? null
    : { name: "Admin User", email: "admin@factoryos.local", role: "Super Admin" };

  return (
    <div className="min-h-screen bg-gray-50 text-gray-900">
      <Head title={title ? `${title} — Brixel FactoryOS` : "Brixel FactoryOS"} />

      {/* Top Bar */}
      <header className="fixed top-0 left-0 right-0 h-16 bg-white border-b z-20 flex items-center justify-between px-6">
        <div className="flex items-center gap-4">
          <button
            onClick={() => setSidebarOpen((prev) => !prev)}
            className="md:hidden p-2 text-gray-600 hover:text-gray-900"
          >
            {sidebarOpen ? <X size={20} /> : <Menu size={20} />}
          </button>
          <Link
            href="/dashboard"
            className="flex items-center gap-2"
          >
            <div className="w-8 h-8 bg-factory-600 rounded-lg flex items-center justify-center text-white font-bold">
              B
            </div>
            <span className="font-bold text-xl tracking-tight">Brixel FactoryOS</span>
          </Link>
        </div>

        <div className="flex items-center gap-4">
          <button
            onClick={() => setUserMenuOpen((prev) => !prev)}
            className="relative p-2 text-gray-600 hover:text-gray-900"
          >
            <Bell size={20} />
            {userMenuOpen && (
              <span className="absolute -top-1 -right-1 w-2 h-2 bg-red-500 rounded-full border-2 border-white"></span>
            )}
          </button>
          <div className="flex items-center gap-2 pl-4 border-l border-gray-200">
            <div className="hidden md:flex flex-col">
              <p className="text-sm font-medium text-gray-900">
                {currentUser?.name || "Guest User"}
              </p>
              <p className="text-xs text-gray-500">
                {currentUser?.role || "Guest"}
              </p>
            </div>
            <div className="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center text-sm font-medium text-gray-700">
              {currentUser?.name?.charAt(0) || "U"}
            </div>
          </div>
        </div>
      </header>

      {/* Sidebar */}
      <aside
        className={cn(
          "fixed top-16 left-0 bottom-0 w-64 bg-white border-r transform transition-transform duration-300 z-30",
          sidebarOpen ? "translate-x-0" : "-translate-x-full",
          "md:translate-x-0 md:static md:inset-0 md:flex md:h-auto md:w-auto md:border-0"
        )}
      >
        <nav className="p-4 space-y-1">
          {NAV_ITEMS.map((item) => {
            const isActive = url === item.href || url.startsWith(item.href + "/");
            const Icon = item.icon;
            return (
              <Link
                key={item.name}
                href={item.href}
                className={({ isActive: isActiveRoute }) =>
                  cn(
                    "flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium transition-colors",
                    isActiveRoute
                      ? "bg-factory-50 text-factory-700"
                      : "text-gray-600 hover:bg-gray-100 hover:text-gray-900"
                  )}
              >
                <Icon size={18} strokeWidth={2} />
                {item.name}
              </Link>
            );
          })}
          <div className="mt-8 pt-4 border-t border-gray-100">
            <Link
              href="/settings"
              className="flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100 hover:text-gray-900"
            >
              <Settings size={18} strokeWidth={2} />
              Settings
            </Link>
          </div>
        </nav>
      </aside>

      {/* Main Content */}
      <main className={`flex-1 mt-16 ${sidebarOpen ? "md:ml-64" : ""} p-6`}>
        {children}
      </main>
    </div>
  );
}

interface PageHeaderProps {
  title: string;
  subtitle?: string;
  actions?: React.ReactNode;
}

export function PageHeader({ title, subtitle, actions }: PageHeaderProps) {
  return (
    <div className="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div>
        <h1 className="text-2xl font-bold text-gray-900">{title}</h1>
        {subtitle && <p className="text-gray-600">{subtitle}</p>}
      </div>
      {actions && <div className="flex gap-2">{actions}</div>}
    </div>
  );
}