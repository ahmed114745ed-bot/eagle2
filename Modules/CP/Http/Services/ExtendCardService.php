<?php
namespace Modules\CP\Http\Services;

use App\Repositories\WareRepository;
use App\Helpers\Common;
use App\Models\Pack;
use Illuminate\Support\Facades\Auth;
use Modules\CP\Repositories\CpRepository;
use Modules\CP\Repositories\PackRepository;
class ExtendCardService
{
    protected $cpRepository;
    protected $packRepository;

    public function __construct(CpRepository $cpRepository, PackRepository $packRepository)
    {
        $this->packRepository = $packRepository;
        $this->cpRepository = $cpRepository;
    }

    public function extendCard($user, $wareId)
    {
        if (!$wareId) {
            return Common::apiResponse(0, 'راجع البيانات المدخله');
        }

        $ware = $this->cpRepository->findWare($wareId);
        if (!$ware) {
            return Common::apiResponse(0, 'المنتج غير موجود');
        }

        if ($ware->price > $user->di) {
            return Common::apiResponse(0, 'لم تملك القيمه من الكويز');
        }

        $expire = 30;
        $existingPack = $this->packRepository->findByUserIdAndTargetId($user->id, $ware->id);

        if ($existingPack) {
            $existingPack->expire = $existingPack->expire ? now()->timestamp + ($expire * 86400) : now()->addDays($expire)->timestamp;
            $existingPack->save();
        } else {
            $this->packRepository->createPack([
                'user_id' => $user->id,
                'get_type' => $ware->get_type,
                'type' => $ware->type,
                'target_id' => $ware->id,
                'num' => 1,
                'expire' => $ware->expire ? now()->addDays($expire)->timestamp : 0,
                'use_num' => $ware->num,
            ]);
        }

        $user->di -= $ware->price;
        $user->save();

        return Common::apiResponse(1, 'تم الاضافه بنجاح');
    }
}
