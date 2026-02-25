<?php

namespace Utd\Tasks\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Utd\Tasks\Services\TaskProgressService;

class TaskProgressController extends Controller
{
    protected $taskProgressService;

    public function __construct(TaskProgressService $taskProgressService)
    {
        $this->taskProgressService = $taskProgressService;
    }

    public function getDays(Request $request)
    {
        $userId = auth()->id();
        $response = $this->taskProgressService->getDays($userId);

        return $response;
    }
}
