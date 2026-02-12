<?php

namespace Utd\Pk\Http\Resources;

use App\Models\Ware;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Vip\Entities\OVip;

class AdminPKEventRewardsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function get_name($id, $type)
    {
        if ($type === 'ware') {
            $item = Ware::find($id);
            if ($item) {
                return $item->name;
            }

            return '';

        } elseif ($type === 'vip') {
            $item = OVip::find($id);
            if ($item) {
                return $item->name;
            }

            return '';

        }
    }

    public function toArray(Request $request): array
    {
        $img_check = 0;
        $coins_check = 0;
        if ($this->type === 'coins') {
            $coins_check = 0;
            $name = $this->target;
        } elseif ($this->type === 'achievement') {
            $coins_check = 0;
            $name = $this->target;
        } else {
            $name = $this->get_name($this->target, $this->type);
        }

        return [
            'id' => $this->id,
            'expire' => $this->expire,
            'type' => $this->type,
            'level' => $this->level,
            'img_check' => $img_check,
            'coins_check' => $coins_check,
            'name' => $name,
        ];
    }
}
