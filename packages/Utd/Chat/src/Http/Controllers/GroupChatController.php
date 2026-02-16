<?php

namespace Utd\Chat\Http\Controllers;

use App\Helpers\Common;
use App\Http\Controllers\Controller;
use App\Jobs\SendNotificationsToAllUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Modules\Public\Http\Services\UpgradeLevelServices;
use Throwable;
use Utd\Chat\Events\GroupChat as GroupChatEvent;
use Utd\Chat\Http\Resources\GroupChatResource;
use Utd\Chat\Http\Services\GroupChatService;

class GroupChatController extends Controller
{
    protected GroupChatService $groupChatService;

    public function __construct(GroupChatService $groupChatService)
    {
        $this->groupChatService = $groupChatService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $costGroupChat = Common::getConfig('group_chat');
        $user = $request->user();
        $data = $this->groupChatService->index();

        // Update user unread message counter
        if ($user) {
            $user->update(['unread_counter_message' => 0]);
        }

        return response()->json([
            'price_message' => $costGroupChat,
            'data' => GroupChatResource::collection($data),
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'sometimes|image|mimes:jpeg,png,gif,bmp,tiff,webp',
            'image_url' => 'sometimes|string|max:255',
            'text' => 'required',
            'message_id' => 'nullable|integer|exists:group_chat,id',
        ]);

        if ($validator->fails()) {
            return Common::apiResponse(0, implode(',', $validator->errors()->all()), null, 422);
        }

        $costGroupChat = Common::getConfig('group_chat') ?? 11;
        if (! $costGroupChat) {
            return Common::apiResponse(0, 'not found params (group_chat) in config dashboard', null, 404);
        }

        $user = $request->user();

        if ($user->di < $costGroupChat) {
            return Common::apiResponse(0, 'Insufficient balance, please go to recharge!', null, 407);
        }

        $groupChat = $this->groupChatService->create($user, $request, $costGroupChat);

        (new UpgradeLevelServices())->sendWorldChat($user);

        // Pusher notification
        $groupChatResource = new GroupChatResource($groupChat);
        $resourceData = $groupChatResource->toArray($request);

        try {
            event(new GroupChatEvent($resourceData));
        } catch (Throwable $th) {
            Log::error('GroupChatController: Failed to fire GroupChat event', [
                'error' => $th->getMessage(),
                'user_id' => $user->id,
            ]);
        }

        try {
            switch ($request->message_type) {
                case 'reel':
                case 'share_room':
                case 'room':
                    if (class_exists(\App\Jobs\SendShareGroupChatNotificationJob::class)) {
                        dispatchJobToQueue(new \App\Jobs\SendShareGroupChatNotificationJob($user, $request->text, $resourceData), queueName: 'heavyProcessing');
                    }
                    break;

                default:
                    dispatchJobToQueue(new SendNotificationsToAllUsers($user, $request->text, $resourceData), queueName: 'heavyProcessing');
                    break;
            }
        } catch (Throwable $th) {
            Log::error('GroupChatController: Failed to dispatch notification job', [
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString(),
                'user_id' => $user->id,
                'message_type' => $request->message_type,
            ]);
        }

        return Common::apiResponse(1, 'created done', $resourceData, 201);
    }
}
