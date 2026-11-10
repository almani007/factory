import * as React from "react";
/**
 * Brixel FactoryOS — StatStrip
 * Compact headline metrics above a data table with tone-based coloring.
 */

import { cn } from "@/lib/utils";

interface Stat {
  label: string;
  value: string | number;
  hint?: string;
  tone?: "default" | "success" | "danger" | "warning" | "info";
}

const TONE_STYLE: Record<NonNullable<Stat["tone"]>, string> = {
  default: "text-slate-900",
  success: "text-emerald-600",
  danger: "text-rose-600",
  warning: "text-amber-600",
  info: "text-sky-600",
};

export function StatStrip({ items }: { items: Stat[] }) {
  return (
    <div className="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
      {items.map((item) => (
        <div
          key={item.label}
          className="rounded-xl border border-slate-200 bg-white p-5 shadow-sm transition-all hover:shadow-md hover:-translate-y-0.5"
        >
          <p className="text-[11px] font-semibold uppercase tracking-wider text-slate-500">
            {item.label}
          </p>
          <p
            className={cn(
              "mt-2 text-2xl font-semibold tracking-tight tabular-nums",
              TONE_STYLE[item.tone ?? "default"]
            )}
          >
            {item.value}
          </p>
          {item.hint && <p className="mt-1 text-xs text-slate-500">{item.hint}</p>}
        </div>
      ))}
    </div>
  );
}
