<?php

namespace Utd\Gifts\Http\Controllers\Api;

use Utd\Gifts\Support\ClassResolver;
use Utd\Gifts\Entities\GiftCategory;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

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
