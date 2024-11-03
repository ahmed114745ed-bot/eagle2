<?php
namespace App\Traits;

use App\Models\Follow;
use App\Models\Room;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

Trait FollowTrait{

    public function friends_ids()
    {
        return $this->friends()->pluck('users.id');
    }

    public function following(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'follows', 'user_id', 'followed_user_id')
            ->withPivot('status', 'created_at')
            ->withTimestamps()
            ->orderBy('follows.created_at', 'desc');
    }

    // Define the users that are following this user
    public function followerss(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'follows', 'followed_user_id', 'user_id')
            ->withPivot('status', 'created_at')
            ->withTimestamps()
            ->orderBy('follows.created_at', 'desc');
    }

    // Define mutual followers as friends
    public function friends(): BelongsToMany
    {
        return $this->following()
            ->wherePivot('status', 1) // Active status for mutual relationships
            ->whereHas('followerss', function($query) {
                $query->where('user_id', $this->id);
            })
            ->orderBy('follows.created_at', 'desc');
    }


    // Assume we have a relationship to check if the user is being followed
    public function followedByAuthUser()
    {
        return $this->hasOne(Follow::class, 'followed_user_id', 'id')
            ->where('user_id', auth()->id());
    }

    // Accessor for is_follow property
    public function getIsFollowAttribute()
    {
        // Check if the authenticated user follows this user
        return $this->followedByAuthUser()->exists();
    }


    public function numberOfFans(){
        return $this->followers()->count();
    }

    public function numberOfFollowings(){
        return $this->following()->count();
    }

    public function numberOfFriends(){
        return $this->friends()->count();
    }

}
