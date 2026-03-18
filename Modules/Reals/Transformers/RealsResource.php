<?php

namespace Modules\Reals\Transformers;

use Illuminate\Http\Request;
use Auth;
use Modules\Reals\Entities\RealUserLike;
use Illuminate\Http\Resources\Json\JsonResource;

class RealsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request
     * @return array
     */
    public function toArray($request)
    {
        $user = Auth::user();
        return [
            'id'             => $this->id,
            'user_id'        => $this->user_id,
            'description'    => $this->description,
            'url'            => $this->url,
            'sub_video'      => $this->sub_video,
            'sub_frame'      => (config('app.env') != 'production' ? '' : 'test-') ."frames/".$this->id.'.jpg',
            'created_at'     => $this->created_at,
            'updated_at'     => $this->updated_at,
            'likes_count'    => $this->likes_count,
            'comments_count' => $this->comments_count,
            'likes_exists'   => RealUserLike::where(['user_id' => $user->id, 'real_id' => $this->id])->exists() ?? false,
            'user'           => new UserResource($this->user),
            'share_count'    => $this->share_num,
        ];
    }
}
