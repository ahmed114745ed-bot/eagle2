<?php
namespace Modules\CP\Http\Services;

use App\Repositories\WareRepository;
use App\Helpers\Common;
use App\Models\Pack;
use Illuminate\Support\Facades\Auth;
use Modules\CP\Repositories\CpRepository;
use Modules\CP\Repositories\PackRepository;
use Modules\CP\Transformers\CpListResource;

class CpProfileService
{
    protected $cpRepository;
    protected $packRepository;

    public function __construct(CpRepository $cpRepository, PackRepository $packRepository)
    {
        $this->packRepository = $packRepository;
        $this->cpRepository = $cpRepository;
    }

    public function getCpProfiles($userId)
    {
        $statuses = [1, 4];
        $data = $this->cpRepository->getUserCpProfiles($userId, $statuses);

        $vipCount = $this->packRepository->countUserVipPacks($userId);

        if ($vipCount == 1) {
            $data->take(7);
        } elseif ($vipCount >= 2) {
            $data->take(10);
        } else {
            $data->take(4);
        }

        $data = $data->get();

        return Common::apiResponse(1, '', CpListResource::collection($data));
    }
}
