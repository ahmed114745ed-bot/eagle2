<?php

namespace App\Enums;

enum SuperAdminNotificationLink: string
{  case BANNER_APPROVED = 'banner_approved';

    public function url(array $data = []): string
    {
        return match($this) {
            self::BANNER_APPROVED => route('superadmin.home-carousel.index',['itemNotification' => $data['item_id'] ]),
   
        };
    }
}



