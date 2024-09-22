<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Target;

use App\Helpers\Common;
use App\Rules\ValidUsd;

use Illuminate\Http\Request;
use App\Tik\Services\TargetService;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\Dashboard\Target\AdminTargetResource;


class TargetController extends Controller
{
    public function __construct(private TargetService $targetService) {}

    public function index()
    {
        $data = $this->targetService->index();
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
        try {
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
        } catch (\Exception $exception) {

            return Common::apiResponse(0, $exception->getMessage(), null, 400);
        }
    }
    public function show(string $id)
    {

        return $this->targetService->show($id);
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
        try {
            $this->targetService->update($id, $request);
            return 200;
        } catch (\Exception $exception) {

            return Common::apiResponse(0, $exception->getMessage(), null, 400);
        }
    }
}
