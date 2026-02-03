<?php

namespace Utd\Agency\Contracts;

interface ChargeServiceInterface
{
    /**
     * Send money to host
     */
    public function sendMoney($user, $userUuid, $count);
    
    /**
     * Charge dollar for owner
     */
    public function chargeDollarForOwner($user, $userUuid, $count);
    
    /**
     * Charge dollar for owner to agency
     */
    public function chargeDollarForOwner_to_agency($user, $userUuid, $count);
    
    /**
     * Charge to user or agency
     */
    public function chargeTo($from, $to, $coins, $isRoomTarget, $usd);
    
    /**
     * Charge to agency
     */
    public function chargeToAgency($from, $to, $coins, $isRoomTarget, $usd);
    
    /**
     * Charge from agency to another
     */
    public function chargeAgencyToAnother($from, $request);
    
    /**
     * Get charge user history
     */
    public function getChargeUserHistory($userId, $type, $byDate = null, $chargeType = null, $searchKey = null);
    
    /**
     * Get charge to user history
     */
    public function getChargeToUserHistory();
    
    /**
     * Get charge agency history
     */
    public function getChargeAgencyHistory();
    
    /**
     * Search user agency
     */
    public function userAgencySearch($request);
    
    /**
     * Get coin logs
     */
    public function getCoinLogs($userId, $searchKey = null);
}
