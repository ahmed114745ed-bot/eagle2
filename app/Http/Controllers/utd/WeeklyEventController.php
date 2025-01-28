<?php

namespace App\Http\Controllers\utd;

use Exception;
use App\Helpers\Common;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Modules\Events\Entities\PkReward;
use Modules\Events\Entities\WeeklyStar;
use Illuminate\Support\Facades\Validator;

class WeeklyEventController extends Controller
{

    public function index(Request $request)
    {
        $id = $request->id;
        $perPage = $request->per_page;
        $page = $request->page;
        $data = WeeklyStar::where('type', $request->type)->with('gifts')->when(isset($id), function ($query) use ($id) {
            $query->where('id', $id);
        })->paginate($perPage, ['*'], 'page', $page);
        return Common::apiResponse(true, 'done', $data);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'start_date' => 'required|date|date_format:Y-m-d',
            'type' => 'required|string',
            'gifts' => 'required|array|size:3',

        ]);

        if ($validator->fails()) {
            return Common::apiResponse(0, __('api_responses.validation_error'), $validator->errors());
        }
        try {
            $lastStartDate = WeeklyStar::max('start_date');
            $minStartDate = $lastStartDate ? \Carbon\Carbon::parse($lastStartDate)->addWeek()->toDateString() : null;
            if ($minStartDate == $request->start_date) {
                return Common::apiResponse(0, __('date must be after ' . $minStartDate),);
            }

            $weeklyEvent = WeeklyStar::create($request->all());
            $weeklyEvent->gifts()->sync($request->gifts);
            return Common::apiResponse(true, 'created successfully');
        } catch (Exception $exception) {

            return Common::apiResponse(0, $exception->getMessage(), null, 400);
        }
    }

    public function defaultDate(Request $request)
    {
        $lastStartDate = WeeklyStar::where("type", $request->type)->max('start_date');

        $minStartDate = $lastStartDate ? \Carbon\Carbon::parse($lastStartDate)->addDay(8)->toDateString() : null;
        return Common::apiResponse(true, 'done', $minStartDate);
    }

    public function show($id)
    {
        try {
            $data = WeeklyStar::with('gifts')->findOrFail($id);
            return Common::apiResponse(true, ' successfully',  $data);
        } catch (Exception $exception) {

            return Common::apiResponse(0, $exception->getMessage(), null, 400);
        }
    }

    public function update($id, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'start_date' => 'required|date|date_format:Y-m-d',
            'type' => 'required|string',
            'gifts' => 'required|array|size:3',

        ]);

        if ($validator->fails()) {
            return Common::apiResponse(0, __('api_responses.validation_error'), $validator->errors());
        }

        if ($validator->fails()) {
            return Common::apiResponse(0, __('api_responses.validation_error'), $validator->errors());
        }

        try {
            $data = WeeklyStar::findOrFail($id);
            $data->update($request->all());
            $data->gifts()->sync($request->gifts);
            return Common::apiResponse(true, 'updated successfully');
        } catch (Exception $exception) {

            return Common::apiResponse(0, $exception->getMessage(), null, 400);
        }
    }

    public function destroy($id)
    {
        try {
            $data = WeeklyStar::findOrFail($id);
            $data->delete();
            
            return Common::apiResponse(true, 'deleted successfully',  $data);
        } catch (Exception $exception) {

            return Common::apiResponse(0, $exception->getMessage(), null, 400);
        }
    }

    public function allGifts($pkId, Request $request)
    {
        $id = $request->id;
        $perPage = $request->per_page;
        $page = $request->page;
        $data = PkReward::where('pk_event_id', $pkId)->where("pk_type", $request->pk_type)->where('level', $request->level)->when(isset($id), function ($query) use ($id) {
            $query->where('id', $id);
        })->with('ware', 'vip')->paginate($perPage, ['*'], 'page', $page);
        return Common::apiResponse(true, 'done', $data);
    }

    public function storeGift(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'pk_event_id' => 'required',
            'pk_type' => 'required|string',
            'level' => 'required|numeric|in:1,2,3',
            'type' => 'required',
            'target1' => 'nullable',
            'target2' => 'nullable',
            'target3' => 'nullable',
            'expire'  => 'required',
        ]);

        if ($validator->fails()) {
            return Common::apiResponse(0, __('api_responses.validation_error'), $validator->errors());
        }
        try {
            if ($request->hasFile('target4')) {
                $target = Common::upload('images', $request->file('target4'));
            }

            $data = [
                'pk_event_id' => $request->pk_event_id,
                'pk_type' => $request->pk_type,
                'level' => $request->level,
                'type' => $request->type,
                'expire'  => $request->expire,
            ];
            PkReward::create($data);
            return Common::apiResponse(true, 'created successfully');
        } catch (Exception $exception) {

            return Common::apiResponse(0, $exception->getMessage(), null, 400);
        }
    }

    public function updateGift($id, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'pk_event_id' => 'required',
            'pk_type' => 'required|string',
            'level' => 'required|numeric|in:1,2,3',
            'type' => 'required',
            'target1' => 'nullable',
            'target2' => 'nullable',
            'target3' => 'nullable',
            'expire'  => 'required',
        ]);

        if ($validator->fails()) {
            return Common::apiResponse(0, __('api_responses.validation_error'), $validator->errors());
        }
        try {
            if ($request->hasFile('target4')) {
                $target = Common::upload('images', $request->file('target4'));
            }

            $data = [
                'pk_event_id' => $request->pk_event_id,
                'pk_type' => $request->pk_type,
                'level' => $request->level,
                'type' => $request->type,
                'expire'  => $request->expire,
            ];
            $reward =  PkReward::findOrFail($id);
            $reward->update($data);
            return Common::apiResponse(true, 'created successfully');
        } catch (Exception $exception) {

            return Common::apiResponse(0, $exception->getMessage(), null, 400);
        }
    }

    public function destroyGift($id)
    {
        try {
            $data =  PkReward::findOrFail($id);
            $data->delete();
            return Common::apiResponse(true, 'deleted successfully',  $data);
        } catch (Exception $exception) {

            return Common::apiResponse(0, $exception->getMessage(), null, 400);
        }
    }

    public function showGift($id)
    {
        try {
            $data =  PkReward::with('ware', 'vip')->findOrFail($id);
            return Common::apiResponse(true, 'done',  $data);
        } catch (Exception $exception) {

            return Common::apiResponse(0, $exception->getMessage(), null, 400);
        }
    }
}
