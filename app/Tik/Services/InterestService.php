<?php

namespace App\Tik\Services;

use Exception;
use App\Helpers\Common;
use Illuminate\Support\Facades\DB;
use App\Tik\Repositories\InterestRepository;


class InterestService
{
    public function __construct(private readonly InterestRepository $interestRepository) {}

    public function all($id)
    {
        return $this->interestRepository->all($id);
    }

    public function create($request)
    {
        if ($request->hasFile('img')) {
            $img = Common::upload('images', $request->file('img'));
        }
        $data = [
            'name' => $request->name,
            'img' =>  $img ?? ''
        ];
        $this->interestRepository->create($data);
        return true;
    }

    public function update($id, $request)
    {

        $data = [
            'name' => $request->name,
        ];
        if ($request->hasFile('img')) {
            $data['img'] = Common::upload('images', $request->file('img'));
        }
        $this->interestRepository->update($data, $id);
        return true;
    }

    public function delete($id)
    {

        $data = $this->interestRepository->findOrFail($id);
        $data->delete();
        return true;
    }

    public function show($id)
    {
        return $this->interestRepository->findOrFail($id);
    }
}
