<?php

namespace App\Http\Resources\Api\V1;

use App\Http\Resources\Api\V1\NewUserRankingResource;
use Illuminate\Http\Resources\Json\ResourceCollection;


class UserRankingCollection extends ResourceCollection
{
    public $collects = NewUserRankingResource::class;

    protected $user;
    protected $key;
    protected $userExp;

    public function __construct($resource, $user, $key, $userExp = null)
    {
        parent::__construct($resource);
        $this->user = $user;
        $this->key = $key;
        $this->userExp = $userExp;
    }

    public function toArray($request)
    {
        $data = $this->collection->values();

        // top 3
        $top = $data->take(3);

        // others paginated
        $other = $data->slice(3);

        return [
            'user'  => [
                'user_id'         => $this->user->id,
                'uuid'            => $this->user->uuid ?? '',
                'exp'             => ($this->userExp != null) ? (@$this->userExp->total_gifts ?? '0') : ($data->where($this->key, $this->user->id)->first()->total_gifts ?? '0'),
                'vip_level'       => $this->user->UserVip?->level ?? 0,
                'sender_level'    => $this->user->total_sender_level ?? 0,
                'reciver_level'   => $this->user->total_received_level ?? 0,
                'vip_level_img'   => \App\Helpers\UserPackHelper::getVipIcon($this->user),
                'sender_level_img' => \App\Helpers\UserLevelHelper::getSenderImage($this->user),
                'reciver_level_img' => \App\Helpers\UserLevelHelper::getReceiverImage($this->user),
                'type_user'       => intval(@$this->user->type_user) ?: 0,
                'country'         => $this->user->country,
                'manger_type'     => !$this->user->mangerType ? null : new MangerTypeResource(@$this->user->mangerType),
                'age'             => $this->user->profile?->age ?? '',
                'color_name'      => \App\Helpers\UserPackHelper::getColorName($this->user),
                'achievement_images' => [],
            ],
            'top'   => NewUserRankingResource::collection($top),
            'other'  => NewUserRankingResource::collection($other),

        ];
    }
}
