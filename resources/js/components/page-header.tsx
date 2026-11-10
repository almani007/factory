/**
 * Brixel FactoryOS — Page Header Component
 * Consistent page title, subtitle, actions and optional breadcrumb.
 */

import * as React from "react";
import { ChevronRight } from "lucide-react";
import { cn } from "@/lib/utils";

interface BreadcrumbItem {
  label: string;
  href?: string;
}

interface PageHeaderProps {
  title: string;
  subtitle?: string;
  actions?: React.ReactNode;
  children?: React.ReactNode;
  breadcrumbs?: BreadcrumbItem[];
}

export function PageHeader({
  title,
  subtitle,
  actions,
  children,
  breadcrumbs,
}: PageHeaderProps) {
  const aside = actions ?? children;

  return (
    <div className="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between gap-3">
      <div className="min-w-0 flex flex-col gap-1">
        {breadcrumbs && breadcrumbs.length > 0 && (
          <div className="flex flex-wrap items-center gap-1 text-[11px] font-medium uppercase tracking-wider text-slate-400">
            {breadcrumbs.map((item, index) => (
              <React.Fragment key={item.label || index}>
                {index > 0 && (
                  <ChevronRight size={13} className="mx-1 text-slate-300" />
                )}
                {item.href ? (
                  <a
                    href={item.href}
                    className="text-slate-500 hover:text-factory-600 transition-colors"
                  >
                    {item.label}
                  </a>
                ) : (
                  <span className="text-slate-600">{item.label}</span>
                )}
              </React.Fragment>
            ))}
          </div>
        )}
        <h1 className="text-xl font-semibold tracking-tight text-slate-900 sm:text-2xl">
          {title}
        </h1>
        {subtitle && <p className="mt-1 text-sm text-slate-500">{subtitle}</p>}
      </div>
      {aside && <div className="flex flex-wrap items-center gap-2">{aside}</div>}
    </div>
  );
}
