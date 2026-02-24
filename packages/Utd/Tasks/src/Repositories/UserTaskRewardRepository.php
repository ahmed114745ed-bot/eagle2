<?php

namespace Utd\Tasks\Repositories;

use App\Tik\Repositories\AbstractRepository;
use Utd\Tasks\Entities\UserTaskReward;

class UserTaskRewardRepository extends AbstractRepository
{
    public function __construct(UserTaskReward $model)
    {
        parent::__construct($model);
    }
}
