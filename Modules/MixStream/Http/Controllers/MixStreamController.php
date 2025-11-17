<?php

namespace Modules\MixStream\Http\Controllers;

use App\Helpers\Common;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;
use Modules\MixStream\Services\MixStreamService;
use Modules\MixStream\Transformers\MixStreamResource;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class MixStreamController extends Controller
{
    public function __construct(private readonly MixStreamService $mixStreamService)
    {
    }

    public function index(): JsonResponse
    {
        $result = $this->mixStreamService->index();

        return Common::apiResponse(true, '', MixStreamResource::collection($result));
    }

    /**
     * @throws \Exception
     */
    public function store(): JsonResponse
    {
        $result = $this->mixStreamService->store();

        return Common::apiResponse(true, '', MixStreamResource::make($result), ResponseAlias::HTTP_CREATED);
    }

    /**
     * @throws \Exception
     */
    public function join(Request $request): JsonResponse
    {
        $data = $request->validate([
            'mix_stream_id' => ['required', 'integer', Rule::exists('mix_streams', 'id')],
        ]);

        $result = $this->mixStreamService->join($data);

        return Common::apiResponse(true, '', MixStreamResource::make($result));
    }

    /**
     * @throws \Exception
     */
    public function leave(Request $request): JsonResponse
    {
        $data = $request->validate([
            'mix_stream_id' => ['required', 'integer', Rule::exists('mix_streams', 'id')],
        ]);

        $result = $this->mixStreamService->leave($data);

        return Common::apiResponse(true, '', MixStreamResource::make($result));
    }

    /**
     * @throws \Exception
     */
    public function sendInvitation(Request $request): JsonResponse
    {
        $data = $request->validate([
            'mix_stream_id' => ['required', 'integer', Rule::exists('mix_streams', 'id')],
            'invitee_user_id' => ['required', 'integer', Rule::exists('users', 'id')],
        ]);

        $this->mixStreamService->sendInvitation($data);

        return Common::apiResponse(true, __('sent successfully'));
    }

    /**
     * @throws \Exception
     */
    public function respondInvitation(Request $request): JsonResponse
    {
        $data = $request->validate([
            'mix_stream_id' => ['required', 'integer', Rule::exists('mix_streams', 'id')],
            'status' => ['required', 'string', Rule::in(['accept', 'reject'])],
        ]);

        $result = $this->mixStreamService->respondInvitation($data);

        return Common::apiResponse(true, '', MixStreamResource::make($result));
    }
}
