<?php

namespace Utd\Achievements\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Utd\Achievements\Entities\Achievement;
use Utd\Achievements\Entities\UserAchievementLevel;
use Utd\Achievements\Transformers\AchievementResource;
use Utd\Achievements\Transformers\AchievementDetailResource;
use Utd\Achievements\Transformers\AchievementOneLevelsResource;

class AchievementController extends Controller
{
    /**
     * Update user's selected achievements
     */
    public function achievementSelect(Request $request)
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

        return $this->apiResponse(1, 'Achievements updated successfully', []);
    }

    /**
     * Get all selected achievements for a user
     */
    public function getAllSelect($id = null)
    {
        $user = Auth::user();
        
        if (isset($id)) {
            $achievements = Achievement::whereHas("userAchievments", function ($q) use ($id) {
                $q->where("enable", 1)->where("picked", 1)->where("user_id", $id);
            })->where('id', $id)->with([
                'levels' => function ($query) {
                    $query->withCount([
                        'achievementUsers as enable' => function ($query) {
                            $query->where('user_id', auth()->id())
                                ->where('is_enable', true);
                        }
                    ]);
                },
            ])->get();

            return $this->apiResponse(1, 'successfully', AchievementOneLevelsResource::collection($achievements));
        }

        $achievements = Achievement::whereHas("userAchievments", function ($q) use ($user) {
            $q->where("enable", 1)->where("picked", 1)->where("user_id", $user->id);
        })->get();

        return $this->apiResponse(1, 'successfully', AchievementResource::collection($achievements));
    }

    /**
     * Get all achievements
     */
    public function getAll($id = null)
    {
        if (isset($id) && ($id != 4)) {
            $achievements = Achievement::where('id', $id)->with([
                'levels' => function ($query) {
                    $query->withCount([
                        'achievementUsers as enable' => function ($query) {
                            $query->where('user_id', auth()->id())
                                ->where('is_enable', true);
                        }
                    ]);
                },
            ])->get();

            return $this->apiResponse(1, 'successfully', AchievementOneLevelsResource::collection($achievements));
        }

        $user = Auth::user();
        $achievements = Achievement::whereHas("userAchievementLevel", function ($q) use ($user) {
            $q->where("is_enable", 1)->where("user_id", $user->id);
        })->get();

        return $this->apiResponse(1, 'successfully', AchievementResource::collection($achievements));
    }

    /**
     * Get achievement details
     */
    public function getDetails($id = null)
    {
        $user = Auth::user();
        $userId = $id ?? $user->id;

        $achievementsQuery = UserAchievementLevel::with("achievementLevel");
        $achievementsQuery->where("user_id", $userId);

        if (request('type')) {
            $achievementsQuery->where(function ($outerQuery) {
                $outerQuery->whereHas('achievementLevel', function ($query) {
                    $types = request('type') == 1 ? ['recharge_target', 'gift_target'] : ['room_target'];

                    $query->whereHas('achievement', function ($q) use ($types) {
                        $q->whereIn('type', $types);
                    })->orWhereDoesntHave('achievement');
                });
                if (request('type') == 1) {
                    $outerQuery->orWhereDoesntHave('achievementLevel');
                }
            });
        }
        
        $achievements = $achievementsQuery->get();
        return $this->apiResponse(1, 'successfully', AchievementDetailResource::collection($achievements));
    }

    /**
     * API response helper
     */
    protected function apiResponse($status, $message, $data = null)
    {
        return response()->json([
            'status' => $status,
            'message' => $message,
            'data' => $data,
        ]);
    }
}
