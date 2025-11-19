<?php

namespace Modules\TaskStream\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;
use Modules\TaskStream\Http\Requests\PkSessionRequest;
use Modules\TaskStream\Services\PkSessionService;

class PkSessionController extends Controller
{
    public function __construct(private readonly PkSessionService $pkSessionService)
    {
    }

    public function start(PkSessionRequest $pkSessionRequest): JsonResponse
    {
        $result = $this->pkSessionService->start();

//        return Common::apiResponse(true, '', TaskStreamResource::collection($result));
    }
}
