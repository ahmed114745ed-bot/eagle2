<?php

namespace Utd\Pk\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Utd\Room\Entities\Room;

class Pk extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function getT1PerAttribute(): string
    {
        if (($this->t1_score + $this->t2_score) > 0) {
            $res = $this->t1_score / ($this->t1_score + $this->t2_score);
        } else {
            $res = 0.5;
        }

        return number_format($res, 2);
    }

    public function getT2PerAttribute(): string
    {
        if (($this->t1_score + $this->t2_score) > 0) {
            $res = $this->t2_score / ($this->t1_score + $this->t2_score);
        } else {
            $res = 0.5;
        }

        return number_format($res, 2);
    }

    public function team1Boss(): BelongsTo
    {
        return $this->belongsTo(User::class, 'team_1_boss')->with('profile');
    }

    public function team2Boss(): BelongsTo
    {
        return $this->belongsTo(User::class, 'team_2_boss')->with('profile');
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class, 'room_id');
    }
}
