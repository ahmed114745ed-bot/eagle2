<?php

namespace App\Traits;

use App\Support\PackageHelper;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Utd\Chat\Entities\ChatMessage;
use Utd\Chat\Entities\ChatRoom;

trait ChatUserTrait
{
    public function chats()
    {
        return PackageHelper::checkRelation($this, 'chat', 'belongsToMany') ??
            $this->belongsToMany(ChatRoom::class, 'pin_to_tops');
    }

    public function chatMessages(): HasMany
    {
        return PackageHelper::checkRelation($this, 'chat', 'hasMany') ??
            $this->hasMany(ChatMessage::class);
    }

    public function chatRoomsAsUser(): HasMany
    {
        return PackageHelper::checkRelation($this, 'chat', 'hasMany') ??
            $this->hasMany(ChatRoom::class, 'user_id');
    }

    public function chatRoomsAsUser2(): HasMany
    {
        return PackageHelper::checkRelation($this, 'chat', 'hasMany') ??
            $this->hasMany(ChatRoom::class, 'user_id2');
    }
}
