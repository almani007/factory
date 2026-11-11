<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class NotificationSent implements ShouldBroadcast
{
    use InteractsWithSockets, SerializesModels;

    public $notification;

    public function __construct($notification)
    {
        $this->notification = $notification;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('tenant.' . $this->notification['tenant_id']),
        ];
    }

    /**
     * The event's broadcast name.
     *
     * @return string
     */
    public function broadcastAs(): string
    {
        return 'NotificationSent';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->notification['id'],
            'title' => $this->notification['title'],
            'body' => $this->notification['body'],
            'type' => $this->notification['type'],
            'created_at' => $this->notification['created_at'],
            'actor_name' => $this->notification['actor_name'] ?? 'System',
            'data' => $this->notification['data'] ?? [],
        ];
    }

    /**
     * Determine if the event should broadcast.
     *
     * @return bool
     */
    public function broadcastWhen(): bool
    {
        return true;
    }
}
