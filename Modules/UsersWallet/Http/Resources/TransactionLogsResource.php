<?php

namespace Modules\UsersWallet\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;


class TransactionLogsResource extends JsonResource
{
     /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id'            => $this->id,
            'user_id'       => $this->user_id,
            'amount'        => $this->amount,
            'operation'     => $this->operation,
            'type'          => $this->type,
            'before_amount' => $this->before_amount,
            'after_amount'  => $this->after_amount,
            'created_at'    => $this->created_at ? $this->created_at->format('Y-m-d H:i:s') : null,
        ];
    }
}
