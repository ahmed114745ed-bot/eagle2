<?php

namespace Utd\Gifts\Http\Controllers\Api;

use Exception;
use Utd\Gifts\Entities\Gift;
use Utd\Gifts\Support\ClassResolver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class GiftController extends Controller
{
    protected $giftService;
    protected $Common;
    protected $GiftResource;
    protected $commonClass;
    protected $resourceClass;
    
    public function __construct() 
    {
        $giftServiceClass = ClassResolver::service('gift');
        $this->giftService = app($giftServiceClass);
        $this->Common = ClassResolver::helper('common');
        $this->GiftResource = ClassResolver::resource('gift');
        $this->commonClass = $this->Common;
        $this->resourceClass = $this->GiftResource;
    }
    public function index(Request $request)
    {
        $type = $request->type;
        $gifts = $this->giftService->index($type);
        return $this->commonClass::apiResponse(true, '', $this->resourceClass::collection($gifts), 200);
    }
    public function getByCategory(Request $request)
    {

        $categoryId = $request->input('type');
        $type       = $request->input('type'); 
        $gifts = $this->giftService->getByCategory($categoryId, $type);
        return $this->commonClass::apiResponse(true, '', $this->resourceClass::collection($gifts), 200);
    }
    
    public function get_images(Request $request)
    {

        $gifts = $this->giftService->get_images();
        return $this->commonClass::apiResponse(true, '', $gifts, 200);
    }


    public function allGifts(Request $request)
    {
        $gifts = $this->giftService->allGift($request->page, $request->per_page);
        return $this->commonClass::apiResponse(1, '',  $gifts);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'         => 'nullable|string|max:255',
            'e_name'         => 'nullable|string|max:255',
            'type'         => 'required|numeric',
            'vip_level'         => 'nullable|lt:256',
            'price'         => 'required|numeric',
            'img'          => 'required|mimes:jpeg,png,jpg,gif',
            'show_img'          => 'required|mimes:jpeg,png,jpg,gif,svg,mp4,svga,ZZ',
            'image_type'         => 'required|string|max:255',
            'show_img2'          => 'nullable|mimes:jpeg,png,jpg,gif,svg',
            'sort'         => 'nullable|numeric',
            'enable'         => 'nullable|boolean',
            'music_gift'         => 'nullable|boolean',
            'min_percentage'         => 'nullable|numeric',
            'mid_percentage'         => 'nullable|numeric',
            'max_percentage'         => 'nullable|numeric',
            'win_probability'         => 'nullable|numeric',

        ]);
        if ($validator->fails()) {
            return $this->commonClass::apiResponse(0, __('api_responses.validation_error'), $validator->errors());
        }
        if ((($request->min_percentage + $request->mid_percentage + $request->max_percentage) != 100) && ($request->type == 6)) {
            return $this->commonClass::apiResponse(0, __('The sum of percentages must be equal to 100.'), 400);
        }
        $this->giftService->create($request);

        return $this->commonClass::apiResponse(1, 'created successfully');
    }

    public function storeList(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'         => 'nullable|string|max:255',
            'e_name'         => 'nullable|string|max:255',
            'type'         => 'required|numeric',
            'vip_level'         => 'nullable|lt:256',
            'price'         => 'required|numeric',
            'img'          => 'required',
            'show_img'          => 'required',
            'image_type'         => 'required|string|max:255',
            'show_img2'          => 'nullable|mimes:jpeg,png,jpg,gif,svg',
            'sort'         => 'nullable|numeric',
            'enable'         => 'nullable|boolean',
            'music_gift'         => 'nullable|boolean',
            'min_percentage'         => 'nullable|numeric',
            'mid_percentage'         => 'nullable|numeric',
            'max_percentage'         => 'nullable|numeric',
            'win_probability'         => 'nullable|numeric',

        ]);
        if ($validator->fails()) {
            return $this->commonClass::apiResponse(0, __('api_responses.validation_error'), $validator->errors());
        }
        if ((($request->min_percentage + $request->mid_percentage + $request->max_percentage) != 100) && ($request->type == 6)) {
            return $this->commonClass::apiResponse(0, __('The sum of percentages must be equal to 100.'), 400);
        }
        try{
        $this->giftService->create($request);

        return $this->commonClass::apiResponse(1, 'created successfully');
        }catch (\Exception $exception) {

            return $this->commonClass::apiResponse(0, $exception->getMessage(), null, 400);
        }
    }






    public function show(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'gift_id'  => 'required|integer|exists:gifts,id',
        ]);
        if ($validator->fails()) {
            return $this->commonClass::apiResponse(0, implode(',', $validator->errors()->all()), null, 422);
        }
        $data = $this->giftService->show($request->gift_id);
        return $this->commonClass::apiResponse(1, '', $data);
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'gift_id'  => 'required|integer|exists:gifts,id',
            'name'         => 'nullable|string|max:255',
            'e_name'         => 'nullable|string|max:255',
            'type'         => 'required',
            'vip_level'         => 'nullable|lt:256',
            'price'         => 'required|numeric',
            'img'          => 'nullable|mimes:jpeg,png,jpg,gif',
            'show_img'          => 'nullable|mimes:jpeg,png,jpg,gif,svg,mp4,svga,ZZ',
            'image_type'         => 'nullable|string|max:255',
            'show_img2'          => 'nullable|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'sort'         => 'nullable|numeric',
            'enable'         => 'nullable|boolean',
            'music_gift'         => 'nullable|boolean',
            'min_percentage'         => 'nullable|numeric',
            'mid_percentage'         => 'nullable|numeric',
            'max_percentage'         => 'nullable|numeric',
            'win_probability'         => 'nullable|numeric',

        ]);
        if ($validator->fails()) {
            return $this->commonClass::apiResponse(0, __('api_responses.validation_error'), $validator->errors());
        }
        if ((($request->min_percentage + $request->mid_percentage + $request->max_percentage) != 100) && ($request->type == 6)) {
            return $this->commonClass::apiResponse(0, __('The sum of percentages must be equal to 100.'), 400);
        }
        try {
            $this->giftService->update($request);
        } catch (Exception $exception) {

            return $this->commonClass::apiResponse(0, $exception->getMessage(), null, 400);
        }
        return $this->commonClass::apiResponse(1, 'updated successfully');
    }

    public function musicSwitchUpdate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'music_gift' => 'required|boolean',
            'gift_id' => 'required|integer|exists:gifts,id',
        ]);
        if ($validator->fails()) {
            return $this->commonClass::apiResponse(0, __('api_responses.validation_error'), $validator->errors());
        }
        $value =   $this->giftService->updateSwitch($request->music_gift, $request->gift_id, 'music_gift');
        if (!$value)  return $this->commonClass::apiResponse(1, 'failed');
        return $this->commonClass::apiResponse(1, 'updated successfully');
    }

    public function enableSwitchUpdate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'enable' => 'required|boolean',
            'gift_id' => 'required|integer|exists:gifts,id',
        ]);
        if ($validator->fails()) {
            return $this->commonClass::apiResponse(0, __('api_responses.validation_error'), $validator->errors());
        }
        $value =   $this->giftService->updateSwitch($request->enable, $request->gift_id, 'enable');
        if (!$value)  return $this->commonClass::apiResponse(1, 'failed');
        return $this->commonClass::apiResponse(1, 'updated successfully');
    }

    public function isPlaySwitchUpdate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'is_play' => 'required|boolean',
            'gift_id' => 'required|integer|exists:gifts,id',
        ]);
        if ($validator->fails()) {
            return $this->commonClass::apiResponse(0, __('api_responses.validation_error'), $validator->errors());
        }
        $value =   $this->giftService->updateSwitch($request->is_play, $request->gift_id, 'is_play');
        if (!$value)  return $this->commonClass::apiResponse(1, 'failed');
        return $this->commonClass::apiResponse(1, 'updated successfully');
    }

    public function typeGift(Request $request)
    {
        return translate(TYPE_GIFT);
    }
}
