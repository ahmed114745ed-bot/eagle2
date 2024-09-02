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

    public function getByUserId($userId)
    {
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

    public function getByGroupUserId($userId, $startDate, $endDate)
    {
        return $this->model->query()
            ->where('uid', $userId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('uid', \DB::raw('DATE(created_at)'))
            ->havingRaw('SUM(hours) > 1')
            ->selectRaw('uid, SUM(hours) AS hnum, COUNT(DISTINCT DATE(created_at)) as days');
    }

    public function sumDays($userId, $startDate, $endDate, $date)
    {
        return $this->getByGroupUserId($userId, $startDate, $endDate)->whereDate('created_at', $date)->sum("days");
    }

    public function getByCreatedAt($userId, $startDate, $endDate)
    {
        return $this->model->query()->where('uid', $userId)->whereBetween('created_at', [$startDate, $endDate]);
    }

    public function SumHours($userId, $startDate, $endDate, $date)
    {
        return $this->getByCreatedAt($userId, $startDate, $endDate)->whereDate('created_at', $date)->sum("hours");
    }

    public function totalUsersHoursDays($userIds, $type)
    {
        $builder = $this->model->query()
            ->whereIn('uid', $userIds)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year);
        if ($type == 'days') {
            return      $builder->groupBy('uid')
                ->selectRaw('uid, SUM(hours) AS hnum, COUNT(DISTINCT DATE(created_at)) as days')
                ->havingRaw('SUM(hours) >= 1')->sum('days') ?? 0;
        } else {
            return $builder->sum('hours') ?? 0;
        }
    }

    public function getByDaily($userId, $month, $year)
    {
        return $this->model->query()
            ->selectRaw('sum(hours) as hours, max(created_at) as date')
            ->where('uid', $userId)
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->groupBy(\DB::raw('date(created_at)'))
            ->limit(31)
            ->get();
    }

    public function sumUserHoursByDate($userId,$date)
    {
        return $this->model->query()->where('uid', $userId)->whereDate('created_at', $date) ->sum('hours');
    }
}
