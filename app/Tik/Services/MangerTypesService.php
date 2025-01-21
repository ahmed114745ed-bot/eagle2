<?php

namespace App\Tik\Services;

use Exception;
use App\Helpers\Common;
use Illuminate\Support\Facades\DB;
use App\Tik\Repositories\MangerTypesRepository;


class MangerTypesService
{
    public function __construct(private readonly MangerTypesRepository $MangerTypesRepository) {}

    public function all($id)
    {
        return $this->MangerTypesRepository->all($id);
    }

    public function create($request)
    {
        if ($request->hasFile('img')) {
            $img = Common::upload('images', $request->file('img'));
        }
        $data = [
            'user_id' => $request->user_id,
            'description' => $request->user_id,
            'img' =>  $img ?? ''
        ];
        $this->MangerTypesRepository->create($data);
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
        $this->MangerTypesRepository->update($data, $id);
        return true;
    }

    public function delete($id)
    {

        $data = $this->MangerTypesRepository->findOrFail($id);
        $data->delete();
        return true;
    }

    public function show($id)
    {
        return $this->MangerTypesRepository->all($id);
    }

    public function search($id)
    {

        return $this->MangerTypesRepository->search($id);
    }
}
