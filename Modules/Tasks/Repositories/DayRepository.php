<?php

namespace Modules\Tasks\Repositories;


use Modules\Tasks\Repositories\Contracts\DayRepositoryInterface;
use Modules\Tasks\Entities\Day;

class DayRepository implements DayRepositoryInterface
{
    public function findById($dayId)
    {
        return Day::find($dayId);
    }

    public function save($day)
    {
        $day->save();
    }
}