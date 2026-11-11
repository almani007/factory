/**
 * Brixel FactoryOS — Notifications
 * Notification center with sound, read/unread filtering, and bulk actions.
 */

import * as React from "react";
import { Head, Link, router, usePage } from "@inertiajs/react";
import { AppLayout } from "@/components/layout";
import { PageHeader } from "@/components/layout";
import { DataTable } from "@/components/DataTable";
import { Bell, X, CheckCircle2, AlertTriangle, Info, RefreshCw, Play, Volume2, VolumeX } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import { cn } from "@/lib/utils";

interface Notification {
  id: number;
  title: string;
  body: string;
  type: string;
  data: Record<string, any>;
  created_at: string;
  is_read: boolean;
  actor_name: string;
}

const TYPE_CONFIG: Record<string, { icon: React.ReactNode; color: string; label: string }> = {
  info: { icon: <Info size={14} className="text-blue-600" />, color: "bg-blue-50 text-blue-700 ring-1 ring-inset ring-blue-200", label: "Info" },
  warning: { icon: <AlertTriangle size={14} className="text-amber-600" />, color: "bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-200", label: "Warning" },
  error: { icon: <X size={14} className="text-rose-600" />, color: "bg-rose-50 text-rose-700 ring-1 ring-inset ring-rose-200", label: "Error" },
  success: { icon: <CheckCircle2 size={14} className="text-emerald-600" />, color: "bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-200", label: "Success" },
};

export default function Notifications() {
  const { props: data } = usePage() as any;
  const notifications: Notification[] = data?.notifications?.data || [];
  const meta = data?.notifications?.meta || {};
  const unreadCount: number = data?.unread_count || 0;

  const [soundEnabled, setSoundEnabled] = React.useState(true);
  const [filter, setFilter] = React.useState<"all" | "unread" | "read">("all");
  const [isMarkingAll, setIsMarkingAll] = React.useState(false);
  const audioRef = React.useRef<HTMLAudioElement | null>(null);

  const filteredNotifications = notifications.filter((n) => {
    if (filter === "unread") return !n.is_read;
    if (filter === "read") return n.is_read;
    return true;
  });

  const markRead = (id: number) => {
    router.post(`/notifications/${id}/read`, {}, {
      preserveScroll: true,
      onSuccess: () => {
        if (soundEnabled && audioRef.current) {
          audioRef.current.currentTime = 0;
          audioRef.current.play().catch(() => {});
        }
      },
    });
  };

  const markAllRead = () => {
    setIsMarkingAll(true);
    router.post("/notifications/read-all", {}, {
      preserveScroll: true,
      onSuccess: () => {
        setIsMarkingAll(false);
        if (soundEnabled && audioRef.current) {
          audioRef.current.currentTime = 0;
          audioRef.current.play().catch(() => {});
        }
      },
    });
  };

  const toggleSound = () => {
    setSoundEnabled(!soundEnabled);
    if (!soundEnabled && audioRef.current) {
      audioRef.current.currentTime = 0;
      audioRef.current.play().catch(() => {});
    }
  };

  return (
    <AppLayout title="Notifications">
      <PageHeader
        title="Notifications"
        subtitle="Notification center with sound alerts and read tracking"
        breadcrumbs={[{ label: "Notifications", href: "/notifications" }]}
        actions={
          <div className="flex items-center gap-3">
            <Button
              onClick={markAllRead}
              disabled={isMarkingAll || unreadCount === 0}
              variant="outline"
              size="sm"
            >
              {isMarkingAll ? <RefreshCw className="animate-spin" size={14} /> : <CheckCircle2 size={14} />}
              Mark all as read
            </Button>
            <Link
              href="/notifications/test"
              className="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-factory-50 text-factory-700 font-medium text-sm hover:bg-factory-100 transition-colors"
            >
              <Bell size={14} /> Send Test
            </Link>
            <Button
              onClick={toggleSound}
              variant="ghost"
              size="sm"
              className={cn("text-slate-500 hover:text-factory-600", soundEnabled ? "" : "opacity-50")}
            >
              {soundEnabled ? <Volume2 size={16} /> : <VolumeX size={16} />}
            </Button>
          </div>
        }
      />

      {/* Stats bar */}
      <div className="mb-6 grid grid-cols-1 md:grid-cols-4 gap-4">
        <div className="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
          <p className="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Total</p>
          <p className="mt-2 text-2xl font-semibold text-slate-900 tabular-nums">{notifications.length}</p>
        </div>
        <div className="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
          <p className="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Unread</p>
          <p className={cn(
            "mt-2 text-2xl font-semibold tabular-nums",
            unreadCount > 0 ? "text-rose-600" : "text-emerald-600"
          )}>
            {unreadCount}
          </p>
        </div>
        <div className="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
          <p className="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Read</p>
          <p className="mt-2 text-2xl font-semibold text-slate-900 tabular-nums">{notifications.length - unreadCount}</p>
        </div>
        <div className="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
          <p className="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Sound</p>
          <p className="mt-2 text-2xl font-semibold text-slate-900 tabular-nums">
            {soundEnabled ? "On" : "Off"}
          </p>
        </div>
      </div>

      {/* Filter tabs */}
      <div className="mb-6 flex flex-wrap gap-2">
        {["all", "unread", "read"].map((f) => (
          <button
            key={f}
            onClick={() => setFilter(f as "all" | "unread" | "read")}
            className={cn(
              "px-4 py-2 rounded-lg text-sm font-medium transition-colors",
              filter === f
                ? "bg-factory-50 text-factory-700 border border-factory-200 shadow-sm"
                : "bg-white text-slate-600 border border-slate-200 hover:bg-slate-50"
            )}
          >
            {f.charAt(0).toUpperCase() + f.slice(1)} ({filteredNotifications.length})
          </button>
        ))}
      </div>

      {/* Notifications list */}
      <div className="rounded-xl border border-slate-200 bg-white shadow-sm overflow-hidden">
        {filteredNotifications.length === 0 ? (
          <div className="px-6 py-12 text-center">
            <div className="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 mb-3">
              <Bell size={20} className="text-slate-400" />
            </div>
            <h3 className="text-base font-semibold text-slate-900">No notifications</h3>
            <p className="mt-1 text-sm text-slate-500">
              {filter === "all"
                ? "You don't have any notifications yet."
                : filter === "unread"
                ? "All notifications have been read."
                : "No read notifications."}
            </p>
            {filter !== "all" && (
              <button
                onClick={() => setFilter("all")}
                className="mt-4 text-sm font-medium text-factory-600 hover:text-factory-700"
              >
                Show all notifications
              </button>
            )}
          </div>
        ) : (
          <div className="divide-y divide-slate-100">
            {filteredNotifications.map((notification) => (
              <div
                key={notification.id}
                onClick={() => markRead(notification.id)}
                className={cn(
                  "px-5 py-4 flex items-start gap-3 cursor-pointer hover:bg-slate-50 transition-colors border-b border-slate-50 last:border-b-0",
                  !notification.is_read ? "bg-factory-50/30 border-l-2 border-factory-500" : "",
                )}
              >
                <audio ref={audioRef} preload="auto">
                  <source src="/sounds/notification.mp3" type="audio/mpeg" />
                </audio>
                <div className={`mt-0.5 shrink-0 rounded-full p-1.5 ${TYPE_CONFIG[notification.type]?.color ?? TYPE_CONFIG.info.color}`}>
                  {TYPE_CONFIG[notification.type]?.icon ?? TYPE_CONFIG.info.icon}
                </div>
                <div className="min-w-0 flex-1">
                  <div className="flex items-baseline gap-2">
                    <p className={cn("text-sm font-semibold", !notification.is_read ? "text-slate-900" : "text-slate-500")}>
                      {notification.title}
                    </p>
                    {!notification.is_read && (
                      <span className="h-1.5 w-1.5 rounded-full bg-factory-500" />
                    )}
                  </div>
                  <p className="mt-0.5 text-sm text-slate-600">{notification.body}</p>
                  <div className="mt-1 flex items-center gap-2">
                    <span className="text-[10px] font-medium uppercase tracking-wider text-slate-400">
                      {notification.created_at}
                    </span>
                    <span className="text-[10px] font-medium uppercase tracking-wider text-slate-400">
                      • {notification.actor_name}
                    </span>
                    <span className="ml-auto text-[10px] font-medium uppercase tracking-wider text-slate-400">
                      {notification.is_read ? "Read" : "Unread"}
                    </span>
                  </div>
                </div>
              </div>
            ))}
          </div>
        )}
      </div>

      {/* Pagination */}
      {meta && meta.total > meta.per_page && (
        <div className="mt-6 flex flex-col gap-3 border-t border-slate-200/60 px-5 py-3 text-sm text-slate-500 sm:flex-row sm:items-center sm:justify-between">
          <p>
            Showing <span className="font-medium text-slate-700">{meta.from ?? 0}</span>–{" "}
            <span className="font-medium text-slate-700">{meta.to ?? 0}</span> of{" "}
            <span className="font-medium text-slate-700">{meta.total}</span>
          </p>
          <div className="flex items-center gap-2">
            <Button
              asChild
              href={`/notifications?page=${Math.max(1, meta.current_page - 1)}&filter=${filter}`}
              disabled={meta.current_page <= 1}
              variant="outline"
              size="sm"
            >
              Previous
            </Button>
            <span className="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-medium text-slate-600">
              Page {meta.current_page} of {meta.last_page}
            </span>
            <Button
              asChild
              href={`/notifications?page=${Math.min(meta.last_page, meta.current_page + 1)}&filter=${filter}`}
              disabled={meta.current_page >= meta.last_page}
              variant="outline"
              size="sm"
            >
              Next
            </Button>
          </div>
        </div>
      )}
    </AppLayout>
  );
}
