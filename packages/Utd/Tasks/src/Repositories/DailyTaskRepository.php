<?php

namespace Utd\Tasks\Repositories;

use App\Tik\Repositories\AbstractRepository;
use Utd\Tasks\Entities\DailyTask;

class DailyTaskRepository extends AbstractRepository
{
    public function __construct(DailyTask $model)
    {
        parent::__construct($model);
    }
}
