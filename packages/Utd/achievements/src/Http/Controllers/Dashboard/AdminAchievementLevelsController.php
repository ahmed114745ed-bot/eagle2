<?php

namespace Utd\Achievements\Http\Controllers\Dashboard;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Utd\Achievements\Entities\AchievementLevel;
use Utd\Achievements\Support\AchievementHelper;

class AdminAchievementLevelsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $Type = $request->get('Type');
        $data = AchievementLevel::where('achievement_id', $Type)->get();
        return $data;
    }

    public function store(Request $request)
    {
        $request->validate([
            'en_description'   => 'required|max:255',
            'ar_description'   => 'required|max:255',
            'target'           => 'nullable|max:255',
            'achievement_id'   => 'required|max:255',
            'img1'             => 'required|image|mimes:jpeg,png,jpg',
            'img2'             => 'required|image|mimes:jpeg,png,jpg',
        ]);

        $img1_name = $request->hasFile('img1') 
            ? AchievementHelper::upload('achievements', $request->file('img1')) 
            : null;
        $img2_name = $request->hasFile('img2') 
            ? AchievementHelper::upload('achievements', $request->file('img2')) 
            : null;

        AchievementLevel::insert([
            'en_description'     => $request->en_description,
            'ar_description'     => $request->ar_description,
            'target'             => $request->target ?? null,
            'achievement_id'     => $request->achievement_id,
            'valid_image'        => $img1_name,
            'invalid_image'      => $img2_name,
            'target_type'        => $request->type_id,
        ]);

        return response()->json(['status' => 200]);
    }

    public function show(string $id)
    {
        return AchievementLevel::find($id);
    }

    public function update(Request $request, string $id)
    {
        $achievementLevel = AchievementLevel::find($id);

        $request->validate([
            'en_description'   => 'required|max:255',
            'ar_description'   => 'required|max:255',
            'target'           => 'nullable|max:255',
        ]);

        if ($request->hasFile('img1')) {
            AchievementHelper::delete($achievementLevel->valid_image);
            $achievementLevel->valid_image = AchievementHelper::upload('achievements', $request->file('img1'));
        }

        if ($request->hasFile('img2')) {
            AchievementHelper::delete($achievementLevel->invalid_image);
            $achievementLevel->invalid_image = AchievementHelper::upload('achievements', $request->file('img2'));
        }

        $achievementLevel->en_description = $request->en_description;
        $achievementLevel->ar_description = $request->ar_description;
        $achievementLevel->target = $request->target;
        $achievementLevel->target_type = $request->type_id;
        $achievementLevel->update();

        return response()->json(['status' => 200]);
    }

    public function destroy(string $id)
    {
        $achievementLevel = AchievementLevel::find($id);
        AchievementHelper::delete($achievementLevel->valid_image ?? null);
        AchievementHelper::delete($achievementLevel->invalid_image ?? null);
        $achievementLevel->delete();

        return 200;
    }
}
