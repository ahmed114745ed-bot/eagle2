<?php

namespace Utd\Moments\Http\Controllers\utd;

use App\Helpers\Common;
use App\Http\Controllers\Controller;
use App\Models\Config;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Utd\Moments\Services\MomentService;
use Utd\Moments\Transformers\MomentDashboardResource;
use Utd\Moments\Transformers\utd\MomentResource;

class MomentsController extends Controller
{
    public function __construct(private MomentService $momentService) {}

    public function all(Request $request)
    {
        $data = $this->momentService->all($request->id, $request->per_page, $request->page);

        return Common::apiResponse(true, 'done', MomentResource::collection($data) );
    }

    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [

            'description' => 'required|string',
            'user_id' => 'required',
            'img' => 'required|mimes:jpeg,png,jpg,gif,svg|max:2048',

        ]);
        if ($validator->fails()) {
            return Common::apiResponse(0, __('api_responses.validation_error'), $validator->errors());
        }


        try {
            $this->momentService->createFromRequest($request);
            return Common::apiResponse(true, 'created successfully');
        } catch (Exception $exception) {

            return Common::apiResponse(0, $exception->getMessage(), null, 400);
        }
    }

    public function update($id, Request $request)
    {
        $validator = Validator::make($request->all(), [

            'name' => 'required|string',
            'img' => 'required|mimes:jpeg,png,jpg,gif,svg|max:2048',

        ]);
        if ($validator->fails()) {
            return Common::apiResponse(0, __('api_responses.validation_error'), $validator->errors());
        }

        try {
            $this->momentService->updateFromRequest($id, $request);
            return Common::apiResponse(true, 'updated successfully');
        } catch (Exception $exception) {

            return Common::apiResponse(0, $exception->getMessage(), null, 400);
        }
    }

    public function show($id)
    {
        $data = $this->momentService->find($id);

        return Common::apiResponse(true, 'done', new MomentResource($data) );
    }

    public function destroy($id)
    {
        try {
            $this->momentService->deleteById($id);
            return Common::apiResponse(true, 'deleted successfully');
        } catch (Exception $exception) {

            return Common::apiResponse(0, $exception->getMessage(), null, 400);
        }
    }

    public function search($uuid, Request $request)
    {

        try {
            $moment = $this->momentService->search($uuid);
            return Common::apiResponse(true, 'success', MomentResource::collection($moment) );
        } catch (Exception $exception) {

            return Common::apiResponse(0, $exception->getMessage(), null, 400);
        }
    }

    public function get_user_moments($user_id, Request $request)
    {

        try {
            $reels = $this->momentService->getUserMomentsForDashboard($user_id);
            // return $reels;
            return Common::apiResponse(true, 'success', MomentDashboardResource::collection($reels));
        } catch (Exception $exception) {

            return Common::apiResponse(0, $exception->getMessage(), null, 400);
        }
    }

    public function config(Request $request)
    {
        $conf = Config::where('name', 'upload_moment')->first();
        if (!$conf) {
            config::create([
                'name'  => 'upload_moment',
                'value' => $request->num,
            ]);
        } else {
            $conf->value = $request->num;
            $conf->save();
        }
        return Common::apiResponse(true, __('dashboard.update'), null);
    }
}
