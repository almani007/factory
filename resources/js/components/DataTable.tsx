import * as React from "react";
/**
 * Brixel FactoryOS — DataTable
 * Server-driven table: Inertia visits handle search, sorting and pagination.
 * Professional styling with zebra rows, status badges, and clean typography.
 */

import { Head, Link, router, usePage } from "@inertiajs/react";
import { ChevronDown, ChevronLeft, ChevronRight, Inbox, Plus, Search } from "lucide-react";
import { cn } from "@/lib/utils";

interface Column<T> {
  key: string;
  label: string;
  sortable?: boolean;
  align?: "left" | "right" | "center";
  format?: (value: any, row: T) => React.ReactNode;
}

interface DataTableProps<T extends Record<string, any>> {
  title: string;
  columns: Column<T>[];
  data: T[];
  emptyMessage?: string;
  createUrl?: string;
  meta?: {
    current_page?: number;
    last_page?: number;
    per_page?: number;
    total?: number;
    from?: number;
    to?: number;
  };
  className?: string;
}

const STATUS_STYLES: Record<string, string> = {
  active: "bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-200",
  posted: "bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-200",
  completed: "bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-200",
  closed: "bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-200",
  approved: "bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-200",
  released: "bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-200",
  confirmed: "bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-200",
  delivered: "bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-200",
  passed: "bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-200",
  pending: "bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-200",
  pending_approval: "bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-200",
  submitted: "bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-200",
  partially_accepted: "bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-200",
  rework_required: "bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-200",
  draft: "bg-slate-100 text-slate-600 ring-1 ring-inset ring-slate-200",
  inactive: "bg-slate-100 text-slate-500 ring-1 ring-inset ring-slate-200",
  suspended: "bg-slate-100 text-slate-500 ring-1 ring-inset ring-slate-200",
  in_progress: "bg-sky-50 text-sky-700 ring-1 ring-inset ring-sky-200",
  in_production: "bg-sky-50 text-sky-700 ring-1 ring-inset ring-sky-200",
  in_transit: "bg-sky-50 text-sky-700 ring-1 ring-inset ring-sky-200",
  material_reserved: "bg-sky-50 text-sky-700 ring-1 ring-inset ring-sky-200",
  quality_inspection: "bg-indigo-50 text-indigo-700 ring-1 ring-inset ring-indigo-200",
  rejected: "bg-rose-50 text-rose-700 ring-1 ring-inset ring-rose-200",
  cancelled: "bg-rose-50 text-rose-700 ring-1 ring-inset ring-rose-200",
  failed: "bg-rose-50 text-rose-700 ring-1 ring-inset ring-rose-200",
};

function StatusBadge({ value }: { value: string }) {
  const style = STATUS_STYLES[value] || "bg-slate-100 text-slate-600 ring-1 ring-inset ring-slate-200";

  return (
    <span
      className={cn(
        "inline-flex items-center rounded-full px-2.5 py-0.5 text-[11px] font-semibold capitalize",
        style
      )}
    >
      {value.replace(/_/g, " ")}
    </span>
  );
}

export function DataTable<T extends Record<string, any>>({
  title,
  columns,
  data,
  emptyMessage = "No records found.",
  createUrl,
  meta,
  className,
}: DataTableProps<T>) {
  const { url } = usePage() as any;
  const basePath = (url || "").split("?")[0];
  const searchParams = new URLSearchParams((url || "").split("?")[1] || "");
  const sortField = searchParams.get("sort") || "";
  const sortDir = searchParams.get("dir") || "asc";
  const query = searchParams.get("q") || "";

  const total = meta?.total ?? data.length;
  const currentPage = meta?.current_page ?? 1;
  const lastPage = meta?.last_page ?? 1;

  const hrefWith = (overrides: Record<string, string | null>) => {
    const next = new URLSearchParams(searchParams);

    Object.entries(overrides).forEach(([key, value]) => {
      if (value === null || value === "") {
        next.delete(key);
      } else {
        next.set(key, value);
      }
    });

    const suffix = next.toString();
    return suffix ? `${basePath}?${suffix}` : basePath;
  };

  const submitSearch = (event: React.FormEvent<HTMLFormElement>) => {
    event.preventDefault();
    const value = String(new FormData(event.currentTarget).get("q") || "").trim();
    router.visit(hrefWith({ q: value || null, page: null }), { preserveState: true });
  };

  const sortBy = (key: string) =>
    router.visit(
      hrefWith({
        sort: key,
        dir: sortField === key && sortDir === "asc" ? "desc" : "asc",
        page: null,
      }),
      { preserveState: true }
    );

  return (
    <div className={className}>
      <Head title={`${title} — Brixel FactoryOS`} />

      <div className="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm transition-all">
        <div className="flex flex-col gap-3 border-b border-slate-200/60 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
          <div>
            <h2 className="text-base font-semibold tracking-tight text-slate-900">{title}</h2>
            <p className="mt-0.5 text-xs text-slate-500">
              {total} {total === 1 ? "record" : "records"}
              {query && <> matching “{query}”</>}
            </p>
          </div>

          <div className="flex items-center gap-2">
            <form onSubmit={submitSearch} className="relative hidden md:block">
              <Search
                size={15}
                className="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"
              />
              <input
                type="search"
                name="q"
                defaultValue={query}
                placeholder="Search…"
                className="h-9 w-52 rounded-lg border border-slate-200 bg-slate-50 pl-9 pr-3 text-sm text-slate-700 placeholder:text-slate-400 focus:border-factory-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-factory-500"
              />
            </form>

            {createUrl && (
              <Link
                href={createUrl}
                className="inline-flex h-9 items-center gap-1.5 rounded-lg bg-factory-600 px-3 text-sm font-medium text-white shadow-sm transition-colors hover:bg-factory-700"
              >
                <Plus size={15} />
                New
              </Link>
            )}
          </div>
        </div>

        <div className="overflow-x-auto">
          <table className="w-full border-collapse text-sm">
            <thead>
              <tr className="border-b border-slate-200 bg-slate-50/80">
                {columns.map((col) => (
                  <th
                    key={col.key}
                    onClick={col.sortable ? () => sortBy(col.key) : undefined}
                    className={cn(
                      "whitespace-nowrap px-5 py-3 text-[11px] font-semibold uppercase tracking-wider text-slate-500",
                      col.align === "right" ? "text-right" : col.align === "center" ? "text-center" : "text-left",
                      col.sortable && "cursor-pointer select-none hover:text-slate-700"
                    )}
                  >
                    <span className="inline-flex items-center gap-1">
                      {col.label}
                      {col.sortable && (
                        <ChevronDown
                          size={12}
                          className={cn("text-slate-400", {
                            "text-factory-600": sortField === col.key,
                            "rotate-180": sortField === col.key && sortDir === "desc",
                          })}
                        />
                      )}
                    </span>
                  </th>
                ))}
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-100">
              {data.length === 0 ? (
                <tr>
                  <td colSpan={columns.length} className="px-5 py-14 text-center">
                    <Inbox size={22} className="mx-auto mb-2 text-slate-300" />
                    <p className="text-sm font-medium text-slate-600">{emptyMessage}</p>
                    <p className="mt-0.5 text-xs text-slate-400">
                      Records will appear here once they are created.
                    </p>
                  </td>
                </tr>
              ) : (
                data.map((row, index) => (
                  <tr key={row.id ?? index} className="transition-colors hover:bg-slate-50/70">
                    {columns.map((col) => {
                      const value = row[col.key];

                      return (
                        <td
                          key={col.key}
                          className={cn(
                            "whitespace-nowrap px-5 py-3 text-slate-700",
                            col.align === "right" ? "text-right" : col.align === "center" ? "text-center" : "text-left"
                          )}
                        >
                          {col.format ? (
                            col.format(value, row)
                          ) : col.key === "status" && typeof value === "string" ? (
                            <StatusBadge value={value} />
                          ) : (
                            (value ?? "—")
                          )}
                        </td>
                      );
                    })}
                  </tr>
                ))
              )}
            </tbody>
          </table>
        </div>

        {meta && (
          <div className="flex flex-col gap-3 border-t border-slate-200/60 px-5 py-3 text-sm text-slate-500 sm:flex-row sm:items-center sm:justify-between">
            <p>
              Showing <span className="font-medium text-slate-700">{meta.from ?? 0}</span>–
              <span className="font-medium text-slate-700">{meta.to ?? 0}</span> of{" "}
              <span className="font-medium text-slate-700">{total}</span>
            </p>
            <div className="flex items-center gap-2">
              <PageButton
                href={hrefWith({ page: String(currentPage - 1) })}
                disabled={currentPage <= 1}
                label="Previous"
                icon="left"
              />
              <span className="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-medium text-slate-600">
                Page {currentPage} of {lastPage}
              </span>
              <PageButton
                href={hrefWith({ page: String(currentPage + 1) })}
                disabled={currentPage >= lastPage}
                label="Next"
                icon="right"
              />
            </div>
          </div>
        )}
      </div>
    </div>
  );
}

function PageButton({
  href,
  disabled,
  label,
  icon,
}: {
  href: string;
  disabled: boolean;
  label: string;
  icon: "left" | "right";
}) {
  const className = cn(
    "inline-flex items-center gap-1 rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-medium transition-colors",
    disabled
      ? "cursor-not-allowed text-slate-300"
      : "text-slate-600 hover:bg-slate-50 hover:text-slate-900"
  );

  if (disabled) {
    return (
      <span className={className}>
        {icon === "left" && <ChevronLeft size={14} />}
        {label}
        {icon === "right" && <ChevronRight size={14} />}
      </span>
    );
  }

  return (
    <Link href={href} className={className} preserveScroll>
      {icon === "left" && <ChevronLeft size={14} />}
      {label}
      {icon === "right" && <ChevronRight size={14} />}
    </Link>
  );
}
