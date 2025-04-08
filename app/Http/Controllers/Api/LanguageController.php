<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Common;
use App\Http\Controllers\Controller;
use App\Http\Resources\LanguageResource;
use Illuminate\Http\Request;
use App\Models\Language;
use Illuminate\Http\JsonResponse;
class LanguageController extends Controller
{
    public function index(): JsonResponse
    {
        $languages = Language::where('is_enabled', true)
            ->select('id', 'name', 'code', 'direction')
            ->get();

            return Common::apiResponse(1, '', LanguageResource::collection($languages));

    }
}
