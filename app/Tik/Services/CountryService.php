<?php

namespace App\Tik\Services;

use App\Helpers\Common;
use App\Tik\Repositories\CountryRepository;




class CountryService
{
    public function __construct(
        private readonly CountryRepository $countryRepository,
    ) {
    }

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
        $html = "
            <div style='font-family:Arial;padding:10px;'>
                <h2>{$country->name}</h2>
                <p><strong>Code:</strong> {$country->code}</p>
                <p><strong>Capital:</strong> {$country->capital}</p>
                <p><strong>Population:</strong> {$country->population}</p>
            </div>
        ";

        return Common::apiResponse(1, 'success', ['html' => $html]);
    }

    return Common::apiResponse(0, __('not found'), null, 404);
}
}