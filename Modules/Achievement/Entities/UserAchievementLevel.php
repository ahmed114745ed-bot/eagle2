<?php

namespace Modules\Achievement\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Modules\Achievement\Entities\Achievement;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Achievement\Http\Services\AchievementLevelsService;

class UserAchievementLevel extends Model
{
    use HasFactory;

    protected $fillable = ["id","achievement_level_id","user_id","gift_achievement_id","unique_value","end_at","is_enable","achievement_id","custom_image","picked"];

    protected $guarded = [];


    public function achievementLevel(): BelongsTo
    {
        return $this->belongsTo(AchievementLevel::class, 'achievement_level_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function achievement(): BelongsTo
    {
        return $this->belongsTo(Achievement::class, 'achievement_id');
    }

    // public function achievement()
    // {
    //     return $this->belongsTo(Achievement::class, 'achievement_id');
    // }

    public function scopeWithAllData(Builder $query): Builder
    {
        return $query->with([
            'achievementLevel' => function ($query) {
                $query->select(['id', 'valid_image', 'target']);
            },
            'Achievement'
        ]);
    }
}
