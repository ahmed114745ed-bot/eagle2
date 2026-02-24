<?php

namespace Utd\Tasks\Repositories;

use App\Tik\Repositories\AbstractRepository;
use Utd\Tasks\Entities\UserDayProgress;

class UserDayProgressRepository extends AbstractRepository
{
    public function __construct(UserDayProgress $model)
    {
        parent::__construct($model);
    }

    public function save($userDayProgress)
    {
        $userDayProgress->save();
    }
}
