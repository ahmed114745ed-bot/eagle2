<?php
namespace App\Jobs;

use App\Helpers\Common;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SendFirebaseNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public array $tokens,
        public string $title,
        public string $body,
        public array $data = [],
        public ?string $messageType = null,
        public $user = null,
        public string $action = '',
        public string $type = '',
        public string $id = '',
        public string $notification_type = 'user_notification',
    ) {}

    public function handle()
    {
        \Log::info('Job started for token: ' . $this->tokens);

        $api_access_key = Common::getPublicGoogleAccessToken();
        $projectId = env('FIREBASE_PROJECT_NAME');
    
        foreach ($this->tokens as $token) {
            $notification = [
                'title' => $this->title,
                'body'  => $this->body,
            ];
    
            $userData = [];
            if ($this->user) {
                $userData = [
                    'user_id' => $this->user->id,
                    'name'    => $this->user->name,
                    'uuid'    => $this->user->uuid,
                    'has_color_name' => Common::hasInPack($this->user->id, 18, true),
                    'image'   => $this->user->profile->avatar,
                ];
            }
    
            $payload = [
                'token' => $token,
                'notification' => $notification,
                'data' => [
                    'click_action'    => 'FLUTTER_NOTIFICATION_CLICK',
                    'message-type'    => json_encode($this->messageType ?? ''),
                    'data'            => json_encode($this->data),
                    'action'          => $this->action,
                    'type'            => $this->type,
                    'id'              => $this->id,
                    'notification_type' => $this->notification_type,
                ],
            ];
    
            if (!empty($userData)) {
                $payload['data']['user'] = json_encode($userData);
            }
    
            if (isset($this->data['image'])) {
                $payload['notification']['image'] = $this->data['image'];
            }
    
            $headers = [
                'Authorization' => 'Bearer ' . $api_access_key,
                'Content-Type'  => 'application/json',
            ];
    
            $response = Http::withHeaders($headers)->post(
                "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send",
                ['message' => $payload]
            );
    
            \Log::info('FCM Response: ', [
                'token' => $token,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
        }
    }
    
}
