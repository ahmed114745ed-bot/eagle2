<?php

namespace Utd\Chat\Http\Services;

use App\Helpers\Common;
use Utd\Chat\Entities\GroupChat;
use Utd\Chat\Http\Repositories\GroupChatRepository;

class GroupChatService
{
    public function __construct(
        private readonly GroupChatRepository $groupChatRepository
    ) {}

    public function index(int $perPage = 10)
    {
        return $this->groupChatRepository->getWithPaginate($perPage);
    }

    public function create($user, $request, int $costGroupChat): GroupChat
    {
        // Decrement user coins
        $user->di -= $costGroupChat;
        $user->save();

        // Handle image upload
        $image = null;
        if ($request->hasFile('image')) {
            $image = Common::upload('group-chat', $request->file('image'));
        }
        if ($request->image_url) {
            $image = $request->image_url;
        }

        $data = [
            'text' => $request->text,
            'user_id' => $user->id,
            'image' => $image ?? '',
            'parent_id' => $request->message_id ?? $request->parent_id,
        ];

        $groupChatMessage = $this->groupChatRepository->create($data);

        // Count reel shares if applicable
        if ($request->message_type != null && $request->message_type == 'reel') {
            $this->countReel($request->text);
        }

        // Update user unread message counter
        $user->increment('unread_counter_message');

        return $groupChatMessage;
    }

    public function update(int $id, array $data): bool
    {
        return $this->groupChatRepository->update($data, $id);
    }

    public function delete(int $id): bool
    {
        return $this->groupChatRepository->delete($id);
    }

    public function find(int $id): ?GroupChat
    {
        return $this->groupChatRepository->find($id);
    }

    public function getFiltered(array $filters = [], int $perPage = 10)
    {
        return $this->groupChatRepository->getFiltered($filters, $perPage);
    }

    protected function countReel(string $data): void
    {
        $parts = explode(':', str_replace("\n", ':', $data));
        $reelId = $parts[4] ?? null;

        if (! $reelId) {
            return;
        }

        // Check if Reals package is installed
        if (! class_exists(\Utd\Reals\Entities\Real::class)) {
            return;
        }

        $reel = \Utd\Reals\Entities\Real::find($reelId);
        if ($reel) {
            $reel->share_num += 1;
            $reel->save();
        }
    }
}
