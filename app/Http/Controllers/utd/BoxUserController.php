<?php

namespace App\Http\Controllers\utd;

use App\Helpers\Common;
use App\Http\Controllers\Controller;
use App\Models\BoxUse;
use Illuminate\Http\Request;

class BoxUserController extends Controller
{
    public function index(){
        $search = request('search');
        $result = BoxUse::when($search,function($q)use($search){
            $q->where('id', $search);
        })
        ->paginate(10);

        return Common::apiResponse(true, 'Success', $result);
    }

    public function show($id){
        $result = BoxUse::findOrFail($id);

        return Common::apiResponse(true, 'Success', $result);
    }

    public function delete($id){
        $result = BoxUse::findOrFail($id);

        $result->delete();

        return Common::apiResponse(true, 'Success');
    }

    public function delete_all(Request $request){

        $request->validate([
            'ids' => 'required'
        ]);

        $ids = explode(',', $request->ids);

        BoxUse::whereIn('id', $ids)->delete();

        return Common::apiResponse(true, 'Success');
    }

    public function update($id, Request $request){
        $validatedData = $request->validate([
            'box_id'       => 'required|integer|exists:boxs,id',
            'user_id'      => 'required|integer|exists:users,id',
            'coins'        => 'required|integer|min:0',
            'end_at'       => 'nullable|date',
            'room_uid'     => 'nullable|string|max:255',
            'room_id'      => 'nullable|string|max:255',
            'users_num'    => 'required|integer|min:0',
            'type'         => 'nullable|string|max:255',
            'label'        => 'nullable|string|max:255',
            'used_num'     => 'required|integer|min:0',
            'not_used_num' => 'required|integer|min:0',
        ]);

        $result = BoxUse::findOrFail($id);

        $result->update($validatedData);

        return Common::apiResponse(true, 'Success', $result);
    }
}
