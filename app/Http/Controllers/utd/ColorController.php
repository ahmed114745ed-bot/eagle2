<?php

namespace App\Http\Controllers\utd;


use App\Helpers\Common;
use App\Models\ImageColor;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ColorController extends Controller
{

    public function index(Request $request)
    {
        $id = $request->id;
        $perPage = $request->per_page;
        $page = $request->page;
        $data = ImageColor::when(isset($id), function ($query) use ($id) {
            $query->where('id', $id);
        })->paginate($perPage, ['*'], 'page', $page);
        return Common::apiResponse(true, 'done', $data);
    }
}
