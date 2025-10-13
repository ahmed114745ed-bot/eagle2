<?php

namespace App\Tik\Services;

use App\Helpers\Common;
use App\Tik\Repositories\CountryRepository;




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

    public function searchCountries($key, $page)
    {
        $perPage = 10;
        return $this->countryRepository->searchCountry($key, $page, $perPage);
    }
}
