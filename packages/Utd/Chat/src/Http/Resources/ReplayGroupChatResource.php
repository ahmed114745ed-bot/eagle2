<?php

namespace Utd\Chat\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

class ReplayGroupChatResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|JsonSerializable
     */
    public function toArray($request)
    {
        $framePack = $this->user?->packs
            ->firstWhere(fn ($p) => $p->type === 4 && $p->target_id === $this->user->dress_1);

        $frame = $framePack?->ware?->img2 ?? $framePack?->ware?->img1 ?? '';

        $data = [
            'id' => (int) (@$this->user?->id ?? 0),
            'uuid' => @$this->user?->uuid ?? '',
            'name' => @$this->user?->name ?? '',
            'profile' => [
                'image' => @$this->user->profile->avatar ?? '',
            ],
            'frame' => $frame,
            'frame_id' => $frame !== '' ? (@$this->user->dress_1 ?? 0) : 0,
            'vip' => [
                'level' => @$this->user?->UserVip?->level ?? 0,
            ],
            'level' => [
                'receiver_img' => @$this->user?->receiverLevel?->img ?? '',
                'sender_img' => @$this->user?->senderLevel?->img ?? '',
            ],
            'has_color_name' => $this->user->hasPackOfType(18),
            'message_id' => @$this->id,
            'group_message' => $this->text,
            'group_image' => @$this->image ?? '',
            'created_at' => Carbon::parse($this->created_at)->toDateTimeString(),
        ];

        return $data;
    }
}
