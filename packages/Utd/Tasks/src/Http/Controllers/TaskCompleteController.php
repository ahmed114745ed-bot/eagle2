<?php

namespace Utd\Tasks\Http\Controllers;

use App\Helpers\Common;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Modules\DailyPrize\Http\Controllers\Api\DailyGiftController;
use Utd\Tasks\Services\TaskService;

class TaskCompleteController extends Controller
{
    protected $taskService;

    public function __construct(TaskService $taskService)
    {
        $this->taskService = $taskService;
    }

    public function collectTaskPoints($taskId, Request $request, DailyGiftController $dailyGiftController)
    {
        try {
            $userId = $request->user()->id;
            $result = $this->taskService->collectTaskPoints($taskId, $userId);

            return $result;
        } catch (Exception $e) {
            return Common::apiResponse(false, $e->getMessage(), null, 500);
        }
    }
}
