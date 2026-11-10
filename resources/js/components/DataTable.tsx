/**
 * Brixel FactoryOS — DataTable Component with Inertia.js integration
 */

import { Head, Link, usePage } from "@inertiajs/react";
import { Badge } from "@/components/ui/badge";
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table";
import { ChevronDown, Plus, Search } from "lucide-react";
import { cn } from "@/lib/utils";

interface DataTableProps<T extends Record<string, any>> {
  title: string;
  columns: Array<{
    key: string;
    label: string;
    sortable?: boolean;
    format?: (value: any, row: T) => React.ReactNode;
  }>;
  data: T[];
  emptyMessage?: string;
  createUrl?: string;
  meta?: any;
  className?: string;
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
  const sortField = (new URL(url).searchParams.get("sort") || "") as string;
  const sortDir = (new URL(url).searchParams.get("dir") || "asc") as string;

  return (
    <div className={className}>
      <Head title={`${title} — Brixel FactoryOS`} />
      <div className="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
          <h1 className="text-2xl font-bold text-gray-900">{title}</h1>
        </div>
        {createUrl && (
          <Link
            href={createUrl}
            className="flex items-center gap-2 px-4 py-2 bg-factory-600 text-white rounded-lg text-sm font-medium hover:bg-factory-500"
          >
            <Plus size={16} />
            New Record
          </Link>
        )}
      </div>

      <div className="rounded-xl border border-gray-200 bg-white shadow-sm overflow-hidden">
        <Table>
          <TableHeader>
            <TableRow>
              {columns.map((col) => (
                <TableHead key={col.key} className={cn("h-11", col.sortable && "cursor-pointer select-none hover:bg-gray-50")}>
                  {col.label}
                  {col.sortable && (
                    <ChevronDown
                      className={cn("ml-1 h-3 w-3 text-gray-400", {
                        "text-factory-600": sortField === col.key,
                        "rotate-180": sortDir === "desc",
                      })}
                    />
                  )}
                </TableHead>
              ))}
              <TableHead className="w-16" />
            </TableRow>
          </TableHeader>
          <TableBody>
            {data.length === 0 ? (
              <TableRow>
                <TableCell colSpan={columns.length + 1} className="h-24 text-center text-gray-500">
                  {emptyMessage}
                </TableCell>
              </TableRow>
            ) : (
              data.map((row, index) => (
                <TableRow key={index}>
                  {columns.map((col) => (
                    <TableCell key={col.key}>
                      {col.format ? col.format(row[col.key], row) : row[col.key]}
                    </TableCell>
                  ))}
                  <TableCell className="text-right">
                    <Link
                      href={`/${title.toLowerCase().replace(/\s+/g, "-")}/${row.id}`}
                      className="text-factory-600 hover:text-factory-500 text-sm font-medium"
                    >
                      View
                    </Link>
                  </TableCell>
                </TableRow>
              ))
            )}
          </TableBody>
        </Table>
      </div>

      {meta && (
        <div className="mt-4 flex items-center justify-between text-sm text-gray-500">
          <p>
            Showing <span className="font-medium">{meta.from}</span> to{" "}
            <span className="font-medium">{meta.to}</span> of{" "}
            <span className="font-medium">{meta.total}</span> results
          </p>
          <div className="flex items-center space-x-2">
            {meta.current_page > 1 && (
              <button className="px-3 py-1 border border-gray-200 rounded-lg hover:bg-gray-50">
                Previous
              </button>
            )}
            <span className="px-3 py-1 border border-gray-200 rounded-lg">
              Page {meta.current_page} of {meta.last_page}
            </span>
            {meta.current_page < meta.last_page && (
              <button className="px-3 py-1 border border-gray-200 rounded-lg hover:bg-gray-50">
                Next
              </button>
            )}
          </div>
        </div>
      )}
    </div>
  );
}