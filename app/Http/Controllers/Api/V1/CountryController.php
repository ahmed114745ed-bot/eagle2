<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\Common;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Tik\Services\CountryService;
use App\Http\Resources\CountryResource;
use Doctrine\DBAL\Schema\Index;
use Illuminate\Support\Facades\DB;

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

    public function countries(){

        $allTypesWithCountries = DB::table('countries')
        ->leftJoin('users', 'users.country_id', '=', 'countries.id')
        ->select(
            'countries.name as country_name',
            'countries.flag as image',
            DB::raw('COUNT(users.id) as user_count')
        )
        ->groupBy('countries.id', 'countries.name', 'countries.flag')
        ->get();
        
        // تقسيم النتائج إلى المصفوفتين
        $allCountriesSortedByName = $allTypesWithCountries->sortBy('country_name')->values();
        $hotCountries = $allTypesWithCountries->sortByDesc('user_count')->take(20)->values();
        
        $data = [
            'all' => $allCountriesSortedByName,
            'hot' => $hotCountries,
        ];

        return Common::apiResponse(1,'', $data);
    }
}
