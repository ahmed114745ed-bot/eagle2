<?php

namespace Utd\Chat\Http\Controllers;

use App\Http\Controllers\Controller;
use Throwable;
use Utd\Chat\Http\Requests\ReactStoreRequest;
use Utd\Chat\Http\Services\ReactService;

class ChatReactsController extends Controller
{
    public function __construct(public ReactService $reactService) {}

    public function store(ReactStoreRequest $request)
    {

        try {

            $user = $request->user();
            $response = $this->reactService->handleReact(
                $user,
                $request->message_id,
                $request->react
            );

            if ($response['status'] !== 200) {
                return response()->json(['status' => $response['status'], 'message' => $response['message']], 404);
            }

            return response()->json([
                'status' => $response['status'],
                'react' => $response['react'],
                'message' => $response['message'],
            ]);

        } catch (Throwable $e) {
            return response()->json(['status' => 500, 'error' => $e->getMessage()], 500);
        }
    }
}
