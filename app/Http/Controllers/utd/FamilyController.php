<?php

namespace App\Http\Controllers\utd;

use App\Helpers\Common;
use App\Http\Controllers\Controller;
use App\Models\Family;
use Illuminate\Http\Request;

class FamilyController extends Controller
{
    public function index(){

        $perPage = request('per_page')?? 10;
        $families = Family::paginate($perPage);


        return Common::apiResponse(true, '', $families, 200);

    }

    public function store(Request $request){


        $family  = Family::create([
            'name' => $request->name,
            'introduce' => $request->introduce,
            'notice' => $request->notice,
            'is_success' => $request->is_success,
            'image' => $request->image,
            'user_id'=> $request->user_id
        ]);

        return Common::apiResponse(true, '',  $family, 200);
    }

    public function update(Request $request, $id){


        Family::findOrFail($id)->update([
            'name' => $request->name,
            'introduce' => $request->introduce,
            'notice' => $request->notice,
            'is_success' => $request->is_success,
            'image' => $request->img,
            'user_id'=> $request->user_id
        ]);

        return Common::apiResponse(1, 'Family updated successfully');


    }

    public function show($id){
        $family = Family::findOrFail($id);

        return Common::apiResponse(true, '', $family, 200);
    }

    public function destroy($id){

        Family::findOrFail($id)->delete();

        return Common::apiResponse(1, 'success', null, 200);
    }
}
