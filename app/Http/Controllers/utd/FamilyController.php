<?php

namespace App\Http\Controllers\utd;

use App\Helpers\Common;
use App\Http\Controllers\Controller;
use App\Models\Family;
use App\Traits\Dashboard\DashBoardTrait;
use Illuminate\Http\Request;

class FamilyController extends Controller
{
    use DashBoardTrait;
    public function index(){

        $perPage = request('per_page')?? 10;
        $families = Family::paginate($perPage);


        return Common::apiResponse(true, '', $families, 200);

    }

    public function store(Request $request){

        $image = null;

        if($request->has('image')){
            $image = Common::upload('images', $request->image);
        }

        $family  = Family::create([
            'name' => $request->name,
            'introduce' => $request->introduce,
            'notice' => $request->notice,
            'is_success' => $request->is_success,
            'image' => $image,
            'user_id'=> $request->user_id,
            'num' => $request->num,
        ]);

        return Common::apiResponse(true, '',  $family, 200);
    }

    public function update(Request $request, $id){


        if($request->has('image')){
            $image = Common::upload('images', $request->image);
            Family::findOrFail($id)->update([
                'image' => $image
            ]);
        }
        Family::findOrFail($id)->update([
            'name' => $request->name,
            'introduce' => $request->introduce,
            'notice' => $request->notice,
            'is_success' => $request->is_success,
            'user_id'=> $request->user_id,
            'num' => $request->num
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
