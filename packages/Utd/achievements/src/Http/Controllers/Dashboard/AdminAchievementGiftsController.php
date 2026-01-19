<?php

namespace Utd\Achievements\Http\Controllers\Dashboard;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Utd\Achievements\Entities\GiftAchievement;
use Utd\Achievements\Transformers\GiftAchievementUser;

class AdminAchievementGiftsController extends Controller
{
    public function index()
    {
        $data = GiftAchievement::orderBy('id', 'desc')->get();
        return GiftAchievementUser::collection($data);
    }

    public function store(Request $request)
    {
        $request->validate([
            'gift_id'   => 'required|exists:gifts,id',
            'user_id'   => 'required|exists:users,id',
        ]);

        GiftAchievement::insert([
            'gift_id'            => $request->gift_id,
            'user_id'            => $request->user_id,
            'achievement_id'     => 3,
        ]);

        return response()->json(['status' => 200]);
    }

    public function show(string $id)
    {
        $data = GiftAchievement::find($id);
        return new GiftAchievementUser($data);
    }

    public function update(Request $request, string $id)
    {
        $giftAchievement = GiftAchievement::find($id);

        $request->validate([
            'gift_id'   => 'required|exists:gifts,id',
            'user_id'   => 'required|exists:users,id',
        ]);

        $giftAchievement->gift_id = $request->gift_id;
        $giftAchievement->user_id = $request->user_id;
        $giftAchievement->update();

        return response()->json(['status' => 200]);
    }

    public function destroy(string $id)
    {
        $giftAchievement = GiftAchievement::find($id);
        $giftAchievement->delete();

        return 200;
    }
}
