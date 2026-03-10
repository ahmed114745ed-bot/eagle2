<?php

namespace Utd\Reals\Http\Controllers;

use App\Helpers\Common;
use Utd\Reals\Services\RealsNotification;
use Exception;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Throwable;
use Utd\Reals\Http\Requests\StoreRealComment;
use Utd\Reals\Services\RealCommentsService;
use Utd\Reals\Services\RealsService;
use Utd\Reals\Transformers\RealCommentsResource;

class RealsUserCommentController extends Controller
{
    public $realCommentsService;

    public $realsService;

    public function __construct(RealsService $realsService, RealCommentsService $realCommentsService)
    {
        $this->realsService = $realsService;
        $this->realCommentsService = $realCommentsService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index($realId)
    {

        try {
            $real = $this->realsService->findOrFail($realId);
        } catch (Throwable $th) {
            $real = null;
        }
        if ($real === null) {
            return Common::apiResponse(0, 'real not founded', [], 402);
        }

        $paginateComments = $this->realCommentsService->showComments($real);
        $data = RealCommentsResource::collection($paginateComments);

        return Common::apiResponse(1, 'successful', $data, 200);

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store($realId, StoreRealComment $request)
    {
        $user = Auth::user();
        try {
            $real = $this->realsService->findOrFail($realId);
        } catch (Exception $e) {
            $real = null;
        }
        if ($real === null) {
            return Common::apiResponse(0, 'real not founded', [], 402);
        }
        $this->realCommentsService->add($request->validated(), $real, $user);

        (new RealsNotification())->CommentReal($real, $user);

        return Common::apiResponse(1, 'success', [], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($realId, $id)
    {
        try {
            $real = $this->realsService->findOrFail($realId);
        } catch (Exception $e) {
            $real = null;
        }
        if ($real === null) {
            return Common::apiResponse(0, 'real not founded', [], 402);
        }
        $this->realCommentsService->delete($id, $real);
    }
}
