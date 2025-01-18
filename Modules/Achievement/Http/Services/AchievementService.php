<?php

namespace Modules\Achievement\Http\Services;

use App\Models\User;
use App\Helpers\Common;
use App\Tik\Repositories\GiftRepository;
use Modules\Achievement\Enums\TargetType;
use Modules\Achievement\Entities\Achievement;
use Modules\Achievement\Http\Repositories\AchievementRepository;
use Modules\Achievement\Http\Repositories\GiftAchievementRepository;
use Modules\Achievement\Http\Repositories\AchievementLevelRepository;
use Modules\Achievement\Http\Repositories\UserAchievementLevelRepository;

class AchievementService
{
    public function __construct(
        private readonly AchievementRepository $achievementRepository,
        private readonly AchievementLevelRepository $achievementLevelRepository,
        private readonly GiftAchievementRepository $giftAchievementRepository,
        private readonly GiftRepository $giftRepository,
        private readonly UserAchievementLevelRepository $userAchievementLevelRepository,

    ) {}

    public function show(User $user, int $page = 1)
    {
        $userId = $user->id;
        return Achievement::query()
            /*->withExists(['userAchievement' => function($query) use($userId){
                              $query->where('user_id', $userId)->where('is_achieve', true);
                          }])*/
            ->get();
    }

    public function all()
    {
        return $this->achievementRepository->all();
    }

    public function allAchievementLevel($achievementId, $perPage, $Page)
    {
        return $this->achievementLevelRepository->all($achievementId, $perPage, $Page);
    }

    public function createAchievementLevel($request)
    {
        if ($request->hasFile('valid_image')) {
            $validImage = Common::upload('images', $request->file('valid_image'));
        }
        if ($request->hasFile('invalid_image')) {
            $invalidImage = Common::upload('images', $request->file('invalid_image'));
        }
        $data = [
            'invalid_image' => $invalidImage,
            'valid_image' => $validImage,
            'achievement_id' => $request->achievement_id,
            'target' => $request->target,
            'target_type' => $request->target_type,
            'ar_description' => $request->ar_description,
            'en_description' => $request->en_description
        ];
        $this->achievementLevelRepository->create($data);
        return true;
    }

    public function updateAchievementLevel($id, $request)
    {

        $data = [
            'achievement_id' => $request->achievement_id,
            'target' => $request->target,
            'target_type' => $request->target_type,
            'ar_description' => $request->ar_description,
            'en_description' => $request->en_description
        ];
        if ($request->hasFile('valid_image')) {
            $data['valid_image'] = Common::upload('images', $request->file('valid_image'));
        }
        if ($request->hasFile('invalid_image')) {
            $data['invalid_image'] = Common::upload('images', $request->file('invalid_image'));
        }
        $this->achievementLevelRepository->update($data, $id);
        return true;
    }

    public function deleteAchievementLevel($id)
    {
        $data = $this->achievementLevelRepository->findOrFail($id);
        $data->delete();
        return true;
    }

    public function showAchievementLevel($id)
    {
        return $this->achievementLevelRepository->findOrFail($id, ['achievements']);
    }

    public function achievementTargetType()
    {
        return TargetType::getTranslatedOptions();
    }

    public function allAchievementGift($achievementId, $perPage, $Page)
    {
        return $this->giftAchievementRepository->all($achievementId, $perPage, $Page);
    }

    public function achievementGift($request)
    {
        $this->giftAchievementRepository->create([$request->all()]);
        return true;
    }

    public function giftAchievement()
    {
        return $this->giftRepository->allAchievementGift();
    }

    public function userAchievementLevel($perPage, $Page, $uuid)
    {
        return $this->userAchievementLevelRepository->all($perPage, $Page, $uuid);
    }
}
