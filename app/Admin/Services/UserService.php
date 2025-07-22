<?php

namespace App\Admin\Services;

use Modules\Vip\Entities\Vip;

// use App\Models\Vip;

class UserService
{
    public function adminUserAvatar($user, bool $withoutLevels = false): string
    {
        if (! $user) return __('No user');

        $uid = $user->original_uuid;
        $special = $user->uuid_v3;

        $defaultImage = asset('images/businessman-icon.jpg');
        $path = $user->profile->avatar ?? null;

        $url = getImagePath($path) ?? $defaultImage;

        // Avoid calling external resources unless necessary
        if (! isImageExists($url)) {
            $url = $defaultImage;
        }

        $image = handleShowImageWithTypes($user->id, $url, 50, 50);

        // Level-related data
        $levelImages = '';

        if (! $withoutLevels) {
            // Cache these expensive DB calls in one place
            $senderAmount = $user->sender_level + $user->sub_sender_level;
            $receiverAmount = $user->received_level + $user->sub_receiver_level;

            $vipLevels = Vip::collectionBuilder()
                ->whereIn('type', [1, 2])
                ->whereIn('level', [$senderAmount, $receiverAmount])
                ->orderByDesc('exp')
                ->get()
                ->keyBy(fn ($vip) => "{$vip->type}_{$vip->level}");

            $receiverLevel = $vipLevels["1_{$receiverAmount}"] ?? null;
            $senderLevel = $vipLevels["2_{$senderAmount}"] ?? null;

            $receiverImg = getImagePath($receiverLevel->img ?? null) ?? null;
            $senderImg = getImagePath($senderLevel->img ?? null) ?? null;

            // Charge level
            $chargeLevel = Vip::collectionBuilder()
                ->where('type', 5)
                ->where('level', $user->total_charge_level)
                ->orderByDesc('exp')
                ->first();

            $chargerImg = getImagePath($chargeLevel->img ?? null) ?? null;

            // HTML rendering for levels
            foreach ([$receiverImg, $senderImg, $chargerImg] as $img) {
                if (!empty($img)) {
                    $levelImages .= "<img src='$img' style='width: 32px; height: 14px;'> ";
                }
            }
        }

        return "
        <div style='display: flex; align-items: center; gap: 10px;'>
            $image
            <div>
                <strong>{$user->name}</strong><br>
                <span style='font-size: smaller;'>UID: $uid</span><br>
                <span style='font-size: smaller;'>special: $special</span><br>
                $levelImages
            </div>
        </div>
    ";
    }


}
