/**
 * Brixel FactoryOS — Notification Test Page
 * Send test notifications to verify real-time Reverb WebSocket integration.
 */

import { Head, Link, router, useForm } from "@inertiajs/react";
import { AppLayout } from "@/components/layout";
import { PageHeader } from "@/components/layout";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { Bell, Users, ArrowLeft } from "lucide-react";
import { cn } from "@/lib/utils";

interface User {
  id: number;
  name: string;
  email: string;
}

export default function NotificationTest() {
  const { data: formData, setData, errors, isPending, recentErrors } =
    useForm<{ title: string; body: string; type: string; user_ids: string[]; data_json: string }>({
      title: "",
      body: "",
      type: "info",
      user_ids: [],
      data_json: "",
    });

  const [users, setUsers] = React.useState<User[]>([]);
  const [sent, setSent] = React.useState<{ count: number; notifications: any[] } | null>(null);

  const NOTIFICATION_TYPES = [
    { value: "info", label: "Info", color: "text-blue-600 bg-blue-50" },
    { value: "warning", label: "Warning", color: "text-amber-600 bg-amber-50" },
    { value: "error", label: "Error", color: "text-rose-600 bg-rose-50" },
    { value: "success", label: "Success", color: "text-emerald-600 bg-emerald-50" },
  ];

  React.useEffect(() => {
    // Fetch users for recipient selection
    router.get("/rbac/users", {}, {
      preserveScroll: true,
      onSuccess: (page: any) => {
        // The users page returns paginated data, we need to extract users
        // For now, we'll fetch from a simple endpoint
      },
    });
  }, []);

  const submit = (event: React.FormEvent) => {
    event.preventDefault();
    router.post("/notifications", formData, {
      onSuccess: (page: any) => {
        setSent(page?.sent ? { count: page.sent, notifications: page.notifications || [] } : null);
        setData({ title: "", body: "", type: "info", user_ids: [], data_json: "" });
      },
      onError: () => {},
    });
  };

  return (
    <AppLayout title="Notification Test">
      <PageHeader
        title="Notification Test"
        subtitle="Send test notifications to verify real-time Reverb WebSocket integration"
        breadcrumbs={[{ label: "Notifications", href: "/notifications" }, { label: "Test" }]}
        actions={
          <Link href="/notifications" className="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-slate-200 bg-white px-3 text-sm font-medium text-slate-700 shadow-sm transition-colors hover:bg-slate-50">
            <ArrowLeft size={14} /> Back to Notifications
          </Link>
        }
      />

      <div className="max-w-3xl">
        {/* Status Info */}
        <Card className="mb-6">
          <CardHeader>
            <CardTitle className="text-base font-semibold text-slate-900">Real-Time Notification Test</CardTitle>
          </CardHeader>
          <CardContent className="p-5 pt-0">
            <div className="flex items-start gap-3">
              <Bell size={20} className="mt-0.5 text-factory-600" />
              <div>
                <p className="text-sm font-medium text-slate-900">How it works</p>
                <p className="text-sm text-slate-500">
                  This page sends a notification that is broadcast via <strong>Laravel Reverb</strong> WebSockets.
                  Open another browser tab or have a colleague open the app on their device — the notification
                  bell will ring instantly with sound when a new notification arrives.
                </p>
              </div>
            </div>
            <div className="mt-4 p-3 rounded-lg bg-slate-50 border border-slate-100">
              <p className="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Setup required:</p>
              <ul className="text-sm text-slate-600 space-y-1 list-disc list-inside">
                <li>Run <code className="text-factory-600 font-mono">composer require laravel/reverb</code></li>
                <li>Run <code className="text-factory-600 font-mono">php artisan install:broadcasting</code></li>
                <li>Run <code className="text-factory-600 font-mono">php artisan reverb:start</code></li>
                <li>Set <code className="text-factory-600 font-mono">BROADCAST_DRIVER=reverb</code> in <code className="text-factory-600 font-mono">.env</code></li>
              </ul>
            </div>
          </CardContent>
        </Card>

        {/* Send Notification Form */}
        <Card>
          <CardHeader>
            <CardTitle className="text-base font-semibold text-slate-900">Send Test Notification</CardTitle>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Title</label>
                <Input
                  value={formData.title}
                  onChange={(e) => setData("title", e.currentTarget.value)}
                  placeholder="e.g., Production order completed"
                  className={cn("w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-factory-500 focus:outline-none focus:ring-1 focus:ring-factory-500", errors.title ? "border-rose-500" : "")}
                />
                {errors.title && <p className="text-xs text-rose-600">{errors.title}</p>}
              </div>
              <div className="space-y-1">
                <label className="text-sm font-medium text-slate-700">Type</label>
                <Select onValueChange={(value) => setData("type", value)}>
                  <SelectTrigger className="w-full">
                    <SelectValue placeholder={formData.type} />
                  </SelectTrigger>
                  <SelectContent>
                    {NOTIFICATION_TYPES.map((t) => (
                      <SelectItem key={t.value} value={t.value}>{t.label}</SelectItem>
                    ))}
                  </SelectContent>
                </Select>
                {errors.type && <p className="text-xs text-rose-600">{errors.type}</p>}
              </div>
            </div>

            <div className="space-y-1">
              <label className="text-sm font-medium text-slate-700">Message</label>
              <Input
                value={formData.body}
                onChange={(e) => setData("body", e.currentTarget.value)}
                placeholder="e.g., Your production order WO-2024-0001 has been completed successfully."
                className={cn("w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-factory-500 focus:outline-none focus:ring-1 focus:ring-factory-500", errors.body ? "border-rose-500" : "")}
              />
              {errors.body && <p className="text-xs text-rose-600">{errors.body}</p>}
            </div>

            <div className="space-y-1">
              <label className="text-sm font-medium text-slate-700">Recipients</label>
              <p className="text-xs text-slate-500 mb-1">Leave empty to notify all active users.</p>
              <Select
                onValueChange={(value) => setData("user_ids", Array.isArray(value) ? value : [value])}
                multiple
              >
                <SelectTrigger className="w-full">
                  <SelectValue placeholder="Select users (optional)" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="">All active users</SelectItem>
                  {/* Users will be populated dynamically */}
                </SelectContent>
              </Select>
              {errors.user_ids && <p className="text-xs text-rose-600">{errors.user_ids}</p>}
            </div>

            <div className="space-y-1">
              <label className="text-sm font-medium text-slate-700">Extra Data (JSON)</label>
              <Input
                value={formData.data_json}
                onChange={(e) => setData("data_json", e.currentTarget.value)}
                placeholder='{"order_id": 123, "url": "/production"}'
                className={cn("w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-factory-500 focus:outline-none focus:ring-1 focus:ring-factory-500", errors.data_json ? "border-rose-500" : "")}
              />
              <p className="text-xs text-slate-400 mt-1">Optional JSON data attached to the notification.</p>
              {errors.data_json && <p className="text-xs text-rose-600">{errors.data_json}</p>}
            </div>
          </CardContent>
        </Card>

        <div className="flex justify-end pt-4">
          <Button
            type="button"
            onClick={(e) => {
              e.preventDefault();
              // Parse JSON data
              let data = {};
              if (formData.data_json) {
                try {
                  data = JSON.parse(formData.data_json);
                } catch {
                  // ignore parse errors, will send empty
                }
              }
              router.post("/notifications", {
                ...formData,
                data,
              }, {
                onSuccess: (page: any) => {
                  setSent(page?.sent ? { count: page.sent, notifications: page.notifications || [] } : null);
                  setData({ title: "", body: "", type: "info", user_ids: [], data_json: "" });
                },
              });
            }}
            disabled={isPending}
          >
            {isPending ? "Sending…" : <><Bell size={16} /> Send Notification</>}
          </Button>
        </div>

        {/* Sent Confirmation */}
        {sent && (
          <Card className="mt-6 border-emerald-200 bg-emerald-50">
            <CardContent className="p-4">
              <div className="flex items-start gap-3">
                <div className="mt-0.5 shrink-0 bg-emerald-100 text-emerald-600 flex items-center justify-center rounded-full p-1.5">
                  <Bell size={16} />
                </div>
                <div className="min-w-0 flex-1">
                  <h4 className="text-sm font-semibold text-emerald-800">Notification Sent!</h4>
                  <p className="text-xs text-emerald-700 mt-1">
                    Successfully broadcast to <strong>{sent.count}</strong> user(s) via Reverb WebSocket.
                  </p>
                </div>
              </div>
              <div className="mt-3 pt-3 border-t border-emerald-100/50">
                <p className="text-xs font-semibold uppercase tracking-wider text-emerald-600 mb-1">Sent Notifications:</p>
                {sent.notifications.map((n: any) => (
                  <div key={n.id} className="flex items-start gap-2 text-sm text-emerald-700">
                    <span className="mt-0.5 shrink-0 w-1.5 h-1.5 rounded-full bg-emerald-500" />
                    <div>
                      <p className="font-medium">{n.title}</p>
                      <p className="text-[10px] text-emerald-500">{n.created_at}</p>
                    </div>
                  </div>
                ))}
              </div>
            </CardContent>
          </Card>
        )}

        {recentErrors && (
          <div className="mt-6 rounded-lg bg-rose-50 p-4 border border-rose-100">
            <p className="text-sm text-rose-700">{recentErrors}</p>
          </div>
        )}
      </div>
    </AppLayout>
  );
}
