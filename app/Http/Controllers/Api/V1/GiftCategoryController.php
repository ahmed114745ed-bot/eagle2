<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\Common;
use App\Models\GiftCategory;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\GiftCategoryResource;


class GiftCategoryController extends Controller
{
    public function index(Request $request)
    {
        $giftCategories = GiftCategory::orderBy('sort', 'asc')->get();
        return Common::apiResponse(1, '', GiftCategoryResource::collection($giftCategories));
    }
}
