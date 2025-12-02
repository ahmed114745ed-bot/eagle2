<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CountrySupportersResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => @$this->id ?? 0,
            'name' => __("countries.{$this->e_name}"),
            'e_name' => __("countries.{$this->e_name}"),
//            'name' => (app()->getLocale() == 'ar' ? (@$this->name ?: '') : (@$this?->e_name ?? '')),
            'flag' => @$this->flag ?: '',
            'lang' => @$this->language ?: '',
            'phone_code' => @$this->phone_code ?: '',
            'iso' => @$this->iso ?: '',
            'show_url' => route('countries.preview', $this->id),
            'total_rooms' => $this->whenHas('total_rooms'),
            'supporters' => $this->whenLoaded('supporters', function () {
                return $this->supporters->map(function ($supporter) {
                    return [
                        'id'     => $supporter->sender?->id ?? 0,
                        'uuid'     => $supporter->sender?->uuid ?? 0,
                        'name'   => $supporter->sender?->name ?? '',
                        'avatar' => $supporter->sender?->profile?->avatar ?? '',
                        'total'  => (int) $supporter->total_sent,
                    ];
                });
            }),
        ];
    }
}
