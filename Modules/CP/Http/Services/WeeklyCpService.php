<?php

namespace Modules\CP\Http\Services;

use App\Models\Pack;
use App\Helpers\Common;
use App\Repositories\WareRepository;
use Illuminate\Support\Facades\Auth;
use Modules\Events\Entities\WeeklyStar;
use Modules\CP\Repositories\CpRepository;
use Modules\CP\Repositories\PackRepository;
use Modules\CP\Repositories\WeeklyCpRepository;
use Modules\CP\Http\Resources\TopWeeklyCpResource;
use Modules\CP\Http\Resources\UserWeeklyCpResource;

class WeeklyCpService
{
    public function __construct(private readonly WeeklyCpRepository $weeklyCpRepository) {}

    public function perviousCpWinners()
    {
        $weeklyCp = $this->weeklyCpRepository->currentWeeklyCp();
        if (!$weeklyCp) throw new \Exception('there is not weekly cp now');
        return $this->weeklyCpRepository->perviousWeeklyCpWinners('lover');
    }

    public function weeklyCpDetails()
    {
        $weeklyCp = $this->weeklyCpRepository->currentWeeklyCp();
        if (!$weeklyCp) throw new \Exception('there is not weekly cp now');
        $rule = $this->weeklyCpRepository->role();
        return [$weeklyCp, $rule];
    }

    public function topUsers()
    {
        $weeklyCp = $this->weeklyCpRepository->currentWeeklyCp();
        if (!$weeklyCp) throw new \Exception('there is not weekly cp now');

        $giftIds  = $weeklyCp->gifts->pluck('id')->toArray();

        $data  = $this->weeklyCpRepository->topUsers($giftIds, $weeklyCp);

        $firstTenQueries     = $data->take(10);
        $data  =  TopWeeklyCpResource::collection($firstTenQueries);

        return $data;
    }

    public function topOnePerviousWeeklyCp()
    {
        $perviousWeeklyCp = $this->weeklyCpRepository->perviousWeeklyCp();
        if (!$perviousWeeklyCp) throw new \Exception('there is not weekly cp ');
        return $this->weeklyCpRepository->firstPerviousWeeklyCp($perviousWeeklyCp->id);
    }

    public function userDetails($user)
    {
        $weeklyCp = $this->weeklyCpRepository->currentWeeklyCp();
        if (!$weeklyCp) throw new \Exception('there is not weekly cp now');
        $giftIds  = $weeklyCp->gifts->pluck('id')->toArray();
        return  $this->weeklyCpRepository->userDetails($giftIds, $weeklyCp, $user->id);
    }
}
