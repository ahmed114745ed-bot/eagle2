<?php

namespace App\Classes\Packs;

use App\Helpers\Common;
use App\Models\OVip;
use App\Models\User;
use App\Models\Ware;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * @property Collection $wares
 * @property Collection $packs
 * */
class AllowPacks
{


    private $packIds;
    private $user;
    private $packs;
    private $wares;
    private $data;
    private $userOVipLevel;
    private $vipPrices;
    public function __construct(User $user, array $data)
    {
        $this->user = $user;
        $this->data = $data;
        $this->userOVipLevel = $this->getUserVip();


        if (gettype(array_key_first($data)) == 'string') {
            $this->packIds = array_values($data);
        } else {
            $this->packIds = $data;
        }

        $this->initialize();
    }

    public function getUserVip(): int
    {
        $oVip = Common::ovip_center($this->user);

        if (gettype($oVip) != 'array') {
            return 0;
        }

        return key_exists('level', $oVip) ? @$oVip['level'] : 0;
    }

    public function isPackUsedAndExist(int $id)
    {
        $isFound = false;
        if ($this->packs->where('type', $id)->where('is_used', 1)->first()) {
            $isFound = true;
        }
        return $isFound;
    }

    public function getWare(int $id)
    {
        return $this->wares->where('type', $id)->first();
    }


    public function initialize()
    {
        $this->packs = $this->user->packs()->whereIn('type', $this->packIds)->get();
        $this->wares = Ware::query()
        ->selectRaw('type, MIN(level) as min_level, MAX(level) as max_level, MIN(id) as min_id, MAX(id) as max_id')
        ->whereIn('type', $this->packIds)
        ->where('is_active_for_vip', true)
        ->groupBy('type')
        ->get();

        Log::info('this is wares : ' . json_encode($this->wares));

        $this->vipPrices = $this->getVipPrices();
    }

    public function getVipPrices(): Collection
    {
        $cacheKey = 'ovips_prices';

        // Specify the number of seconds you want the data to be cached
        $seconds = 3600 * 24; // e.g., 3600 seconds = 1 hour

        // Retrieve the data from the cache or execute the query if it's not cached
        $ovips = Cache::remember($cacheKey, $seconds, function () {
            return OVip::query()->select(['id', 'price'])->get();
        });

        return $ovips;
    }

    public function getData(): array
    {
        $data = [];

        foreach ($this->data as $key => $value) {
            $ware      = $this->getWare($value);
            if ($value == 16) {
                Log::info("ware before is:" . json_encode($ware ));
            }
            $isAllow   = $this->isAllowToUser($ware) ?? false;
            $minLevel = @$ware->min_level;

            // if (!$isAllow) {

            // }

            $data[]    = [
                'key' => $key,
                'title' => __('api.' . $key . '_title',[],'ar'),
                'title_en' => __('api.' . $key . '_title',[],'en'),
                'description' => $this->getDescription($key, $isAllow, $minLevel, @$ware->max_level,'ar'),
                'description_en' => $this->getDescription($key, $isAllow, $minLevel, @$ware->max_level,'en'),
                'is_active' => $this->isPackUsedAndExist($value),
                'is_allow_to_user' => $isAllow,
                'min' => $minLevel,
                'max' => @$ware->max_level,
                'min_price' => @$this->vipPrices->where('id', $minLevel)?->first()?->price,
            ];
            if ($value == 16) {

            }

        }


        return $data;
    }

    public function isAllowToUser($ware)
    {
        if (!$ware) return null;
        $userlevel = $this->userOVipLevel;

        $packs = $this->packs;
        return $userlevel >= $ware->min_level && $userlevel <= $ware->max_level && $packs->where('target_id', $ware->max_id)->isNotEmpty();
    }

    private function getDescription(string $key, $isAllow, $minLevel, $maxLevel,$lang ='en')
    {
        if ($minLevel == null) return __('api.pack_not_allow_yet',[],$lang);

        if ($isAllow) return __('api.' . $key . '_description_allow',[],$lang);

        return __('api.' . $key . '_description', ['minLevel' => $minLevel, 'maxLevel' => $maxLevel],$lang);
    }
}
