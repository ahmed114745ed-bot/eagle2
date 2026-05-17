<?php

namespace Utd\AreaManager\Services;

use Utd\AreaManager\Entities\Area;
use Illuminate\Support\Facades\Auth;

class AreaService
{
    public function all()
    {
        return Area::where('is_active', true)->get();
    }

    public function find($id)
    {
        return Area::findOrFail($id);
    }

    public function create(array $data)
    {
        return Area::create($data);
    }

    public function update($id, array $data)
    {
        $area = $this->find($id);
        $area->update($data);
        return $area;
    }

    public function delete($id)
    {
        $area = $this->find($id);
        return $area->delete();
    }
}
