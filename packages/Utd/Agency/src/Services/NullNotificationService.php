<?php

namespace Utd\Agency\Services;

use Utd\Agency\Contracts\NotificationServiceInterface;

class NullNotificationService implements NotificationServiceInterface
{
    public function acceptRequestAgency($user)
    {
        return null;
    }
    
    public function refuseRequestAgency($user)
    {
        return null;
    }
    
    public function charges($user, $title, $body, $data = [])
    {
        return null;
    }
}
