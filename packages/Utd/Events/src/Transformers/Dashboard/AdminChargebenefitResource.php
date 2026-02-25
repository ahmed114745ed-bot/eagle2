<?php

namespace Utd\Events\Transformers\Dashboard;

use App\Models\Ware;
use App\Support\PackageHelper;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Utd\Vip\Entities\OVip;

class AdminChargebenefitResource extends JsonResource
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
                return [
                    'name' => $item->name,
                    'id' => $item->id,
                ];
            }

            return '';

        }
        if ($type === 'vip') {
            $item = PackageHelper::isInstalled('vip') ? OVip::find($id) : null;
            if ($item) {
                return [
                    'name' => $item->name,
                    'id' => $item->id,
                ];
            }

            return '';

        }

        return null;

    }

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'event_type' => null,
            'event_id' => $this->charge_event_id,
            'type' => $this->type,
            'level' => $this->level,
            'expire' => $this->expire,
            'target' => $this->target,
            'vip' => $this->get_name($this->target, $this->type),
            'ware' => $this->get_name($this->target, $this->type),
        ];
    }
}
