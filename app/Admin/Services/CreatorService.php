<?php

namespace App\Admin\Services;

use App\Models\AdminUser;

class CreatorService
{
    /**
     *
     * @param  mixed  $creatorModelOrIdOrArray
     * @param  bool   $showUid
     * @param  string|null $showUrl
     * @return string
     */
    public function show($creatorModelOrIdOrArray, bool $showUid = true, string $showUrl = null): string
    {
        if (!$creatorModelOrIdOrArray) {
            return __('No creator');
        }

        if (is_array($creatorModelOrIdOrArray)) {
            $creatorId = $creatorModelOrIdOrArray['id'] ?? null;
            if (!$creatorId) return __('No creator');
            $creator = AdminUser::find($creatorId);
        } elseif (is_numeric($creatorModelOrIdOrArray)) {
            $creator = AdminUser::find($creatorModelOrIdOrArray);
        } else {
            $creator = $creatorModelOrIdOrArray;
        }
           
        if (!$creator) return __('No creator');

        $uid =  $creator->id;
        $name = htmlspecialchars($creator->username ?? 'Unknown', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        $defaultImage = asset('images/businessman-icon.jpg');
        $avatarPath = $creator?->avatar ?? null;
        $url = getImagePath($avatarPath) ?? $defaultImage;

        if (!isImageExists($url)) {
            $url = $defaultImage;
        }

        $image = "<img src='{$url}' alt='{$name}' style='width:50px;height:50px;border-radius:50%;object-fit:cover;'>";

        $showUrl = $showUrl ?: $this->creatorUrl($creator->id);

        $uidHtml = $showUid ? "UID: <span id='uid-{$creator->id}'>{$uid}</span>
            <button onclick=\"event.preventDefault();event.stopPropagation();copyToClipboard('uid-{$creator->id}')\"
                style='background:none;border:none;cursor:pointer;margin-left:5px;font-size:13px;color:#007bff;' title='Copy UID'>📝</button>"
            : '';

        return <<<HTML
        <a href="{$showUrl}" style="display:flex;align-items:center;gap:10px;padding:5px;text-decoration:none;color:inherit;">
            {$image}
            <div>
                <strong style="font-size:16px;">{$name}</strong><br>
                <span style="font-size:13px;">{$uidHtml}</span>
            </div>
        </a>
        HTML;
    }

    protected function creatorUrl(int $id): string
    {
        return url("admin/users/{$id}");
    }
}
