<?php

namespace Modules\CP\Http\Controllers\Api;

use App\Helpers\Common;
use App\Models\Cp;
use App\Models\GiftLog;
use App\Models\Pack;
use App\Models\Ware;
use Auth;
use DB;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\CP\Entities\CpRelation;
use Illuminate\Support\Facades\Validator;
use Modules\CP\Entities\UserRelationAvilable;
use Modules\CP\Http\Services\CpProfileService;
use Modules\CP\Http\Services\CpserviceCo;
use Modules\CP\Http\Services\ExtendCardService;
use Modules\CP\Transformers\CpListResource;
use Modules\CP\Transformers\RankingResource;
use Modules\CP\Transformers\RequestCpResource;

class CpController extends Controller
{
    protected $cpService,$extendCardService,$cpProfileService;

    public function __construct(CpserviceCo $cpService,ExtendCardService $extendCardService,CpProfileService $cpProfileService)
    {
        $this->cpService = $cpService;
        $this->extendCardService = $extendCardService;
        $this->cpProfileService = $cpProfileService;
    }

    public function makeRequestCp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'cp_relation_id' => 'required|exists:cp_relations,id',
        ]);

        if ($validator->fails()) {
            $errors = implode(',', $validator->errors()->all());
            return Common::apiResponse(0, $errors);
        }

        $user = $request->user();
        return $this->cpService->makeRequestCp($request, $user);
    }

    public function getRequestCp()
    {
        $user = Auth::user();
        return $this->cpService->getRequestCp($user);
    }

    public function RespondRequest(Request $request)
    {
        return $this->cpService->respondToRequest($request);
    }

    public function CpRanking()
    {
        return $this->cpService->getCpRanking();
    }

    public function cpList()
    {
        $userId = Auth::id();
        return $this->cpService->getCpList($userId);
    }

    public function extendCard(Request $request)
    {
        $user = Auth::user();
        return $this->extendCardService->extendCard($user, $request->ware_id);
    }


    public function cpProfile()
    {
        $userId = Auth::id();
        return $this->cpProfileService->getCpProfiles($userId);
    }

}