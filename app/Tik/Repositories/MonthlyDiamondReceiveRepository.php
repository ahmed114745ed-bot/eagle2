<?php

namespace App\Tik\Repositories;


use Utd\Achievements\Entities\MonthlyDiamondReceive;


class MonthlyDiamondReceiveRepository extends AbstractRepository
{

    public function __construct()
    {
        parent::__construct(new MonthlyDiamondReceive());
    }
}