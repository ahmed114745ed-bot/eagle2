<?php

namespace Utd\Tasks\Repositories;

use App\Tik\Repositories\AbstractRepository;
use Utd\Tasks\Entities\TaskReward;

class TaskRewardRepository extends AbstractRepository
{
    public function __construct(TaskReward $model)
    {
        parent::__construct($model);
    }
}
