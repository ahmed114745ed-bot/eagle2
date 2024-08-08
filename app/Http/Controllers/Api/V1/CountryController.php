<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\Common;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Tik\Services\CountryService;
use App\Http\Resources\CountryResource;
use Doctrine\DBAL\Schema\Index;

class CountryController extends Controller
{

    public function __construct(private CountryService $countryService)
    {
    }

    public function allCountries()
    {
        $countries = $this->countryService->index();
        return Common::apiResponse(1, '', CountryResource::collection($countries));
    }

    public function getCountry($id)
    {
        $country = $this->countryService->findById($id);
        if ($country) {
            return Common::apiResponse(1, '', new CountryResource($country));
        }
        return Common::apiResponse(0, __('not found'), null, 404);
    }

    public function index()
    {
        return $this->countryService->index2();
    }
}
