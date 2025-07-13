<?php
namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use App\Helpers\Common; 
use Illuminate\Support\Collection;

class SendFirebaseTopicNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public array|Collection $tokens,
        public string $title,
        public string $body,
        public string $icon = '',
        public array $data = [],
        public ?string $messageType = null,
        public $user = null,
        public string $action = '',
        public string $type = '',
        public string $id = '',
        public string $notification_type = 'user_notification',
    ) {}

    public function handle(): void
    {
        $tokens = $this->tokens instanceof Collection ? $this->tokens->toArray() : $this->tokens;

        if (count($tokens) <= 1) {
            return; 
        }

        Common::send_firebase_notification_top(
            tokens: $tokens,
            title: $this->title,
            body: $this->body,
            icon: $this->icon,
            data: $this->data,
            messageType: $this->messageType,
            user: $this->user,
            action: $this->action,
            type: $this->type,
            id: $this->id,
            notification_type: $this->notification_type
        );
    }
}
