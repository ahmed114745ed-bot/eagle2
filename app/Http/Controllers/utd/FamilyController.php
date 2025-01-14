<?php

namespace App\Http\Controllers\utd;

use App\Http\Controllers\Controller;
use App\Models\Family;
use Illuminate\Http\Request;

class FamilyController extends Controller
{
    public function index(){

        $perPage = request('per_page')?? 10;
        $families = Family::paginate($perPage);

        return response()->json([
            'status' => 'success',
            'message' => 'families returned successfully',
            'data' => $families
        ]);
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

        return response()->json([
            'message' => 'Family created successfully',
            'status' => 'success',
            'data' => $family
        ]);

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

        return response()->json([
            'message' => 'Family updated successfully',
            'status' => 'success',
        ]);

    }

    public function show($id){
        $family = Family::findOrFail($id);

        return response()->json([
            'status' => 'success',
            'message' => 'Family returned successfully',
            'data' => $family
        ]);
    }

    public function destroy($id){

        Family::findOrFail($id)->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Family deleted successfully',
        ]);
    }
}
