<?php

namespace Modules\Moment\Transformers;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class MomentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id'               => $this->id ?? 0,
            'user_id'          => $this->user_id ?? 0,
            'description'      => $this->description ?? '',
            'comment_num'      => $this->comments_count ?? 0, // Assuming you have a relationship for comments
            'like_num'         => $this->likes_count ?? 0, // Assuming you have a relationship for likes
            'gifts_count'      => (int)$this->gifts?->sum('gifts_count') ?? 0,
            'created_at'       => Carbon::parse($this->created_at)->setTimezone($request->hasHeader('tz') ? $request->header()['tz'][0] : 'UTC')->format('Y-m-d H:i:s') ?? '',
            'updated_at'       => $this->updated_at ?? '',
            'img'              => $this->img ?? '',
            'is_like'          =>  @$this->likes_exists ?? false,
            'likes_count'          => @$this->likes_count ?? 0,
            'user'             => @$this->whenLoaded('user') ?  new UserResource($this->whenLoaded('user')) : new \stdClass(),
            // 'user' => optional($this->user??0)->relationLoaded('user') ? new UserResource($this->user) : [],

        ];
    }
}
