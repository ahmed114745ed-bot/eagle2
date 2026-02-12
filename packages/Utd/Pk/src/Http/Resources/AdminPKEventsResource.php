<?php

namespace Utd\Pk\Http\Resources;

use App\Models\Ware;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Vip\Entities\OVip;

class AdminPKEventsResource extends JsonResource
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
            $item = OVip::find($id);
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
            'event_type' => $this->pk_type,
            'event_id' => $this->pk_event_id,
            'type' => $this->type,
            'level' => $this->level,
            'expire' => $this->expire,
            'target' => $this->target,
            'vip' => $this->get_name($this->target, $this->type),
            'ware' => $this->get_name($this->target, $this->type),
        ];
    }
}
