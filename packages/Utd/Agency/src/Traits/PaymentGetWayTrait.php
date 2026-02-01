<?php

namespace Utd\Agency\Traits;

trait PaymentGetWayTrait
{
    use ConfigurableModelsTrait;

    public function paymentGateways()
    {
        return $this->belongsToMany(
            $this->getModelClass('payment_gateway'),
            'user_payment_gateways',
            'user_id',
            'payment_gateway_id'
        )->withTimestamps();
    }

    public function AgencypaymentGateways()
    {
        return $this->belongsToMany(
            $this->getModelClass('payment_gateway'),
            'user_payment_gateways',
            'agency_id',
            'payment_gateway_id'
        )->withTimestamps();
    }
}
