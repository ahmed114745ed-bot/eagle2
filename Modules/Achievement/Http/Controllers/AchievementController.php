<?php

namespace Modules\Achievement\Http\Controllers;

use App\Helpers\Common;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Achievement\Entities\Achievement;
use Modules\Achievement\Entities\UserAchievement;
use Modules\Achievement\Entities\UserAchievementLevel;
use Modules\Achievement\Http\Services\AchievementService;
use Modules\Achievement\Transformers\AchievementOneLevelsResource;
use Modules\Achievement\Transformers\AchievementDetailResource;
use Modules\Achievement\Transformers\AchievementResource;

class AchievementController extends Controller
{

    public function __construct(private AchievementService $achievementService) {

    }

    /**
     * Display a listing of the resource.
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $user = Auth::user();
        $data = $this->achievementService->show($user);
        return Common::apiResponse(1, 'successfully', $data);
    }

    public function achivement_select(Request $request)
    {
        $user = $request->user();

        DB::transaction(function () use ($user, $request) {
            UserAchievementLevel::where('user_id', $user->id)->update(['picked' => 0]);

            if (!empty($request->ids) && is_array($request->ids)) {
                UserAchievementLevel::where('user_id', $user->id)
                            ->whereIn('id', $request->ids)
                            ->update(['picked' => 1]);
            }
        });

        return Common::apiResponse(1, 'Achievements updated successfully', []);
    }

    public function store(Request $request)
    {
        //
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        return view('achievement::show');
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function get_all_select($id = null)
    {
        $user=Auth::user();
        if(isset($id)){
            $achievements = Achievement::whereHas("userAchievments",function($q) use ($id){
                $q->where("enable",1)->where("picked",1)->where("user_id",$id);
            })->where('id',$id)-> with([
                'levels' => function ($query) {
                    $query->withCount([
                        'achievementUsers as enable' => function ($query) {
                            $query->where('user_id', auth()->id())
                                  ->where('is_enable', true);
                        }
                    ]);
                },
            ])->get();


            return Common::apiResponse(1, 'successfully', AchievementOneLevelsResource::collection($achievements));
        }

        $achievements = Achievement::whereHas("userAchievments",function($q) use ($user){
            $q->where("enable",1)->where("picked",1)->where("user_id",$user->id);
        })->get();

        return Common::apiResponse(1, 'successfully', AchievementResource::collection($achievements));

    }


    public function get_all($id = null)
    {
        if(isset($id)){
            $achievements = Achievement::where('id',$id)-> with([
                'levels' => function ($query) {
                    $query->withCount([
                        'achievementUsers as enable' => function ($query) {
                            $query->where('user_id', auth()->id())
                                  ->where('is_enable', true);
                        }
                    ]);
                },
            ])->get();


            return Common::apiResponse(1, 'successfully', AchievementOneLevelsResource::collection($achievements));
        }
        $user=Auth::user();
        $achievements = Achievement::whereHas("userAchievementLevel",function($q) use ($user){
            $q->where("is_enable",1)->where("user_id",$user->id);
        })->get();
        return Common::apiResponse(1, 'successfully', AchievementResource::collection($achievements));

    }

    // public function get_details($id = null)
    // {
    //     $user=Auth::user();
    //     if(isset($id)){
    //         $achievements = Achievement::whereHas("userAchievments",function($q) use ($id){
    //             $q->where("user_id",$id);
    //         })-> with([
    //             'levels' => function ($query) {
    //                 $query->withCount([
    //                     'achievementUsers as enable' => function ($query) {
    //                         $query->where('user_id', auth()->id())
    //                               ->where('is_enable', true);
    //                     }
    //                 ]);
    //             },
    //         ])->get();


    //         return Common::apiResponse(1, 'successfully', AchievementOneLevelsResource::collection($achievements));
    //     }

    //     $achievements = Achievement::whereHas("userAchievments",function($q) use ($user){
    //         $q->where("user_id",$user->id);
    //     })-> get();
    //     return Common::apiResponse(1, 'successfully', AchievementDetailResource::collection($achievements));

    // }

    public function get_details($id = null)
    {
        $user = Auth::user();
        $userId = $id ?? $user->id;

        $achievementsQuery = UserAchievementLevel::with("achievementLevel");

        $achievementsQuery->where("user_id", $userId);

        if (request('type')) {
            $achievementsQuery->where(function($outerQuery) {
                $outerQuery->whereHas('achievementLevel', function($query) {
                    $types = request('type') == 1 ? ['recharge_target', 'gift_target'] : ['room_target'];

                    $query->whereHas('achievement', function($q) use ($types) {
                        $q->whereIn('type', $types);
                    })->orWhereDoesntHave('achievement');
                });
                if (request('type') == 1) {
                    $outerQuery->orWhereDoesntHave('achievementLevel');
                }
            });
        }
        $achievements = $achievementsQuery->get();
        return Common::apiResponse(1, 'successfully', AchievementDetailResource::collection($achievements));
    }


    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        //
    }
}
