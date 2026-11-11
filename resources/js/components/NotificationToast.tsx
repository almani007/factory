/**
 * Brixel FactoryOS — Notification Toast
 * Toast notification component with sound alert.
 */

import * as React from "react";
import { X, CheckCircle2, AlertTriangle, Info, Mail } from "lucide-react";
import { cn } from "@/lib/utils";

interface NotificationToastProps {
  notification: {
    id: number;
    title: string;
    body: string;
    type: "info" | "success" | "warning" | "error";
  };
  onDismiss: () => void;
  onClick?: () => void;
  soundEnabled: boolean;
}

const TYPE_CONFIG: Record<string, { icon: React.ReactNode; bg: string; text: string; border: string }> = {
  info: {
    icon: <Info size={18} className="text-blue-600" />,
    bg: "bg-blue-50",
    text: "text-blue-800",
    border: "border-blue-200",
  },
  success: {
    icon: <CheckCircle2 size={18} className="text-emerald-600" />,
    bg: "bg-emerald-50",
    text: "text-emerald-800",
    border: "border-emerald-200",
  },
  warning: {
    icon: <AlertTriangle size={18} className="text-amber-600" />,
    bg: "bg-amber-50",
    text: "text-amber-800",
    border: "border-amber-200",
  },
  error: {
    icon: <X size={18} className="text-rose-600" />,
    bg: "bg-rose-50",
    text: "text-rose-800",
    border: "border-rose-200",
  },
};

export default function NotificationToast({ notification, onDismiss, onClick, soundEnabled }: NotificationToastProps) {
  const audioRef = React.useRef<HTMLAudioElement | null>(null);

  React.useEffect(() => {
    if (soundEnabled && audioRef.current) {
      audioRef.current.currentTime = 0;
      audioRef.current.play().catch(() => {});
    }
  }, [soundEnabled]);

  const typeConfig = TYPE_CONFIG[notification.type] ?? TYPE_CONFIG.info;

  return (
    <div
      className={cn(
        "flex items-start gap-3 rounded-xl border p-4 shadow-lg animate-in slide-in-from-right-4 sm:slide-in-from-top-4 sm:slide-in-from-bottom-4 sm:duration-300",
        typeConfig.bg,
        typeConfig.border,
      )}
      onClick={onClick}
      role="alert"
    >
      <audio ref={audioRef} preload="auto">
        <source src="/sounds/notification.mp3" type="audio/mpeg" />
      </audio>
      <div className={`shrink-0 mt-0.5 ${typeConfig.text}`}>{typeConfig.icon}</div>
      <div className="min-w-0 flex-1">
        <h4 className="text-sm font-semibold text-slate-900">{notification.title}</h4>
        <p className="mt-0.5 text-xs text-slate-600">{notification.body}</p>
      </div>
      <button
        type="button"
        onClick={onDismiss}
        className="rounded-md p-1 text-slate-400 hover:bg-slate-200 hover:text-slate-600 transition-colors"
        aria-label="Dismiss notification"
      >
        <X size={14} />
      </button>
    </div>
  );
}
