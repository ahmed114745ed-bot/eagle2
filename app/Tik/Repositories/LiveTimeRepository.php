<?php

namespace App\Tik\Repositories;

use Carbon\Carbon;
use App\Models\LiveTime;



class LiveTimeRepository extends AbstractRepository
{
    public function __construct()
    {
        parent::__construct(new LiveTime());
    }

    public function totalHoursUser($userId)
    {
        return $this->model->query()->where('uid', $userId)->whereYear('created_at', '=', Carbon::now()->year)->whereMonth('created_at', '=', Carbon::now()->month)->whereDay('created_at', '=', Carbon::now()->day)->sum('hours');
    }

    public function getByUserId($userId){
        return $this->model->query()->where('uid', $userId)
        ->where('end_time', null)
        ->whereDate('created_at', today())
        ->orderByDesc('id')
        ->first();
    }
    
    public function totalHoursByMonth($userId)
    {
        return $this->model->LiveTime::query()->where('uid', $userId)
        ->whereYear('created_at', '=', Carbon::now()->year)
        ->whereMonth('created_at', '=', Carbon::now()->month)->sum('hours');
    }

    public function totalHours($userId)
    {
        return $this->model->query()->where('uid', $userId)
        ->sum('hours');
    }
}
