<?php

namespace Modules\CP\Http\Resources;

use App\Helpers\Common;
use Modules\CP\Entities\Cp;
use Illuminate\Http\Resources\Json\JsonResource;

class CpsUserResource extends JsonResource
{
    protected $relationType;


    public function __construct($resource, $relationType = null)
    {
        // Ensure you call the parent constructor
        parent::__construct($resource);

        // Store the additional parameter
        $this->relationType = $relationType;
    }


    public function toArray($request)
    {
        $cp = Cp::where(function ($query) {
            $query->where('user_one_id', $this->id)
                ->orWhere('user_two_id', $this->id);
        })->where('cp_relation_id', $this->relationType)->orderByDesc('di')->first();
        $total_received_level_img = Common::getImageTotalReceiverOrSender($this->total_received_level);
        $total_sender_level_img = Common::getImageTotalReceiverOrSender($this->total_sender_level);
        return [
            'id' => $this->id,
            'name' => $this->name,
            'uuid' => $this->uuid,
            'image' => $this->profile->avatar,
            'exp'           => $cp?->di ?? 0,
            'reciver_level_img'  => $total_received_level_img->img ?? '',
            'sender_level_img'  => $total_sender_level_img->img ?? '',
        ];
    }
}
