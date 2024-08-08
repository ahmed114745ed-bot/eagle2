<?php

namespace App\Tik\Services;

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

 public function findById($id)
 {
    return $this->countryRepository->findById($id);
 }
 public function index2()
 {
    return $this->countryRepository->countryGet();
 }
}