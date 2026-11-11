/**
 * Brixel FactoryOS — KPI Card Component
 * Premium KPI card with trend indicator and optional icon.
 */

import * as React from "react";
import { cn } from "@/lib/utils";

interface KPICardProps {
  title: string;
  value: string | number;
  change?: string;
  trend?: "up" | "down" | "neutral";
  icon?: React.ReactNode;
  className?: string;
}

const TREND_STYLE: Record<
  NonNullable<KPICardProps["trend"]>,
  { bg: string; text: string; ring: string; label: string }
> = {
  up: { bg: "bg-emerald-50", text: "text-emerald-600", ring: "ring-emerald-100", label: "▲" },
  down: { bg: "bg-rose-50", text: "text-rose-600", ring: "ring-rose-100", label: "▼" },
  neutral: { bg: "bg-slate-100", text: "text-slate-600", ring: "ring-slate-200", label: "—" },
};

export function KPICard({ title, value, change, trend, icon, className }: KPICardProps) {
  const style = TREND_STYLE[trend ?? "neutral"];

  return (
    <div
      className={cn(
        "rounded-xl border border-slate-200 bg-white p-5 shadow-sm transition-all duration-200 hover:shadow-md hover:-translate-y-0.5",
        className
      )}
    >
      <div className="flex items-start justify-between gap-3">
        <div className="min-w-0">
          <p className="text-[10px] font-semibold uppercase tracking-wider text-slate-400">
            {title}
          </p>
          <p className="mt-1.5 whitespace-nowrap text-xl font-semibold tracking-tight text-slate-900 tabular-nums xl:text-2xl">
            {value}
          </p>
        </div>
        {icon && (
          <div className="w-10 h-10 rounded-xl bg-slate-50/60 flex items-center justify-center border border-slate-100/60">
            {icon}
          </div>
        )}
      </div>
      {change && (
        <div className="mt-3">
          <span className={cn(
            "inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-semibold whitespace-nowrap",
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
