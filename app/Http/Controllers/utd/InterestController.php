<?php

namespace App\Http\Controllers\utd;

use Exception;
use App\Helpers\Common;
use App\Models\ImageColor;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Tik\Services\InterestService;
use Illuminate\Support\Facades\Validator;

class InterestController extends Controller
{

    public function __construct(private InterestService $interestService) {}

    public function all(Request $request)
    {
        $data = $this->interestService->all($request->id, $request->per_page, $request->page);
        return Common::apiResponse(true, 'done', $data);
    }

    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [

            'name' => 'required|string',
            'img' => 'required|mimes:jpeg,png,jpg,gif,svg|max:2048',

        ]);
        if ($validator->fails()) {
            return Common::apiResponse(0, __('api_responses.validation_error'), $validator->errors());
        }

        try {
            $this->interestService->create($request);
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
            $this->interestService->update($id, $request);
            return Common::apiResponse(true, 'updated successfully');
        } catch (Exception $exception) {

            return Common::apiResponse(0, $exception->getMessage(), null, 400);
        }
    }

    public function show($id)
    {
        $data = $this->interestService->show($id);
        return Common::apiResponse(true, 'done', $data);
    }

    public function destroy($id)
    {
        try {
            $this->interestService->delete($id);
            return Common::apiResponse(true, 'deleted successfully');
        } catch (Exception $exception) {

            return Common::apiResponse(0, $exception->getMessage(), null, 400);
        }
    }
}
