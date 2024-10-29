<?php

namespace App\Tik\Repositories;

use App\Models\Country;

class CountryRepository extends AbstractRepository
{

    
    /**
     * @param Model $model
     */
    public function __construct()
    {
        parent::__construct(new Country());
    }


    public function getCountries()
    {
        return $this->model->query()->where('status', 1)->get();
    }

    public function countryGet()
    {
        return $this->model->select('id', 'name', 'e_name', 'flag','iso')->orderByDesc('id')->get();
    }

    public function findById($id)
    {
        return $this->model->find($id);
    }

    public function findByPhoneCode($phoneCode)
    {
        return $this->model->query()->where('phone_code', $phoneCode)->first();
    }
}
