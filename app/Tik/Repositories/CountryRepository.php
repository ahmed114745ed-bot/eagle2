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

    public function getCountriesWithSupporters()
    {
        return $this->model->query()
            ->select('id', 'name', 'e_name', 'flag', 'language', 'phone_code', 'iso')
            ->where('status', 1)
            ->with([
                'supporters' => function ($q) {
                    $q->orderByDesc('total_sent')->take(3);
                },
                'supporters.sender:id,name,uuid',
                'supporters.sender.profile:id,user_id,avatar'
            ])
            ->get();
    }

    public function countryGet()
    {
        return $this->model->select('id', 'name', 'e_name', 'flag', 'iso')->orderByDesc('id')->get();
    }

    public function findById($id)
    {
        return $this->model->find($id);
    }

    public function findByPhoneCode($phoneCode)
    {
        return $this->model->query()->where('phone_code', $phoneCode)->first();
    }

    public function searchCountry($key, $page, $perPage)
    {
        return $this->model->query()->selectRaw('concat(name, " - ", e_name) as name, id')
            ->where('name', 'like', '%' . $key . '%')
            ->orWhere('e_name', 'like', '%' . $key . '%')
            ->orWhere('id', 'like', '%' . $key . '%')
            ->paginate($perPage, ['*'], 'page', $page);
    }
}
