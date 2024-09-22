<?php

namespace App\Http\Controllers\Api\V1;

use Exception;

use App\Helpers\Common;

use App\Rules\ValidUsd;
use Illuminate\Http\Request;
use App\Tik\Services\TargetService;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;


class TargetController extends Controller
{
    public function __construct(private TargetService $targetService) {}

    public function index()
    {
        $data = $this->targetService->index();
        return Common::apiResponse(1, '', $data);;
    }


    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'level'       => 'required|numeric|unique:targets,level',
            'diamonds'        => 'required|numeric|unique:targets,diamonds',
            'usd' => ['required', 'numeric', new ValidUsd(floatval($request->diamonds))],
            'hours'        => 'nullable|numeric',
            'days'        => 'nullable|numeric',
            'agency_share'        => 'required|numeric|max:30',
            'moment'        => 'nullable|array',
            'reel'        => 'nullable|array',
        ]);
        if ($validator->fails()) {
            return Common::apiResponse(0, __('api_responses.validation_error'), $validator->errors());
        }

        try {
            $this->targetService->create($request);
            return Common::apiResponse(1, 'created successfully');
        } catch (Exception $exception) {

            return Common::apiResponse(0, $exception->getMessage(), null, 400);
        }
    }
    public function show(Request $request)
    {
        $data = $this->targetService->show($request->target_id);
        return Common::apiResponse(1, '', $data);
    }

    public function update(Request $request,)
    {
        $id = $request->target_id;

        $validator = Validator::make($request->all(), [
            'level'       => 'required|numeric|unique:targets,level,' . $id,
            'diamonds'        => 'required|numeric|unique:targets,diamonds,' . $id,
            'usd' => ['required', 'numeric', new ValidUsd(floatval($request->diamonds))],
            'hours'        => 'nullable|numeric',
            'days'        => 'nullable|numeric',
            'agency_share'        => 'required|numeric|max:30',
            'moment'        => 'nullable|array',
            'reel'        => 'nullable|array',
        ]);
        if ($validator->fails()) {
            return Common::apiResponse(0, __('api_responses.validation_error'), $validator->errors());
        }
        try {
            $this->targetService->update($request->target_id, $request);
            return Common::apiResponse(1, 'updated successfully');
        } catch (\Exception $exception) {

            return Common::apiResponse(0, $exception->getMessage(), null, 400);
        }
    }
}
