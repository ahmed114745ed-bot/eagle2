<?php

namespace Utd\Tasks\Repositories;

use App\Tik\Repositories\AbstractRepository;
use Utd\Tasks\Entities\Day;

class DayRepository extends AbstractRepository
{
    public function __construct(Day $model)
    {
        parent::__construct($model);
    }

    public function save($day)
    {
        $day->save();
    }

    public function getNextDay($dayId)
    {
        $currentDay = $this->findOrFail($dayId);
        if (! $currentDay) {
            return null;
        }

        return $this->model
            ->where('day_number', '>', $currentDay->day_number)
            ->orderBy('day_number', 'asc')
            ->first();
    }
}
