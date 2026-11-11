/**
 * Brixel FactoryOS — StatStrip
 * Professional animated stat cards with gradients and hover effects.
 */

import * as React from "react";
import { cn } from "@/lib/utils";

interface Stat {
  label: string;
  value: string | number;
  hint?: string;
  tone?: "default" | "success" | "danger" | "warning" | "info";
  icon?: React.ReactNode;
  gradient?: string;
}

export function StatStrip({ items }: { items: Stat[] }) {
  return (
    <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 xl:grid-cols-5">
      {items.map((item) => (
        <div key={item.label} className={cn(
          "rounded-xl border border-slate-200 bg-white p-5 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300",
          item.gradient && `bg-gradient-to-br from-factory-600/10 to-factory-900/20 border-factory-600/20`
        )}
        >
          <div className="flex items-start justify-between">
            <div className="flex items-center gap-3">
              <div className="w-8 h-8 rounded-lg flex items-center justify-center {item.gradient || 'bg-slate-100 text-slate-600'}">
                {item.icon}
              </div>
            </div>
            <div className="flex items-end gap-1">
              <p className="text-sm font-medium text-slate-900 tabular-nums">{item.value}</p>
              <span className="text-xs text-slate-500">{item.hint}</span>
            </div>
          </div>
          <p className="text-[10px] font-semibold uppercase tracking-wider text-slate-400 mt-4">
            {item.label}
          </p>
        </div>
      ))}
    </div>
  );
}
