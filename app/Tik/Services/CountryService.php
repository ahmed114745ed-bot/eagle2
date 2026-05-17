<?php

namespace App\Tik\Services;

use App\Helpers\Common;
use App\Models\CountryCategory;
use App\Models\ChangeCountryRequest;
use Utd\AreaManager\Entities\Region;
use App\Tik\Repositories\CountryRepository;
use Illuminate\Database\Eloquent\Collection;


class CountryService
{
    public function __construct(
        private readonly CountryRepository $countryRepository,
    ) {}

    public function index()
    {
        return $this->countryRepository->getCountries();
    }

    public function indexWithSupporters()
    {
        return $this->countryRepository->getCountriesWithSupporters();
    }

    public function indexByHotAndSupporters($categoryId): Collection|array
    {
        return $this->countryRepository->orderByHotAndSupporters($categoryId);
    }
    public function findById($id)
    {
        return $this->countryRepository->findById($id);
    }
    public function index2()
    {
        return $this->countryRepository->countryGet();
    }

    public function countryDetails($id)
    {
        $country = $this->findById($id);
        if ($country) {
            $bladeUrl = url("/countries/{$id}");

            return Common::apiResponse(1, 'success', $bladeUrl, 200);
        }

        return Common::apiResponse(0, __('not found'), null, 404);
    }

    public function searchCountries($key, $page, $areaManagerId = null)
    {
        $perPage = 10;
        return $this->countryRepository->searchCountry($key, $page, $perPage, $areaManagerId);
    }

    public function searchRegions($key, $page)
    {
        $perPage = 10;
        return Region::query()->selectRaw('concat(name) as name, id')
            ->where('name', 'like', '%' . $key . '%')
            ->orWhere('id', 'like', '%' . $key . '%')
            ->paginate($perPage, ['*'], 'page', $page);
    }

    public function countryCategory()
    {
        return CountryCategory::orderBy('sort', 'asc')->get();
    }

    public function changeRequest($data): true
    {
        $user = request()->user();
        $stop_invite_code = settings()->get('change_country');

        $existRequest =   ChangeCountryRequest::where('user_id', $user->id)->where('status', 'pending')->exists();
        if ($existRequest) return  throw new \Exception(__('you sent request before'));
        //  dd($stop_invite_code);
        if ($stop_invite_code  === '1') {
            ChangeCountryRequest::updateOrCreate([
                'user_id' => auth()->id(),
                'status' => 'accepted'
            ], $data + ['user_id' => auth()->id(), 'old_country' => $user->country_id]);


            $user->country_id = $data['country_id'];
            $user->save();
            $title = __('Change Country Request');
            $body = __('Your country change request has been accepted');
            Common::sendOfficialMessage($user->id, $title, $body);
            Common::send_firebase_notification($user->notification_id, $title, $body);
        } else {
            ChangeCountryRequest::updateOrCreate([
                'user_id' => auth()->id(),
                'status' => 'pending'
            ], $data + ['user_id' => auth()->id(), 'old_country' => $user->country_id]);
        }


        return true;
    }
}
