<?php

namespace App\Admin\Services;

class UserService
{
    public function adminUserAvatar($user): string
    {
        $uid = $user->original_uuid;

        $special = $user->uuid_v3;

        $path = @$user->profile?->avatar;
        $defaultImage = asset('images/businessman-icon.jpg');
        $url = getImagePath($path) ?? $defaultImage;

        //                $senderLevel = @$this->total_sender_level;
        //                $receivedLevel = @$this->total_received_level;

        $receiver_img = @$user->getImageReceiverOrSender('receiver_id', 1)?->img ?? '';
        $receiverImg = getImagePath($receiver_img) ?? $defaultImage;

        $sender_img = @$user->getImageReceiverOrSender('sender_id', 2)?->img ?? '';
        $senderImg = getImagePath($sender_img) ?? $defaultImage;

        $charger_img = @$user->getTotalChargeLevel($user->total_charge_level)?->img ?? '';
        $chargerImg = getImagePath($charger_img) ?? '';

        // Check if the image exists
        if (! isImageExists($url)) {
            $url = $defaultImage;
        }
        $image = handleShowImageWithTypes($user->id, $url, 50, 50);

        return "
                        <div style='display: flex; align-items: center; gap: 10px;'>
                            $image
                            <div>
                                <strong>$user->name</strong><br>
                                <span style='font-size: smaller;'>UID: $uid</span><br>
                                <span style='font-size: smaller;'>special: $special</span><br>
                                ".(! empty($receiverImg) ? "<img src='$receiverImg' style='width: 32px; height: 14px; '>" : '').'
                                '.(! empty($senderImg) ? "<img src='$senderImg' style='width: 32px; height: 14px; '>" : '').'
                                '.(! empty($chargerImg) ? "<img src='$chargerImg' style='width: 32px; height: 14px; '>" : '').'
                            </div>
                        </div>
                        ';
    }
}
