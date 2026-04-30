<?php

namespace Utd\Gifts\Http\Controllers\Api;

use App\Helpers\Common;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Utd\Gifts\Http\Resources\GiftResource;
use Utd\Gifts\Services\GiftService;

class GiftController extends Controller
{
    protected GiftService $giftService;

    public function __construct(GiftService $giftService)
    {
        $this->giftService = $giftService;
    }

    public function index(Request $request)
    {
        $type = $request->type;
        $gifts = $this->giftService->index($type);

        return Common::apiResponse(true, '', GiftResource::collection($gifts), 200);
    }

    public function getByCategory(Request $request)
    {

        $categoryId = $request->input('type');
        $type = $request->input('type');
        $gifts = $this->giftService->getByCategory($categoryId, $type);

        return Common::apiResponse(true, '', GiftResource::collection($gifts), 200);
    }

    public function get_images(Request $request)
    {

        $gifts = $this->giftService->get_images();

        return Common::apiResponse(true, '', $gifts, 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string|max:255',
            'e_name' => 'nullable|string|max:255',
            'type' => 'required|numeric',
            'vip_level' => 'nullable|lt:256',
            'price' => 'required|numeric',
            'img' => 'required|mimes:jpeg,png,jpg,gif',
            'show_img' => 'required|mimes:jpeg,png,jpg,gif,svg,mp4,svga,ZZ',
            'image_type' => 'required|string|max:255',
            'show_img2' => 'nullable|mimes:jpeg,png,jpg,gif,svg',
            'sort' => 'nullable|numeric',
            'enable' => 'nullable|boolean',
            'music_gift' => 'nullable|boolean',
            'min_percentage' => 'nullable|numeric',
            'mid_percentage' => 'nullable|numeric',
            'max_percentage' => 'nullable|numeric',
            'win_probability' => 'nullable|numeric',

        ]);
        if ($validator->fails()) {
            return Common::apiResponse(0, __('api_responses.validation_error'), $validator->errors());
        }
        if ((($request->min_percentage + $request->mid_percentage + $request->max_percentage) !== 100) && ($request->type === 6)) {
            return Common::apiResponse(0, __('The sum of percentages must be equal to 100.'), 400);
        }
        $this->giftService->create($request);

        return Common::apiResponse(1, 'created successfully');
    }

    public function show(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'gift_id' => 'required|integer|exists:gifts,id',
        ]);
        if ($validator->fails()) {
            return Common::apiResponse(0, implode(',', $validator->errors()->all()), null, 422);
        }
        $data = $this->giftService->show($request->gift_id);

        return Common::apiResponse(1, '', $data);
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'gift_id' => 'required|integer|exists:gifts,id',
            'name' => 'nullable|string|max:255',
            'e_name' => 'nullable|string|max:255',
            'type' => 'required',
            'vip_level' => 'nullable|lt:256',
            'price' => 'required|numeric',
            'img' => 'nullable|mimes:jpeg,png,jpg,gif',
            'show_img' => 'nullable|mimes:jpeg,png,jpg,gif,svg,mp4,svga,ZZ',
            'image_type' => 'nullable|string|max:255',
            'show_img2' => 'nullable|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'sort' => 'nullable|numeric',
            'enable' => 'nullable|boolean',
            'music_gift' => 'nullable|boolean',
            'min_percentage' => 'nullable|numeric',
            'mid_percentage' => 'nullable|numeric',
            'max_percentage' => 'nullable|numeric',
            'win_probability' => 'nullable|numeric',

        ]);
        if ($validator->fails()) {
            return Common::apiResponse(0, __('api_responses.validation_error'), $validator->errors());
        }
        if ((($request->min_percentage + $request->mid_percentage + $request->max_percentage) !== 100) && ($request->type === 6)) {
            return Common::apiResponse(0, __('The sum of percentages must be equal to 100.'), 400);
        }
        try {
            $this->giftService->update($request);
        } catch (Exception $exception) {

            return Common::apiResponse(0, $exception->getMessage(), null, 400);
        }

        return Common::apiResponse(1, 'updated successfully');
    }
}
