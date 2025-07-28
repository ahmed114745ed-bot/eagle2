<?php

namespace Modules\TribeReward\Http\Controllers\Api;

use App\Helpers\Common;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\TribeReward\Services\TribeService;
use Modules\TribeReward\Transformers\TribePeriodResource;

class TribeController extends Controller
{
    public function __construct(private readonly TribeService $tribeService)
    {
    }

    public function index(): \Illuminate\Http\JsonResponse
    {
        $tribePeriod = $this->tribeService->index();

        return Common::apiResponse(true, '', TribePeriodResource::make($tribePeriod), 200);
    }

    public function agencyRanking(): void
    {
        $agencyRanks = $this->tribeService->agencyRanking();

    }

}
