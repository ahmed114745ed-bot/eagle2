<?php

namespace Utd\Gifts\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Utd\Gifts\Entities\GiftCategory;
use Utd\Gifts\Support\ClassResolver;

class GiftCategoryController extends Controller
{
    protected $Common;

    protected $GiftCategoryResource;

    public function __construct()
    {
        $this->Common = ClassResolver::helper('common');
        $this->GiftCategoryResource = ClassResolver::resource('gift_category');
    }

    public function index(Request $request)
    {
        $giftCategories = GiftCategory::orderBy('sort', 'asc')->get();

        return $this->Common::apiResponse(1, '', $this->GiftCategoryResource::collection($giftCategories));
    }
}
