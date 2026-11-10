import * as React from "react";
/**
 * Brixel FactoryOS — KPI Card Component
 */

import { cn } from "@/lib/utils";

interface KPICardProps {
  title: string;
  value: string | number;
  change?: string;
  trend?: "up" | "down" | "neutral";
  icon?: React.ReactNode;
}

export function KPICard({ title, value, change, trend, icon }: KPICardProps) {
  const trendClass = cn(
    "inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[11px] font-semibold",
    trend === "up"
      ? "bg-emerald-50 text-emerald-700"
      : trend === "down"
        ? "bg-rose-50 text-rose-700"
        : "bg-slate-100 text-slate-600"
  );

  return (
    <div className="rounded-xl border border-slate-200 bg-white p-5 shadow-sm transition-shadow hover:shadow-md">
      <div className="flex items-start justify-between gap-3">
        <div className="min-w-0">
          <p className="text-[11px] font-semibold uppercase tracking-wider text-slate-500">
            {title}
          </p>
          <p className="mt-2 whitespace-nowrap text-xl font-semibold tracking-tight text-slate-900 tabular-nums xl:text-2xl">
            {value}
          </p>
        </div>
        {icon && (
          <span className="rounded-lg bg-sky-50 p-2 text-sky-600 ring-1 ring-inset ring-sky-100">
            {icon}
          </span>
        )}
      </div>
      {change && (
        <div className="mt-3">
          <span className={cn(trendClass, "whitespace-nowrap")}>
            {trend === "up" ? "▲" : trend === "down" ? "▼" : "—"} {change}
          </span>
        </div>
      )}
    </div>
  );
}
