<?php

namespace App\Admin\Services;



use App\Helpers\LogHelper;
use App\Helpers\UserLevelHelper;
use App\Models\Admin;
use Encore\Admin\Facades\Admin as Super;
use Modules\Vip\Entities\Vip;

class UserService
{
    public function adminUserAvatar($user, bool $withoutLevels = false, $showUrl = null): string
    {
        if (! $user) return __('No user');

        $uid = e($user->original_uuid);
        $special = e($user->uuid);

        $defaultImage = asset('images/businessman-icon.jpg');
        $path = $user->profile?->avatar; // prevent null crash

        $url = getImagePath($path) ?? $defaultImage;

        if (! isImageExists($url)) {
            $url = $defaultImage;
        }

        $image = handleShowImageWithTypes($user->id, $url, 50, 50);

        // Level-related data
        $levelImages = '';
        if (! $withoutLevels) {
            $receiverImg = getImagePath(UserLevelHelper::getReceiverImage($user));
            $senderImg   = getImagePath(UserLevelHelper::getSenderImage($user));

            foreach ([$receiverImg, $senderImg] as $img) {
                if (!empty($img)) {
                    $levelImages .= "<img src='{$img}' style='width:32px;height:14px;margin-right:2px;'>";
                }
            }
        }

        $rawName = $user->name ?? '';

        // remove NULL bytes and control chars
        $cleanName = preg_replace('/[\x00-\x1F\x7F]/u', '', $rawName);

        // now safely escape
        $name = htmlspecialchars($cleanName, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $showUrl = $showUrl ?: $this->adminUserUrl($user->id);

        $flagHtml = '';
       
            $flagPath = getImagePath(@$user->country->flag);
            $flagTitle = app()->getLocale() === 'ar'
                ? e(@$user->country->name)
                : e(@$user->country->e_name);

            $flagHtml = "<img src='{$flagPath}' 
                        class='flag-image' 
                        alt='flag Image' 
                        title='{$flagTitle}' 
                        style='width:20px;height:auto;vertical-align:middle;margin-left:5px;'>";
        
        return <<<HTML
        <a href="{$showUrl}" style="display:flex;align-items:center;gap:10px;padding:10px;text-decoration:none;color:inherit;">
            {$image}
            <div>
                <strong style="font-size:16px;">{$name}</strong>{$flagHtml}<br>
                <span style="font-size:13px;">
                    UID: <span id="uid-{$user->id}">{$uid}</span>
                    <button onclick="event.preventDefault();event.stopPropagation();copyToClipboard('uid-{$user->id}')"
                        style="background:none;border:none;cursor:pointer;margin-left:5px;font-size:13px;color:#007bff;"
                        title="Copy UID">📝</button>
                </span><br>
                <span style="font-size:13px;">Special: {$special}</span><br>
                {$levelImages}
            </div>
        </a>
    HTML;
    }

    protected function adminUserUrl($id): string
    {
        if (Super::user()->type == "superadmin") {
            return url("superadmin/users/profile/{$id}");
        }
        return url("admin/users/{$id}");
    }
}
