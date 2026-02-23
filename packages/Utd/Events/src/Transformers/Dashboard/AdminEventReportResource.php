<?php

namespace Utd\Events\Transformers\Dashboard;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Utd\Pk\Http\Resources\AdminPKEventRewardsResource;

class AdminEventReportResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    function get_user($id) {
        $user = User::where('id',$id)->first();
        if($user)
        {
            return [
                'id'   =>$user->id ,
                'uuid'   =>$user->uuid ,
                'name' =>$user->name ?? '',
                'img'  =>$user->profile->avatar ?? null,
            ];
        }
        else{
            return null;
        }
    }

    public function toArray(Request $request): array
    {
        // return parent::toArray($request);

        return [
            'id' => $this->id,
            'user' =>$this->get_user($this->pk_winner_id),
            'reword' => new AdminPKEventRewardsResource($this->reward)
        ];
    }
}
