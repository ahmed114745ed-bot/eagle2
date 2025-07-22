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
        if (! @$agency) {
            return "
            <div style='display: flex; align-items: center; gap: 10px;'>
                         <span style='text-decoration: underline; cursor: pointer;'>unknown agency</span>
            </div>
        ";
        }
        $cacheKey = "agency_image_{$agency->id}";
        $image = Cache::remember($cacheKey, 3600, function () use ($agency){

            $path = @$agency->img;
            $defaultImage = asset("images/icon-agency.jpg");
            $url = getImagePath($path) ?? $defaultImage;

            if (!isImageExists($url)) {
                $url = $defaultImage;
            }

            return handleShowImageWithTypes($agency->id, $url, 40, 40, 0);
        });

        $profileUrl = route('admin.agency.profile', ['id' => $agency->id]);

        $name = $agency->name ?? __('No name');
        return "<a href='{$profileUrl}' style='text-decoration: none; color: inherit;'>
                        <div style='display: flex; align-items: center; gap: 10px;'>
                            {$image}
                            <div style='display: flex; flex-direction: column;'>
                                <span style='text-decoration: underline; cursor: pointer;'>{$name}</span>
                                <span style='font-size: smaller;'>ID: {$agency->id}</span>
                            </div>
                        </div>
                    </a>";
    }
}
