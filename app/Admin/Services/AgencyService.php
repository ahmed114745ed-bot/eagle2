<?php

namespace App\Admin\Services;

use Illuminate\Support\Facades\Cache;

class AgencyService
{
    /**
     * @param $agency
     * @param $name
     * @return string
     */
    function adminAgencyData($agency): string
    {
        $cacheKey = "agency_image_{$agency->id}";
        $image = Cache::remember($cacheKey, 3600, function () {
            $path = @$this->img;
            $defaultImage = asset("images/icon-agency.jpg");
            $url = getImagePath($path) ?? $defaultImage;

            if (!isImageExists($url)) {
                $url = $defaultImage;
            }

            return handleShowImageWithTypes($this->id, $url, 40, 40, 0);
        });

        $profileUrl = route('admin.agency.profile', ['id' => $agency->id]);

        return "<a href='{$profileUrl}' style='text-decoration: none; color: inherit;'>
                        <div style='display: flex; align-items: center; gap: 10px;'>
                            {$image}
                            <div style='display: flex; flex-direction: column;'>
                                <span style='text-decoration: underline; cursor: pointer;'>{$agency->name}</span>
                                <span style='font-size: smaller;'>ID: {$agency->id}</span>
                            </div>
                        </div>
                    </a>";
    }
}
