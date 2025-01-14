<?php

namespace App\Http\Controllers\utd;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Public\Entities\LevelInterval;

class LevelIntervalsController extends Controller
{
    public function index(){

        $perPage = request('per_page') ?? 10;
        $levelIntervals = LevelInterval::paginate($perPage);

        return response()->json([
            'status' => 'success',
            'data' => $levelIntervals,
            'message' => 'level intervals returned successfully',
        ]);
    }

    public function store(Request $request){

        $level_interval = LevelInterval::create([
            'name' => $request->name,
            'type' => $request->type,
            'min' => $request->min,
            'max' => $request->max
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'level interval returned successfully',
            'data' => $level_interval
        ]);
    }

    public function update($id, Request $request){

        LevelInterval::findOrFail($id)->update([
            'name' => $request->name,
            'type' => $request->type,
            'min' => $request->min,
            'max' => $request->max
        ]);

        return response()->json([
            'message' => 'level interval updated successfully',
            'status' => 'success',
        ]);
    }

    public function show($id){

        $levelInterval = LevelInterval::findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => $levelInterval,
            'message' => 'level interval returned successfully',
        ]);
    }

    public function destroy($id){
        LevelInterval::findOrFail($id)->delete();

        return response()->json([
            'message' => 'level interval deleted successfully',
            'status' => 'success',
        ]);
    }
}
