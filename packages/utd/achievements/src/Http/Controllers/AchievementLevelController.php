<?php

namespace Utd\Achievements\Http\Controllers;

use Illuminate\Routing\Controller;
use App\Contracts\AchievementContract;
use Utd\Achievements\Transformers\UserAchievementLevelsResource;

class AchievementLevelController extends Controller
{
    protected AchievementContract $achievementService;

    public function __construct(AchievementContract $achievementService)
    {
        $this->achievementService = $achievementService;
    }

    /**
     * Show user's achievement levels
     */
    public function show($id)
    {
        $userModel = config('achievements.models.user');
        
        try {
            $user = $userModel::withoutAppends()->findOrFail($id);
        } catch (\Exception $e) {
            return $this->apiResponse(false, 'No user Founded');
        }

        $userAchievementLevels = $this->achievementService->getUserAchievement($user);

        return $this->apiResponse(true, 'success', UserAchievementLevelsResource::collection($userAchievementLevels));
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
