<?php

namespace Modules\SalaryTransaction\Transformers;

use App\Models\Agency;
use App\Models\Pack;
use App\Models\User;
use App\Models\Charge;
use App\Helpers\Common;
use Illuminate\Http\Resources\Json\JsonResource;

class ChargeAgentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        //        if (!$this instanceof Agency) return [];
        $user = $this->owner;

        $frame = Common::getUserDress(@$user?->id, @$user?->dress_1, 4, 'img2', true) ?: Common::getUserDress(@$user?->id, @$user?->dress_1, 4, 'img1', true);
        return [
            'agency_id' => $this->id,
            'id' => $user->id ?? 0,
            'name' => $this->name ?? '',
            'phone' => $user->phone ?? '',
            'image' => $this->img ?? '',
            'uuid' => $user->uuid ?? '',
            'owner_name' => @$user->name ?? '',
            'owner_image' =>  @$user->profile?->avatar ?? '',
            'payment_getaway' => $this->AgencypaymentGateways ?? [],
            'countries' => $this->Countries ?? [],
            'frame' => $frame ?? '',
            'frame_id' => $frame != '' ? @$user->dress_1 : 0, // both
            'level' => $user->total_sender_level ?? 0, // both
            'vip' => @$user->UserVip->level ?? 0, // both
            'charge_count' => $this->salary_requests_count ?? 0,
            'image_color'          => $user->color_image ?? null,
            'id_image'             => $user->specialId?->ware?->show_img ?? '',

            // 'charge_count' => $userDetails->charges_count ?? 0,
        ];
    }
}
