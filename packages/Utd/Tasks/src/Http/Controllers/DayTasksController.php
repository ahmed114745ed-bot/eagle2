<?php

namespace Utd\Tasks\Http\Controllers;

use App\Http\Controllers\Controller;
use Utd\Tasks\Services\DayTasksService;

class DayTasksController extends Controller
{
    protected $dayTasksService;

    public function __construct(DayTasksService $dayTasksService)
    {
        $this->dayTasksService = $dayTasksService;
    }

    public function getDayTasks($dayId)
    {
        $userId = auth()->id();
        $response = $this->dayTasksService->getDayTasks($userId, $dayId);
        return $response;
    }
}
