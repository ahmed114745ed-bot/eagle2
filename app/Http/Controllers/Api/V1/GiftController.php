<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Gift;
use App\Helpers\Common;
use Illuminate\Http\Request;
use App\Tik\Services\GiftService;
use App\Http\Controllers\Controller;
use App\Http\Resources\GiftResource;
use Illuminate\Support\Facades\Validator;

class GiftController extends Controller
{
    public function __construct(private GiftService $giftService) {}
    public function index(Request $request)
    {
        $gifts = $this->giftService->index($request);
        return Common::apiResponse(true, '', GiftResource::collection($gifts), 200);
    }

    public function allGifts()
    {
        $gifts = $this->giftService->index(null);
        return Common::apiResponse(1, '',  $gifts);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'         => 'nullable|string|max:255',
            'e_name'         => 'nullable|string|max:255',
            'type'         => 'required',
            'vip_level'         => 'nullable|lt:256',
            'price'         => 'required|numeric',
            'img'          => 'nullable|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'show_img'          => 'required|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'image_type'         => 'required|string|max:255',
            'show_img2'          => 'required|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'sort'         => 'nullable|numeric',
            'enable'         => 'nullable|boolean',
            'music_gift'         => 'nullable|boolean',
            'min_percentage'         => 'nullable|numeric',
            'mid_percentage'         => 'nullable|numeric',
            'max_percentage'         => 'nullable|numeric',
            'win_probability'         => 'nullable|numeric',

        ]);
        if ($validator->fails()) {
            return Common::apiResponse(0, __('api_responses.validation_error'), $validator->errors());
        }
        if ((($request->min_percentage + $request->mid_percentage + $request->max_percentage) != 100) && ($request->type == 6)) {
            return Common::apiResponse(0, __('The sum of percentages must be equal to 100.'), 400);
        }
        $this->giftService->create($request);

        return Common::apiResponse(1, 'created successfully');

    }

    public function show(Request $request)
    {
        $data = $this->giftService->show($request->gift_id);
        return Common::apiResponse(1, '', $data);
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'gift_id'  => 'required|numeric',
            'name'         => 'nullable|string|max:255',
            'e_name'         => 'nullable|string|max:255',
            'type'         => 'required',
            'vip_level'         => 'nullable|lt:256',
            'price'         => 'required|numeric',
            'img'          => 'nullable|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'show_img'          => 'required|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'image_type'         => 'required|string|max:255',
            'show_img2'          => 'required|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'sort'         => 'nullable|numeric',
            'enable'         => 'nullable|boolean',
            'music_gift'         => 'nullable|boolean',
            'min_percentage'         => 'nullable|numeric',
            'mid_percentage'         => 'nullable|numeric',
            'max_percentage'         => 'nullable|numeric',
            'win_probability'         => 'nullable|numeric',

        ]);
        if ($validator->fails()) {
            return Common::apiResponse(0, __('api_responses.validation_error'), $validator->errors());
        }
        if ((($request->min_percentage + $request->mid_percentage + $request->max_percentage) != 100) && ($request->type == 6)) {
            return Common::apiResponse(0, __('The sum of percentages must be equal to 100.'), 400);
        }
        if ($validator->fails()) {
            return Common::apiResponse(0, __('api_responses.validation_error'), $validator->errors());
        }

        $this->giftService->update($request);

        return Common::apiResponse(1, 'updated successfully');
    }
}
