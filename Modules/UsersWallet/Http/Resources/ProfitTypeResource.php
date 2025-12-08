<?php

namespace Modules\UsersWallet\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;


class ProfitTypeResource extends JsonResource
{

     public function toArray($request)
    {
        $target = $this->target;
        $targetName = $target?->diamonds ?? '---';
        $broadcastTime = $this->hours ?? $this->days ?? 0;
        $totalDiamonds = $target?->diamonds ?? 0;

          \Log::info('Target object:', ['target' => $target]);
    \Log::info('Type:', ['type' => $this->type]);
    \Log::info('Diamond:', ['diamond' => $this->diamond]);
        $roleKey = match($this->type) {
            'bd' => 'role_bd',
            'agency_manager' => 'role_agency_manager',
            'user' => 'role_user',
            'app' => 'role_app',
            default => $this->type,
        };

        $role = __($roleKey);

        switch ($this->type) {
            case 'user':
                $profitPercent = $target?->usd;
                break;
            case 'bd':
                $profitPercent = $target?->db_percentage ?? 0;
                break;
            case 'agency_manager':
                $profitPercent = $target?->agency_share ?? 0;
                break;
            default:
                $profitPercent = 0;
        }

        $message = __('diamonds_message', [
            'diamonds' => $totalDiamonds,
            'role'     => $role,
            'profit'   => $profitPercent,
            'time'     => $broadcastTime,
        ]);

        return [
            'id'      => $this->id,
            'user_id' => $this->user_id,
            'type'    => $this->type,
            'message' => $message,
            'title'   => $targetName,
            'date'    => $this->created_at?->format('Y-m-d H:i:s'),
        ];
    }
}
