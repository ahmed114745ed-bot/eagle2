<?php

namespace Utd\Agency\Contracts;

interface NotificationServiceInterface
{
    /**
     * Send accept request agency notification
     */
    public function acceptRequestAgency($user);
    
    /**
     * Send refuse request agency notification
     */
    public function refuseRequestAgency($user);
    
    /**
     * Send charge notification
     */
    public function charges($user, $title, $body, $data = []);
}
