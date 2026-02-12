<?php

namespace Utd\Achievements\Repositories;

use App\Tik\Repositories\AbstractRepository;
use Utd\Achievements\Entities\Achievement;

class AchievementRepository extends AbstractRepository
{
    public function __construct()
    {
        parent::__construct(new Achievement());
    }

    public function all()
    {
        return $this->model->get();
    }
}
