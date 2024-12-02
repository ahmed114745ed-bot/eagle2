<?php

namespace Modules\Moment\Http\Controllers;

use DB;
use App\Models\User;
use App\Models\Follow;
use App\Helpers\Common;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Moment\Entities\Moment;
use Illuminate\Support\Facades\Auth;
use Modules\Moment\Entities\MomentLikes;
use Modules\Moment\Entities\ReportMoment;
use Illuminate\Contracts\Support\Renderable;
use Modules\Reals\Http\Services\MomentService;
use Modules\Moment\Transformers\MomentResource;
use Modules\Public\Http\Services\UpgradeLevelServices;

class MomentController extends Controller
{


    public function index(Request $request)
    {

        $type   = $request->type;
        $userId = Auth::id();
        $user=$request->user();

        switch ($type) {
            case 1:
                $user_id = $request->user_id;
                if (!isset($user_id)) {
                    $user_id = Auth::id();
                }
                $data = Moment::where('user_id', $user_id)->likeExists($userId)->with('user')->withCount(['likes','comments',])->with(['gifts' => function ($query) {
                    $query ->select( DB::raw('sum(moment_user_gifts.num) as gifts_count'))
                        ->groupBy('moment_user_gifts.moment_id', 'moment_user_gifts.gift_id');
                }])->orderByDesc('id')->paginate(10);

                return Common::apiResponse(1, '', MomentResource::collection($data), 200);
            case 2: // moment  u like it
                $user_id = Auth::id();

                $likedMoments = MomentLikes::with([
                    'moment.user', 'moment' => function ($query) use ($userId) {
                        $query->likeExists($userId)->withCount(['likes', 'comments',])->with(["user",'gifts' => function ($query) {
                            $query->select(DB::raw('sum(moment_user_gifts.num) as gifts_count'))
                            ->groupBy('moment_user_gifts.moment_id', 'moment_user_gifts.gift_id');
                        }]);
                    }
                ])->where('user_id', $user_id)
                    ->orderByDesc('id')
                    ->paginate(10);
                if ($likedMoments->isEmpty()) {
                    return Common::apiResponse(1, 'No liked moments found', [], 200);
                }
                $likedMoments = $likedMoments->pluck('moment')->flatten();
                $likedMoments = $likedMoments->shuffle();

                return Common::apiResponse(1, '', MomentResource::collection($likedMoments), 200);
                // return $likedMoments;
                //  $data = MomentResource::collection($likedMoments->pluck('moment'));
                /* $data = TransformersMomentfollwedResource::collection($likedMoments);
                return Common::apiResponse(1, '',$data,200);*/
            case 3: // moment u follwed
              $user = auth()->user(); 
             $moments =    Moment::whereHas('user', function ($query) use ($user) {
                $query->whereHas('followersMoment', function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                });
            })->likeExists($userId)->with('user')->withCount(['likes','comments',])->with(['gifts' => function ($query) {
                $query ->select( DB::raw('sum(moment_user_gifts.num) as gifts_count'))
                    ->groupBy('moment_user_gifts.moment_id', 'moment_user_gifts.gift_id');
            }])->inRandomOrder($user->moment_type)->paginate(10);
   

            // $follwedMoments = Follow::where('user_id', $user->id)
            // ->whereHas('moments')
            // ->with([
            //     'moments.user',
            //     'moments' => function ($query) use ($user) {
            //         $query ->withCount(['likes', 'comments'])
            //             ->likeExists($user->id)
            //             ->with(["user",'gifts' => function ($query) {
            //                 $query->select(DB::raw('sum(moment_user_gifts.num) as gifts_count'))
            //                     ->groupBy('moment_user_gifts.moment_id', 'moment_user_gifts.gift_id');
            //             }])
            //             ->inRandomOrder($user->moment_type); // Apply random order here
            //     }
            // ])
            //         // ->orderByDesc('id')
            //         ->paginate(10);
  
                if ($moments->isEmpty()) {
                    return Common::apiResponse(1, 'No following moments found', [], 200);
                }

                // $follwedMoments = $follwedMoments->pluck('moments')->flatten();
                // $follwedMoments = $follwedMoments->shuffle();
              
               
                return Common::apiResponse(1, '', MomentResource::collection($moments), 200);

            case 4:
                $user=$request->user();
                // $data = Moment::likeExists($userId)->withCount(['likes','comments',])->with(['user','gifts' => function ($query) {
                //     $query ->select( DB::raw('sum(moment_user_gifts.num) as gifts_count'))
                //            ->groupBy('moment_user_gifts.moment_id', 'moment_user_gifts.gift_id');
                // }])->orderByDesc('id')->paginate(10);
                $user_id        = Auth::id();
                $data = Moment::withCount(['likes', 'comments'])
                ->with(['user', 'gifts' => function ($query) {
                    $query->select(DB::raw('sum(moment_user_gifts.num) as gifts_count'))
                        ->groupBy('moment_user_gifts.moment_id', 'moment_user_gifts.gift_id');
                }])
                ->orderByRaw("CASE WHEN (SELECT COUNT(*) FROM moment_user_likes WHERE moment_user_likes.moment_id = moment.id AND moment_user_likes.user_id = $user_id) > 0 THEN 1 ELSE 0 END ASC")
                ->inRandomOrder($user->moment_type)
                ->paginate(10);
                return Common::apiResponse(1, '', MomentResource::collection($data), 200);
            default:
                break;
        }


        return Common::apiResponse(1, 'please select valid type', '', 200);

    }

    /**
     * Show the form for creating a new resource.
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $user = $request->user();
        $contacts = @$request->contacts ?? '';
        $img_path = '';
        $user_id  = Auth::id();


        if ($request->hasFile('img')) {
            $img_path = Common::upload('moment', $request->file('img'));
        }
        if ($contacts == '' && $img_path == '') return Common::apiResponse(false, 'Not allowed Post empty content');

        $created = Moment::create([
                                      'user_id'     => $user_id,
                                      'description' => $contacts,
                                      'img'         => $img_path,
                                  ]);
        if (!$created) {
            return Common::apiResponse(0, 'try_again');
        }
        (new UpgradeLevelServices())->uploadMoment($user);
        return Common::apiResponse(1, 'success');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */


    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */


    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
      public function show(Request $request, $id)
     {
         $userId = Auth::id();

         $data = Moment::where('id', $id)->likeExists($userId)->with('user',)->withCount(['likes','comments',])->with(['gifts' => function ($query) {
             $query ->select( DB::raw('sum(moment_user_gifts.num) as gifts_count'))
                    ->groupBy('moment_user_gifts.moment_id', 'moment_user_gifts.gift_id');
         }])->first();
         if (!$data){
             return Common::apiResponse(0, __('Moment not founded'), 402);

         }
         return Common::apiResponse(1, '', new MomentResource($data), 200);
     }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    // public function update(Request $request, $id)
    // {
    //     //
    // }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        $moment = Moment::query()->find($id);
        if (!$moment) {
            return Common::apiResponse(1, 'Item Not Found');
        }
        $moment->delete();
        return Common::apiResponse(1, 'success Deleted');

    }

    public function destroy_dash($moment_id, $id)
    {

        $moment        = Moment::query()->find($moment_id);

        if (!$moment) {
            return redirect()->back();
        }
        ReportMoment::query()->where('moment_id', $moment_id)->delete();
        $moment->delete();
        // $moment_report->delete();
        return redirect()->back();

    }
}
