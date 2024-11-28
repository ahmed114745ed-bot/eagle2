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
        ->select('countries.name as country_name', 'countries.flag as image')
        ->get();

        $hotCountries = DB::table('users')
            ->join('countries', 'users.country_id', '=', 'countries.id')
            ->select('countries.name as country_name', DB::raw('count(users.id) as user_count'), 'countries.flag as image')
            ->groupBy('countries.name', 'image')
            ->orderByDesc('user_count')
            ->take(20)
            ->get();

        return response()->json([
            'data' => [
                'all' => $allTypesWithCountries,
                'hot' => $hotCountries
            ],
            'status' => 'success',
            'message' => 'Countries returned successfully'
        ]);
    }
}
