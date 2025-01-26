<?php

namespace App\Http\Controllers;

use App\Helpers\Common;
use App\Http\Resources\Api\V1\BannerResource;
use App\Http\Services\BannerServices;
use App\Models\Banner;
use App\Models\UserBannerShow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class BannerController extends Controller
{
    public function __construct(private BannerServices $bannerServices)
    {
    }




    public function index()
    {
        $user = Auth::user();
        $now = now();

        // Cache key specific to the user
        $cacheKey = "user_{$user->id}_banners";

        // Check if banners are already cached
        $banners = Cache::remember($cacheKey, now()->addHours(24), function () use ($user, $now) {
            // Query banners only if not cached
            $ids = UserBannerShow::whereHas("banner", function ($q) use ($now) {
                $q->where('is_active', true)
                    ->whereNotNull('publish_at')
                    ->where(function ($q) use ($now) {
                        $q->whereRaw("DATE_ADD(created_at, INTERVAL expire DAY) > ?", [$now])
                          ->orWhere('expire', 0);
                    });
            })
            ->where("user_id", $user->id)
            ->pluck("banner_id")
            ->toArray();

            $countBanner = Banner::where('is_active', true)->count();

            if ($countBanner > 1) {
                if ((count($ids) + 1) == $countBanner) {
                    UserBannerShow::where("user_id", $user->id)->delete();
                }
            } else {
                UserBannerShow::where("user_id", $user->id)->delete();
            }

            return $this->bannerServices->index($ids);
        });



        // Save new banner show if banners exist
        if ($banners->count()) {
            $baner_new = new UserBannerShow();
            $baner_new->user_id = $user->id;
            $baner_new->banner_id = $banners->first()->id;
            $baner_new->save();
        }

        return Common::apiResponse(true, 'successful', BannerResource::collection($banners));
    }



    public function index2()
    {
        $user = Auth::user();
        $now = now();
        $dataShow = UserBannerShow::whereHas("banner", function ($q) use ($now) {
            $q->where('is_active', true)
                ->whereNotNull('publish_at')->where(fn ($q) => $q->whereRaw("DATE_ADD(created_at, INTERVAL expire DAY) > '$now'")->orWhere('expire', 0));
        })->where("user_id", $user->id)->get();
        $ids = $dataShow->pluck('banner_id');
        $banners = $this->bannerServices->index2($ids);
        if (empty($banners)) {
            UserBannerShow::where("user_id", $user->id)->delete();
           return Common::apiResponse(true, 'successful', null);
        }
        // UserBannerShow::where("user_id", $user->id)->delete();
        try {
            if ($banners) {
                $baner_new = new UserBannerShow();
                $baner_new->user_id = $user->id;
                $baner_new->banner_id = $banners->id;
                $baner_new->save();
            }
            return Common::apiResponse(true, 'successful', new BannerResource($banners));
        } catch (\Exception $exception) {
            return Common::apiResponse(true, 'successful', null);
        };
    }
}
