<?php

namespace Utd\TaskStream\Http\Controllers;

use App\Helpers\Common;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;
use Utd\TaskStream\Http\Requests\PkSessionRequest;
use Utd\TaskStream\Services\PkSessionService;
use Utd\TaskStream\Transformers\PkSessionResource;

class PkSessionController extends Controller
{
    public function __construct(private readonly PkSessionService $pkSessionService) {}

    /**
     * @throws Exception
     */
    public function start(PkSessionRequest $pkSessionRequest): JsonResponse
    {
        $result = $this->pkSessionService->start($pkSessionRequest->validated());

        return Common::apiResponse(true, '', PkSessionResource::make($result));
    }

    /**
     * @throws Exception
     */
    public function close(Request $request): JsonResponse
    {
        $data = $request->validate([
            'pk_id' => ['required', 'integer', Rule::exists('pk_sessions', 'id')],
        ]);

        $result = $this->pkSessionService->close($data);

        return Common::apiResponse(true, '', PkSessionResource::make($result));
    }
}
