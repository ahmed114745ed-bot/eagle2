<?php

namespace App\Http\Controllers\Dashboard\Target;

use App\Models\Target;

use App\Helpers\Common;
use App\Rules\ValidUsd;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\Dashboard\Target\AdminTargetResource;


class AdminTargetController extends Controller
{
    public function index()
    {
        $data = Target::orderBy('diamonds')->get();
        return $data;
    }



    public function store(Request $request)
    {
       
        $request->validate([
            'level'       => 'required|numeric|unique:targets,level',
            'diamonds'        => 'required|numeric|unique:targets,diamonds',
            'usd' => ['required', 'numeric', new ValidUsd(floatval($request->diamonds))],
            'hours'        => 'nullable|numeric',
            'days'        => 'nullable|numeric',
            'agency_share'        => 'required|numeric|max:30',
            'moment'        => 'nullable|array',
            'reel'        => 'nullable|array',
        ]);
        if (isset($request->moment)) {
            $arrayMoment = array_values($request->moment);
            // Convert the values to a comma-separated string
            $moment = implode(',', $arrayMoment);
           
        }
        if (isset($request->reel)) {
            $arrayReel = array_values($request->reel);
            // Convert the values to a comma-separated string
            $reel = implode(', ', $arrayReel);
        }


        Target::insert([
            'level'       => $request->level,
            'diamonds'        => $request->diamonds,
            'usd' => $request->usd,
            'hours'        => $request->hours,
            'days'        => $request->days,
            'agency_share'        => $request->agency_share,
            'moment'        => $moment ?? null,
            'reel'        => $reel ?? null,
        ]);
        return response()->json([
            'status' => 200,
        ]);
    }
    public function show(string $id)
    {
        
        $data = Target::find($id);
        return new $data;
    }

    public function update(Request $request, string $id)
    {
       
        $request->validate([
            'level'       => 'required|numeric|unique:targets,level,' . $id,
            'diamonds'        => 'required|numeric|unique:targets,diamonds,' . $id,
            'usd' => ['required', 'numeric', new ValidUsd(floatval($request->diamonds))],
            'hours'        => 'nullable|numeric',
            'days'        => 'nullable|numeric',
            'agency_share'        => 'required|numeric|max:30',
            'moment'        => 'nullable|array',
            'reel'        => 'nullable|array',
        ]);

        if (isset($request->moment)) {
            $arrayMoment = array_values($request->moment);
            // Convert the values to a comma-separated string
            $moment = implode(',', $arrayMoment);
           
        }
        if (isset($request->reel)) {
            $arrayReel = array_values($request->reel);
            // Convert the values to a comma-separated string
            $reel = implode(', ', $arrayReel);
        }

        $target = Target::find($id);
        $target->level = $request->level;
        $target->diamonds = $request->diamonds;
        $target->usd = $request->usd;
        $target->hours = $request->hours;
        $target->days = $request->days;
        $target->agency_share = $request->agency_share;
        $target->moment = $moment ?? null;
        $target->reel = $reel ?? null;
        $target->update();
        return 200;
    }

}
