<?php

namespace Utd\Agency\Http\Resources;

use Utd\Agency\Facades\AgencyHelper;
use Illuminate\Http\Resources\Json\JsonResource;

class MyDataForAgencyNewResource extends JsonResource
{
    public $type;

    public function __construct($resource, $type = 'default')
    {
        parent::__construct($resource);
        $this->type = $type;
    }

    public static function collection($resources, $type = 'default')
    {
        return $resources->map(function ($resource) use ($type) {
            return new static($resource, $type);
        });
    }

    public function toArray($request)
    {
        $data = [
            'id' => @$this->user->id,
            'uuid' => @$this->user->uuid,
            'diamonds' => @$this->user->monthly_diamond_received ?: 0,
            'phone' => @$this->user->phone ?? '',
            'country' => $this->user->country ?? null,
            'name' => @$this->user->name ?: '',
            'vip'=>@AgencyHelper::ovip_center ($this->user->id),
            'level'=>AgencyHelper::level_center_min ($this->user->id),
            'profile' => new ProfileForAgencyResource(@$this->user->profile),
            'status' => $this->status,
            'type' => $this->type,
        ];
        
        if ($this->status !=0){
            $operator_name='';
            if ($this->change_status_type == "app") {
                $operator_name = $this->userOperator?->name;
            }else{
                $operator_name = $this->admin?->name;
            }
            $data['operator'] = $operator_name;
            $data['date'] = $this->updated_at;
        }

        return $data;
    }
}
