<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\Common;
use Illuminate\Http\Request;
use App\Services\AllGameService;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class AllGameController extends Controller
{
    protected $allGameService;

    public function __construct(AllGameService $allGameService)
    {
        $this->allGameService = $allGameService;
    }

    public function index()
    {
        $data = $this->allGameService->getAllGamesData();
        return Common::apiResponse(1, '', $data);
    }

    public function updateGame(Request $request)
    {
        $result = $this->allGameService->updateGame($request->game_id, $request->user());
        return Common::apiResponse($result['status'], $result['message'], $result['code']);
    }

    public function utdGameIndex()
    {
        $data = $this->allGameService->utdIndex();
        return Common::apiResponse(1, '', $data);
    }

    public function utdGameCreate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'url' => 'required|string|max:255',
            'image' => 'sometimes|image|mimes:jpeg,png,gif,bmp,tiff,webp',
            'mini_url' => 'nullable|string|max:255',
            'type' => 'nullable',
            'is_enable' => 'nullable',
            'custom_id' => 'nullable',

        ]);
        if ($validator->fails()) {
            return Common::apiResponse(0, implode(',', $validator->errors()->all()), null, 422);
        }

        $this->allGameService->createUtd($request);
        return Common::apiResponse(1, 'created successfully');
    }
    public function utdGameUpdate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'url' => 'required|string|max:255',
            'image' => 'sometimes|image|mimes:jpeg,png,gif,bmp,tiff,webp',
            'mini_url' => 'nullable|string|max:255',
            'type' => 'nullable',
            'is_enable' => 'nullable',
            'custom_id' => 'nullable',

        ]);
        if ($validator->fails()) {
            return Common::apiResponse(0, implode(',', $validator->errors()->all()), null, 422);
        }

        $this->allGameService->updateUtd($request);
        return Common::apiResponse(1, 'updated successfully');
    }
    public function utdGameSwitchUpdate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'is_enable' => 'required',
        ]);
        if ($validator->fails()) {
            return Common::apiResponse(0, implode(',', $validator->errors()->all()), null, 422);
        }
      $value =   $this->allGameService->updateSwitch($request);
      if( !$value)  return Common::apiResponse(1, 'failed');
        return Common::apiResponse(1, 'updated successfully');
    }
}
