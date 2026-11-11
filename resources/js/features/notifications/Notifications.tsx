/**
 * Brixel FactoryOS — Notifications
 * Advanced notification center with server-side filters, search, and actions.
 */

import * as React from "react";
import { Link, router, usePage } from "@inertiajs/react";
import { AppLayout } from "@/components/layout";
import { PageHeader } from "@/components/layout";
import { Bell, X, CheckCircle2, AlertTriangle, Info, RefreshCw, Volume2, VolumeX, Search, Trash2, Filter } from "lucide-react";
import { Button } from "@/components/ui/button";
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
  const { props: data, url } = usePage() as any;
  const notifications: Notification[] = data?.notifications?.data || [];
  const meta = data?.notifications?.meta || {};
  const unreadCount: number = data?.unread_count || 0;
  const summary = data?.notification_summary || { total: meta.total ?? notifications.length, unread: unreadCount, read: Math.max(0, (meta.total ?? notifications.length) - unreadCount), by_type: {} };
  const filters = data?.filters || {};

  const [soundEnabled, setSoundEnabled] = React.useState(true);
  const [q, setQ] = React.useState(filters.q || "");
  const [status, setStatus] = React.useState(filters.status || "all");
  const [type, setType] = React.useState(filters.type || "all");
  const [busy, setBusy] = React.useState<string | null>(null);
  const audioRef = React.useRef<HTMLAudioElement | null>(null);

  const applyFilters = (next?: Partial<{ q: string; status: string; type: string }>) => {
    const payload = { q, status, type, ...next };
    router.get("/notifications", {
      q: payload.q || undefined,
      status: payload.status && payload.status !== 'all' ? payload.status : undefined,
      type: payload.type && payload.type !== 'all' ? payload.type : undefined,
    }, { preserveState: true, replace: true, preserveScroll: true });
  };

  const markRead = (id: number, isRead: boolean) => {
    if (isRead) return;
    router.post(`/notifications/${id}/read`, {}, {
      preserveScroll: true,
      onSuccess: () => {
        if (soundEnabled && audioRef.current) { audioRef.current.currentTime = 0; audioRef.current.play().catch(() => {}); }
      },
    });
  };

  const markAllRead = () => {
    setBusy('read-all');
    router.post("/notifications/read-all", {}, { preserveScroll: true, onFinish: () => setBusy(null) });
  };

  const deleteRead = () => {
    if (!confirm('Delete all read notifications?')) return;
    setBusy('delete-read');
    router.post("/notifications/delete-read", {}, { preserveScroll: true, onFinish: () => setBusy(null) });
  };

  const deleteOne = (e: React.MouseEvent, id: number) => {
    e.stopPropagation();
    if (!confirm('Delete this notification?')) return;
    router.delete(`/notifications/${id}`, { preserveScroll: true });
  };

  const toggleSound = () => setSoundEnabled(!soundEnabled);
  const pageHref = (page: number) => {
    const params = new URLSearchParams();
    if (q) params.set('q', q);
    if (status && status !== 'all') params.set('status', status);
    if (type && type !== 'all') params.set('type', type);
    params.set('page', String(page));
    return `/notifications?${params.toString()}`;
  };

  return (
    <AppLayout title="Notifications">
      <PageHeader
        title="Notifications"
        subtitle="Advanced notification center with filtering, search, and lifecycle actions"
        breadcrumbs={[{ label: "Notifications", href: "/notifications" }]}
        actions={
          <div className="flex items-center gap-2 flex-wrap">
            <Button onClick={markAllRead} disabled={busy === 'read-all' || unreadCount === 0} variant="outline" size="sm">
              {busy === 'read-all' ? <RefreshCw className="animate-spin" size={14} /> : <CheckCircle2 size={14} />} Mark all read
            </Button>
            <Button onClick={deleteRead} disabled={busy === 'delete-read'} variant="outline" size="sm">
              <Trash2 size={14} /> Delete read
            </Button>
            <a href="/notifications/export" className="inline-flex items-center gap-2 px-3 py-2 rounded-lg border border-slate-200 bg-white text-slate-700 font-medium text-sm hover:bg-slate-50 transition-colors">Export CSV</a>
            <Link href="/notifications/test" className="inline-flex items-center gap-2 px-3 py-2 rounded-lg bg-factory-50 text-factory-700 font-medium text-sm hover:bg-factory-100 transition-colors">
              <Bell size={14} /> Send Test
            </Link>
            <Button onClick={toggleSound} variant="ghost" size="sm" className={cn("text-slate-500 hover:text-factory-600", soundEnabled ? "" : "opacity-50")}>
              {soundEnabled ? <Volume2 size={16} /> : <VolumeX size={16} />}
            </Button>
          </div>
        }
      />

      <div className="mb-6 grid grid-cols-1 md:grid-cols-4 gap-4">
        <div className="rounded-xl border border-slate-200 bg-white p-5 shadow-sm"><p className="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Total</p><p className="mt-2 text-2xl font-semibold text-slate-900 tabular-nums">{summary.total ?? 0}</p></div>
        <div className="rounded-xl border border-slate-200 bg-white p-5 shadow-sm"><p className="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Unread</p><p className={cn("mt-2 text-2xl font-semibold tabular-nums", unreadCount > 0 ? "text-rose-600" : "text-emerald-600")}>{summary.unread ?? unreadCount}</p></div>
        <div className="rounded-xl border border-slate-200 bg-white p-5 shadow-sm"><p className="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Read</p><p className="mt-2 text-2xl font-semibold text-slate-900 tabular-nums">{summary.read ?? 0}</p></div>
        <div className="rounded-xl border border-slate-200 bg-white p-5 shadow-sm"><p className="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Types</p><p className="mt-2 text-sm font-medium text-slate-700">{Object.keys(summary.by_type || {}).length ? Object.entries(summary.by_type).map(([k,v]: any) => `${k}: ${v}`).join(' · ') : '—'}</p></div>
      </div>

      <div className="mb-6 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
        <div className="flex flex-col gap-3 lg:flex-row lg:items-end">
          <div className="flex-1">
            <label className="mb-1 block text-xs font-semibold uppercase tracking-wider text-slate-500">Search</label>
            <div className="relative">
              <Search size={15} className="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" />
              <input value={q} onChange={(e) => setQ(e.target.value)} onKeyDown={(e) => e.key === 'Enter' && applyFilters({ q })} placeholder="Search title, message, actor…" className="h-10 w-full rounded-lg border border-slate-200 bg-white pl-9 pr-3 text-sm focus:border-factory-500 focus:outline-none focus:ring-1 focus:ring-factory-500" />
            </div>
          </div>
          <div className="w-full lg:w-44">
            <label className="mb-1 block text-xs font-semibold uppercase tracking-wider text-slate-500">Status</label>
            <select value={status} onChange={(e) => { setStatus(e.target.value); applyFilters({ status: e.target.value }); }} className="h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm focus:border-factory-500 focus:outline-none focus:ring-1 focus:ring-factory-500">
              <option value="all">All</option><option value="unread">Unread</option><option value="read">Read</option>
            </select>
          </div>
          <div className="w-full lg:w-44">
            <label className="mb-1 block text-xs font-semibold uppercase tracking-wider text-slate-500">Type</label>
            <select value={type} onChange={(e) => { setType(e.target.value); applyFilters({ type: e.target.value }); }} className="h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm focus:border-factory-500 focus:outline-none focus:ring-1 focus:ring-factory-500">
              <option value="all">All types</option><option value="info">Info</option><option value="warning">Warning</option><option value="error">Error</option><option value="success">Success</option>
            </select>
          </div>
          <div className="flex gap-2">
            <Button variant="outline" onClick={() => applyFilters()}><Filter size={14} /> Apply</Button>
            <Button variant="ghost" onClick={() => { setQ(''); setStatus('all'); setType('all'); router.get('/notifications', {}, { preserveState: true, replace: true }); }}>Reset</Button>
          </div>
        </div>
      </div>

      <div className="rounded-xl border border-slate-200 bg-white shadow-sm overflow-hidden">
        <audio ref={audioRef} preload="auto"><source src="/sounds/notification.mp3" type="audio/mpeg" /></audio>
        {notifications.length === 0 ? (
          <div className="px-6 py-12 text-center">
            <div className="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 mb-3"><Bell size={20} className="text-slate-400" /></div>
            <h3 className="text-base font-semibold text-slate-900">No notifications</h3>
            <p className="mt-1 text-sm text-slate-500">No results match the current filters.</p>
          </div>
        ) : (
          <div className="divide-y divide-slate-100">
            {notifications.map((notification) => (
              <div key={notification.id} onClick={() => markRead(notification.id, notification.is_read)} className={cn("px-5 py-4 flex items-start gap-3 cursor-pointer hover:bg-slate-50 transition-colors", !notification.is_read ? "bg-factory-50/30 border-l-2 border-factory-500" : "")}>
                <div className={`mt-0.5 shrink-0 rounded-full p-1.5 ${TYPE_CONFIG[notification.type]?.color ?? TYPE_CONFIG.info.color}`}>
                  {TYPE_CONFIG[notification.type]?.icon ?? TYPE_CONFIG.info.icon}
                </div>
                <div className="min-w-0 flex-1">
                  <div className="flex items-start gap-2">
                    <p className={cn("text-sm font-semibold", !notification.is_read ? "text-slate-900" : "text-slate-500")}>{notification.title}</p>
                    {!notification.is_read && <span className="mt-1.5 h-1.5 w-1.5 rounded-full bg-factory-500" />}
                    <button onClick={(e) => deleteOne(e, notification.id)} className="ml-auto rounded-md p-1 text-slate-400 hover:bg-rose-50 hover:text-rose-600" title="Delete notification"><Trash2 size={14} /></button>
                  </div>
                  <p className="mt-0.5 text-sm text-slate-600">{notification.body}</p>
                  <div className="mt-2 flex flex-wrap items-center gap-2 text-[11px] font-medium uppercase tracking-wider text-slate-400">
                    <span>{notification.created_at}</span>
                    <span>• {notification.actor_name || 'System'}</span>
                    <span className="rounded bg-slate-100 px-1.5 py-0.5 text-slate-600">{notification.type || 'info'}</span>
                    <span className="ml-auto">{notification.is_read ? "Read" : "Unread"}</span>
                  </div>
                </div>
              </div>
            ))}
          </div>
        )}
      </div>

      {meta && meta.total > meta.per_page && (
        <div className="mt-6 flex flex-col gap-3 border-t border-slate-200/60 px-1 py-3 text-sm text-slate-500 sm:flex-row sm:items-center sm:justify-between">
          <p>Showing <span className="font-medium text-slate-700">{meta.from ?? 0}</span>–<span className="font-medium text-slate-700">{meta.to ?? 0}</span> of <span className="font-medium text-slate-700">{meta.total}</span></p>
          <div className="flex items-center gap-2">
            <Link href={pageHref(Math.max(1, (meta.current_page || 1) - 1))} className={cn("rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-medium", meta.current_page <= 1 && "pointer-events-none opacity-50")}>Previous</Link>
            <span className="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-medium text-slate-600">Page {meta.current_page} of {meta.last_page}</span>
            <Link href={pageHref(Math.min(meta.last_page || 1, (meta.current_page || 1) + 1))} className={cn("rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-medium", meta.current_page >= meta.last_page && "pointer-events-none opacity-50")}>Next</Link>
          </div>
        </div>
      )}
    </AppLayout>
  );
}
