<?php

namespace Utd\Chat\Entities;

use App\Models\User;
use App\Traits\TimestampsWithTimezone;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GroupChat extends Model
{
    use TimestampsWithTimezone;

    protected $table = 'group_chat';

    protected $guarded = [];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(GroupChat::class, 'parent_id');
    }
}
