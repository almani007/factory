/**
 * Brixel FactoryOS — Notification Bell
 * Real-time notification bell with sound alert and unread badge.
 * Listens for real-time notifications via Laravel Echo + Reverb.
 */

import * as React from "react";
import { Bell, X, ChevronRight, Play, Volume2, VolumeX } from "lucide-react";
import { Link, router, usePage } from "@inertiajs/react";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import { cn } from "@/lib/utils";
import { echo } from "@/echo";

interface Notification {
  id: number;
  title: string;
  body: string;
  type: string;
  created_at: string;
  is_read: boolean;
  data: Record<string, any>;
  actor_name?: string;
}

interface NotificationBellProps {
  unreadCount: number;
  notifications: Notification[];
}

const TYPE_CONFIG: Record<string, { icon: React.ReactNode; color: string; label: string }> = {
  info: { icon: <div className="w-5 h-5 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center"><svg className="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg></div>, color: "bg-blue-50 text-blue-700", label: "Info" },
  warning: { icon: <div className="w-5 h-5 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center"><svg className="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg></div>, color: "bg-amber-50 text-amber-700", label: "Warning" },
  error: { icon: <div className="w-5 h-5 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center"><svg className="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M6 18L18 6M6 6l12 12"></path></svg></div>, color: "bg-rose-50 text-rose-700", label: "Error" },
  success: { icon: <div className="w-5 h-5 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center"><svg className="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M5 13l4 4L19 7"></path></svg></div>, color: "bg-emerald-50 text-emerald-700", label: "Success" },
};

export default function NotificationBell({ unreadCount, notifications }: NotificationBellProps) {
  const [open, setOpen] = React.useState(false);
  const [soundEnabled, setSoundEnabled] = React.useState(true);
  const [isPlaying, setIsPlaying] = React.useState(false);
  const [liveUnreadCount, setLiveUnreadCount] = React.useState(unreadCount);
  const [liveNotifications, setLiveNotifications] = React.useState(notifications);
  const audioRef = React.useRef<HTMLAudioElement | null>(null);
  const { props } = usePage() as any;
  const tenantId = props?.tenant?.id ?? 1;

  // Subscribe to real-time notifications via Echo + Reverb
  React.useEffect(() => {
    const channel = echo.private(`tenant.${tenantId}`);

    channel.listen('.NotificationSent', (e: any) => {
      const newNotification: Notification = {
        id: e.id,
        title: e.title,
        body: e.body,
        type: e.type,
        created_at: e.created_at,
        is_read: false,
        data: e.data || {},
        actor_name: e.actor_name,
      };

      setLiveNotifications((prev) => [newNotification, ...prev]);
      setLiveUnreadCount((prev) => prev + 1);

      // Play sound for real-time notification
      if (soundEnabled && audioRef.current) {
        audioRef.current.currentTime = 0;
        audioRef.current.play().catch(() => {});
      }
    });

    return () => {
      channel.leave();
    };
  }, [tenantId, soundEnabled]);

  const markRead = (id: number) => {
    router.post(`/notifications/${id}/read`, {}, {
      preserveScroll: true,
      onSuccess: () => {
        setOpen(false);
        setLiveUnreadCount((prev) => Math.max(0, prev - 1));
        setLiveNotifications((prev) => prev.map((n) => n.id === id ? { ...n, is_read: true } : n));
      },
    });
  };

  const markAllRead = () => {
    router.post("/notifications/read-all", {}, {
      preserveScroll: true,
      onSuccess: () => {
        setOpen(false);
        setLiveUnreadCount(0);
        setLiveNotifications((prev) => prev.map((n) => ({ ...n, is_read: true })));
      },
    });
  };

  const toggleSound = () => {
    setSoundEnabled(!soundEnabled);
  };

  return (
    <div className="relative">
      <button
        type="button"
        onClick={() => setOpen(!open)}
        className="relative rounded-lg p-2 text-slate-600 hover:bg-slate-100 hover:text-slate-900 transition-colors"
        aria-label="Notifications"
      >
        <Bell size={19} />
        {liveUnreadCount > 0 && (
          <span className="absolute right-1 top-1 h-2.5 w-2.5 rounded-full bg-rose-500 ring-2 ring-white animate-pulse" />
        )}
      </button>

      {/* Sound toggle for notifications */}
      <button
        type="button"
        onClick={toggleSound}
        className="ml-1 rounded-lg p-1 text-slate-500 hover:bg-slate-100 hover:text-factory-600 transition-colors"
        title={soundEnabled ? "Sound enabled" : "Sound disabled"}
      >
        {soundEnabled ? <Volume2 size={14} /> : <VolumeX size={14} />}
      </button>

      {open && (
        <div className="absolute right-0 z-50 mt-2 w-80 rounded-xl border border-slate-200 bg-white shadow-lg ring-1 ring-black ring-opacity-5">
          <div className="flex items-center justify-between px-4 py-3 border-b border-slate-100">
            <h3 className="text-sm font-semibold text-slate-900">Notifications</h3>
            <div className="flex items-center gap-1">
              <span className="text-[10px] font-medium uppercase tracking-wider text-slate-400">Sound</span>
              <button
                type="button"
                onClick={toggleSound}
                className="rounded p-1 text-slate-500 hover:bg-slate-100"
              >
                {soundEnabled ? <Volume2 size={12} /> : <VolumeX size={12} />}
              </button>
            </div>
          </div>

          <div className="max-h-[400px] overflow-y-auto">
            {liveNotifications.length === 0 ? (
              <div className="px-4 py-6 text-center">
                <div className="mx-auto flex h-8 w-8 items-center justify-center rounded-full bg-slate-100">
                  <Bell size={16} className="text-slate-400" />
                </div>
                <p className="mt-2 text-sm text-slate-500">No notifications yet.</p>
              </div>
            ) : (
              <>
                {liveNotifications.map((notification) => (
                  <div
                    key={notification.id}
                    onClick={() => markRead(notification.id)}
                    className={cn(
                      "px-4 py-3 border-b border-slate-100 hover:bg-slate-50 cursor-pointer transition-colors",
                      !notification.is_read ? "bg-factory-50/30 border-l-2 border-factory-500" : ""
                    )}
                  >
                    <div className="flex items-start gap-3">
                      <div className={`mt-0.5 shrink-0 rounded-full p-1.5 ${TYPE_CONFIG[notification.type]?.color ?? TYPE_CONFIG.info.color}`}>
                        {TYPE_CONFIG[notification.type]?.icon ?? TYPE_CONFIG.info.icon}
                      </div>
                      <div className="min-w-0 flex-1">
                        <p className={cn("text-sm font-medium", !notification.is_read ? "text-slate-900" : "text-slate-500")}>
                          {notification.title}
                        </p>
                        <p className="mt-0.5 text-xs text-slate-500 truncate">
                          {notification.body}
                        </p>
                        <p className="mt-1 text-[10px] text-slate-400">
                          {notification.created_at}
                        </p>
                      </div>
                      {!notification.is_read && (
                        <div className="mt-0.5 h-1.5 w-1.5 rounded-full bg-factory-500" />
                      )}
                    </div>
                  </div>
                ))}
                <div className="px-4 py-2 border-t border-slate-100">
                  <button
                    type="button"
                    onClick={markAllRead}
                    className="w-full text-xs font-medium text-factory-600 hover:text-factory-700 py-2"
                  >
                    Mark all as read
                  </button>
                </div>
              </>
            )}
          </div>

          <div className="px-4 py-3 border-t border-slate-100">
            <Link
              href="/notifications"
              className="w-full text-sm font-medium text-factory-600 py-2 text-center hover:bg-factory-50 rounded-lg"
            >
              View all notifications <ChevronRight size={14} className="ml-1" />
            </Link>
          </div>
        </div>
      )}
    </div>
  );
}
