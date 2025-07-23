<?php

namespace App\Admin\Services;

use App\Models\Vip;

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
        admin_toastr();
        return "
    <a href='" . url("admin/users/{$user->id}") . "' style='
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px;
        text-decoration: none;
        color: inherit;
        transition: background-color 0.2s ease;
    ' onmouseover=\"this.style.backgroundColor='#f0f0f0'\" >
        $image
        <div>
            <strong style='font-size: 16px;'>{$user->name}</strong><br>
            <span style='font-size: 13px;'>
                UID: <span id='uid-{$user->id}'>{$uid}</span>
                <button onclick=\"event.preventDefault(); event.stopPropagation(); copyToClipboard('uid-{$user->id}')\" style='
                    background: none;
                    border: none;
                    cursor: pointer;
                    margin-left: 5px;
                    font-size: 13px;
                    color: #007bff;
                ' title='Copy UID'>📝</button>
            </span><br>
            <span style='font-size: 13px;'>Special: {$special}</span><br>
            $levelImages
        </div>
    </a>

    <script>
        function copyToClipboard(elementId) {
            const text = document.getElementById(elementId)?.textContent;
            if (text) {
                navigator.clipboard.writeText(text).then(() => {
                        toastr.success('" . e(trans('Copied')) . "');
                });
            }
        }
    </script>
";
    }


}
