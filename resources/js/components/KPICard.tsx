import * as React from "react";
/**
 * Brixel FactoryOS — KPI Card Component
 * Elevated KPI card with trend indicator and optional icon.
 */

import { cn } from "@/lib/utils";

interface KPICardProps {
  title: string;
  value: string | number;
  change?: string;
  trend?: "up" | "down" | "neutral";
  icon?: React.ReactNode;
}

const TREND_STYLE: Record<
  NonNullable<KPICardProps["trend"]>,
  { bg: string; text: string; ring: string; label: string }
> = {
  up: { bg: "bg-emerald-50", text: "text-emerald-700", ring: "ring-emerald-100", label: "▲" },
  down: { bg: "bg-rose-50", text: "text-rose-700", ring: "ring-rose-100", label: "▼" },
  neutral: { bg: "bg-slate-100", text: "text-slate-600", ring: "ring-slate-200", label: "—" },
};

export function KPICard({ title, value, change, trend, icon }: KPICardProps) {
  const style = TREND_STYLE[trend ?? "neutral"];

  return (
    <div className="rounded-xl border border-slate-200 bg-white p-5 shadow-sm transition-all hover:shadow-md hover:-translate-y-0.5">
      <div className="flex items-start justify-between gap-3">
        <div className="min-w-0">
          <p className="text-[11px] font-semibold uppercase tracking-wider text-slate-500">
            {title}
          </p>
          <p className="mt-1.5 whitespace-nowrap text-xl font-semibold tracking-tight text-slate-900 tabular-nums xl:text-2xl">
            {value}
          </p>
        </div>
        {icon && (
          <span className="rounded-lg bg-factory-50 p-2 text-factory-600 ring-1 ring-inset ring-factory-100">
            {icon}
          </span>
        )}
      </div>
      {change && (
        <div className="mt-3">
          <span className={cn(
            "inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[11px] font-semibold whitespace-nowrap",
            style.bg,
            style.text,
            style.ring
          )}>
            {style.label} {change}
          </span>
        </div>
      )}
    </div>
  );
}
