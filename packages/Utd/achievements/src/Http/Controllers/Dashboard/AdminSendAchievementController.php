<?php

namespace Utd\Achievements\Http\Controllers\Dashboard;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Utd\Achievements\Entities\UserAchievementLevel;
use Utd\Achievements\Support\AchievementHelper;
use Utd\Achievements\Transformers\UserAchievementLevelsResource;

class AdminSendAchievementController extends Controller
{
    public function index()
    {
        $data = UserAchievementLevel::with('achievementLevel', 'user', 'achievement')
            ->orderBy('id', 'desc')
            ->get();

        return UserAchievementLevelsResource::collection($data);
    }

    public function enableUserAchievement(Request $request, $id, $status)
    {
        $achievement = UserAchievementLevel::find($id);

        if ($achievement) {
            $achievement->is_enable = $status == 'true' ? 1 : 0;
            $achievement->update();
        }

        return response()->json(['status' => 200]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'enable' => 'required',
            'achievement_level_id' => 'nullable|exists:achievement_levels,id'
        ]);

        $img = $request->hasFile('img') 
            ? AchievementHelper::upload('achievements', $request->file('img')) 
            : null;

        $data = new UserAchievementLevel();
        $data->custom_image = $img;
        $data->is_enable = $request->enable;
        $data->user_id = $request->user_id;
        $data->achievement_level_id = $request->achievement_level_id;
        $data->save();

        return 200;
    }

    public function destroy(string $id)
    {
        $userAchievementLevel = UserAchievementLevel::find($id);

        if ($userAchievementLevel->custom_image) {
            AchievementHelper::delete($userAchievementLevel->custom_image);
        }

        $userAchievementLevel->delete();

        return 200;
    }
}
