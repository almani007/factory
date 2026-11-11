<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NotificationController extends Controller
{
    public function store(Request $request)
    {
        $tenantId = $request->user()->tenant_id ?? 0;
        $userId = $request->user()->id ?? null;

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'type' => 'required|string|in:info,warning,error,success',
            'user_ids' => 'nullable|array',
            'user_ids.*' => 'exists:users,id',
            'data' => 'nullable|array',
        ]);

        $targetUserIds = $validated['user_ids'] ?? [];
        if (empty($targetUserIds)) {
            $targetUserIds = DB::table('users')
                ->where('tenant_id', $tenantId)
                ->where('is_active', true)
                ->pluck('id')
                ->toArray();
        }

        $notifications = [];
        foreach ($targetUserIds as $targetUserId) {
            $notificationId = DB::table('notifications')->insertGetId([
                'tenant_id' => $tenantId,
                'user_id' => $targetUserId,
                'title' => $validated['title'],
                'body' => $validated['body'],
                'type' => $validated['type'],
                'data' => $validated['data'] ? json_encode($validated['data']) : null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $notifications[] = [
                'id' => $notificationId,
                'title' => $validated['title'],
                'body' => $validated['body'],
                'type' => $validated['type'],
                'tenant_id' => $tenantId,
                'actor_name' => $request->user()->name ?? 'System',
                'data' => $validated['data'] ?? [],
                'created_at' => now()->format('Y-m-d H:i:s'),
            ];
        }

        foreach ($notifications as $notification) {
            broadcast(new \App\Events\NotificationSent($notification))->toOthers();
        }

        return response()->json([
            'success' => true,
            'message' => 'Notification sent to ' . count($notifications) . ' user(s).',
            'notifications' => $notifications,
        ]);
    }
}
