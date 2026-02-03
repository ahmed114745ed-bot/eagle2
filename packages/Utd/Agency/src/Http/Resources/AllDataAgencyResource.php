<?php

namespace Utd\Agency\Http\Resources;

use Carbon\Carbon;
use Utd\Agency\Contracts\ExternalModuleInterface;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class AllDataAgencyResource extends JsonResource
{
    public function toArray($request)
    {
        $externalModule = app(ExternalModuleInterface::class);
        
        $userSalaryModel = $externalModule->get('models.user_salary');
        $targetModel = $externalModule->get('models.target');
        
        $target = $userSalaryModel::where('user_agency_id', $this->agency_id)->sum('agency_sallary');

        $minValue = $targetModel::where('usd', '<', $target)
            ->orderBy('usd', 'desc')
            ->first();

        $result = (@$minValue->agency_share / 100) * @$target;

        $authUser = Auth::user();
        $owner = @$authUser->ownAgency;
        $admin = @$authUser->agencyUserJob;
        $type = '';

         if ($this->type == 1) {
            $type = 'hosts ';
        } elseif ($this->type == 2) {
            $type = 'shipping';
        }

        $year = request('year') ?? Carbon::now()->year;
        $month = request('month') ?? Carbon::now()->month;

        $giftLog = $this->getTopGiftLogsByUserType(
            year: $year,
            month: $month,
            userType: 'receiver',
            limit: 5
        );

        $heroGiftLog = $this->getTopGiftLogsByUserType(
            year: $year,
            month: $month,
            userType: 'sender',
            limit: 5
        );

        return [
            'id' => $this->id ?: 0,
            'target' => $result ?: 0,
            'name' => $this->name ?: '',
            'notice' => $this->notice ?: '',
            'phone' => $this->phone ?: 0,
            'img' => $this->img ?: '',
            'agency_type' => $type,
            'num_of_hosts'      => $this->mempers->count(),
            'owner' => new MyDataForAgencyResource($this->owner) ?: [
                "id" => 0,
                "uuid" => '',
                "target_usd" => 0,
                'name' => '',
                "profile" => [
                    "image" => ''
                ]
            ],
            'mempers_count' => $this->mempers_count,
            'admin' => AdminsAgencyResource::collection(@$this->admins),
            'star' => ReceiverGiftLogResource::collection($giftLog),
            'heroes' => SenderGiftLogResource::collection($heroGiftLog),
        ];
    }
}
