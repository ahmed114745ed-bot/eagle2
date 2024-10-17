<?php
namespace Modules\CP\Http\Services;

use App\Repositories\CpRepository;
use App\Models\User;
use App\Helpers\Common;
use Illuminate\Http\Request;
use Modules\CP\Entities\CpRelation;
use Modules\CP\Repositories\CpRepository as RepositoriesCpRepository;
use Modules\CP\Transformers\CpListResource;
use Modules\CP\Transformers\RankingResource;
use Modules\CP\Transformers\RequestCpResource;

class CpserviceCo
{
    protected $cpRepository;

    public function __construct(RepositoriesCpRepository $cpRepository)
    {
        $this->cpRepository = $cpRepository;
    }

    public function makeRequestCp($request, $user)
    {
        $cpRelation = $this->cpRepository->getCpRelationById($request->cp_relation_id);

        if (!$cpRelation) {
            return Common::apiResponse(0, 'لا يوجد cp relations');
        }

        $cpCount = $this->cpRepository->getCpCount($user->id);

        if ($cpCount >= 15) {
            return Common::apiResponse(0, 'لقد تعديت العدد المسموح به!');
        }

        $existingCp = $this->cpRepository->checkExistingCp($user->id, $request->user_id);

        if ($existingCp) {
            return Common::apiResponse(0, 'لا يمكنك تقديم cp مع هذا المستخدم حاليا!');
        }

        $userRelation = $this->cpRepository->getUserRelationAvailable($user->id, $request->cp_relation_id);

        if ($userRelation) {
            $this->cpRepository->decrementUserRelationCount($userRelation);
        } else {
            if ($user->di < $cpRelation->price) {
                return Common::apiResponse(0, 'لا يوجد رصيد كافي من الكوينات برجاء الشحن!');
            }
        }

        $this->cpRepository->createCp([
            "cp_relation_id" => $request->cp_relation_id,
            "user_one_id" => $user->id,
            "user_two_id" => $request->user_id,
            "price" => $cpRelation->price,
        ]);

        $user->di -= $cpRelation->price;
        $user->save();

        return Common::apiResponse(1, 'تم الاضافه بنجاح');
    }

    public function getRequestCp($user)
    {
        $data = $this->cpRepository->getRequestsForUser($user->id);
        return Common::apiResponse(1, '', RequestCpResource::collection($data));
    }

    public function respondToRequest(Request $request)
    {
        $cp = $this->cpRepository->findCpById($request->cp_id);
        $user = $request->user();

        if (!$cp || $cp->status != 0) {
            return Common::apiResponse(0, 'لا يوجد cp');
        }

        if ($cp->user_two_id != $user->id) {
            return Common::apiResponse(0, 'هناك شئ ما خطا');
        }

        if ($request->status == 1) {
            $this->cpRepository->updateCpStatus($cp, 1);
        } elseif ($request->status == 0) {
            $this->cpRepository->updateCpStatus($cp, 2);
            $this->cpRepository->updateOrCreateUserRelation($cp->user_one_id, $cp->cp_relation_id);
        }

        return Common::apiResponse(1, 'تم الرد علي الطلب بنجاح');
    }

    public function getCpRanking()
    {
        $relationType = request("relationType") ?? CpRelation::first()?->id;
        $type = request("type") ?? 1;

        $data = $this->cpRepository->getCpRanking($relationType, $type);

        $first = $data->take(3);
        $second = $data->skip(3);
        $result = [
            "firstThree" => RankingResource::collection($first),
            "remain" => RankingResource::collection($second),
        ];

        return Common::apiResponse(1, '', $result);
    }

    public function getCpList($userId)
    {
        $data = $this->cpRepository->getCpList($userId);
        return Common::apiResponse(1, '', CpListResource::collection($data));
    }
    
}
