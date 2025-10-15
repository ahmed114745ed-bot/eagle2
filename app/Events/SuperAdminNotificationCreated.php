<?php
namespace App\Events;

use App\Models\SuperAdminNotification;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SuperAdminNotificationCreated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $notification;

    /**
     * Create a new event instance.
     *
     * @param \App\Models\SuperAdminNotification $notification
     */
    public function __construct(SuperAdminNotification $notification)
    {
        $this->notification = $notification;
    }

    /**
     * The name of the channel on which the event is broadcast.
     *
     * @return Channel|array
     */
    public function broadcastOn()
    {
        return new Channel('superAdmin.notifications.' . $this->notification->super_admin_id);
    }
   
    public function broadcastAs()
    {
        return 'SuperAdminNotificationCreated';
    }
    /**
     *
     * @return array
     */
    public function broadcastWith()
    {
        \Log::info('📡 Broadcasting AdminNotificationCreated', [
            'id' => $this->notification->id,
            'title' => $this->notification->title,
            'channel' => 'superAdmin.notifications.' . $this->notification->super_admin_id,
        ]);

        return [
            'id'        => $this->notification->id,
            'title'     => $this->notification->title,
            'message'   => $this->notification->message,
            'data'      => $this->notification->data,
            'is_read'   => $this->notification->is_read,
            'super_admin_id'   => $this->notification->super_admin_id,
            'created_at'=> $this->notification->created_at->toDateTimeString(),
            
        ];
    }
}
