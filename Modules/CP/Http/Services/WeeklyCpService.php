<?php

namespace Modules\CP\Http\Services;


use Modules\CP\Repositories\WeeklyCpRepository;
use Modules\CP\Http\Resources\TopWeeklyCpResource;


class WeeklyCpService
{
    public function __construct(private readonly WeeklyCpRepository $weeklyCpRepository) {}

    public function perviousCpWinners()
    {
        $weeklyCp = $this->weeklyCpRepository->currentWeeklyCp();
        if (!$weeklyCp) throw new \Exception('there is not weekly cp now');
        return $this->weeklyCpRepository->perviousWeeklyCpWinners($weeklyCp->start_date);
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
        return$perviousWeeklyCp ;
    }

    public function userDetails($user)
    {
        $weeklyCp = $this->weeklyCpRepository->currentWeeklyCp();
        if (!$weeklyCp) throw new \Exception('there is not weekly cp now');
        $giftIds  = $weeklyCp->gifts->pluck('id')->toArray();
        return  $this->weeklyCpRepository->userDetails($giftIds, $weeklyCp, $user->id);
    }
}
