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
        $userDetails =  User::where('id', $user->id)
            ->whereHas('charges', function ($q) use ($user) {
                $q->whereRaw('charger_id != user_id')
                    ->where(function ($q) use ($user) {
                        $q->where('charger_id', $this->id)
                            ->orWhere('user_id', $user->id);
                    });
            })->withCount('charges')
            ->first();

        $frame = Common::getUserDress($user->id, $user->dress_1, 4, 'img2', true) ?: Common::getUserDress($user->id, $user->dress_1, 4, 'img1', true);
        return [
            'id' => $user->id,
            'name' => $user->name,
            'phone' => $user->phone,
            'image' => @$user->profile->avatar ?: '',
            'uuid' => $user->uuid,
            'payment_getaway' => $this->AgencypaymentGateways ?? [],
            'countries' => $this->Countries ?? [],
            'frame' => $frame,
            'frame_id' => $frame != '' ? @$user->dress_1 : 0, // both
            'level' => $user->total_sender_level, // both
            'vip' => @$user->UserVip->level, // both
            'charge_count' => $this->salary_requests_count ?? 0,
            // 'charge_count' => $userDetails->charges_count ?? 0,
        ];
    }
}
