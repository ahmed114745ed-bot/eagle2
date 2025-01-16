<?php

namespace Modules\Achievement\Http\Repositories;

use App\Tik\Repositories\AbstractRepository;
use Modules\Achievement\Entities\GiftAchievement;


class GiftAchievementRepository extends AbstractRepository
{
    public function __construct()
    {
        parent::__construct(new GiftAchievement());
    }

    public function all($achievementId, $perPage, $Page)
    {
        return $this->model->where('achievement_id', $achievementId)->with('user', 'gift', 'Achievement')->paginate($perPage, ['*'], 'page', $Page);
    }
}
