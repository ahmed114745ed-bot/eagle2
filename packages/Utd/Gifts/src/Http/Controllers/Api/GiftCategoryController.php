<?php

namespace Utd\Gifts\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Utd\Gifts\Entities\GiftCategory;
use App\Helpers\Common;
use App\Http\Resources\GiftCategoryResource;

class GiftCategoryController extends Controller
{
    public function index(Request $request)
    {
        $giftCategories = GiftCategory::orderBy('sort', 'asc')->get();

        return Common::apiResponse(1, '', GiftCategoryResource::collection($giftCategories));
    }
}
