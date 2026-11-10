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
    "text-xs font-medium",
    trend === "up" ? "text-green-600" : trend === "down" ? "text-red-600" : "text-gray-500"
  );

  return (
    <div className="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
      <div className="flex items-center justify-between mb-4">
        <div>
          <p className="text-sm text-gray-500">{title}</p>
          <p className="text-2xl font-bold text-gray-900">{value}</p>
        </div>
        {icon && (
          <div className="p-2 bg-factory-50 rounded-lg text-factory-600">
            {icon}
          </div>
        )}
      </div>
      {change && (
        <div className={cn("flex items-center gap-1", trendClass)}>
          {trend === "up" ? "▲" : trend === "down" ? "▼" : "—"}{" "}
          {change}
        </div>
      )}
    </div>
  );
}