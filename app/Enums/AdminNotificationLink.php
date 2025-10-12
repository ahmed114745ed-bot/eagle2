<?php

namespace App\Enums;

enum AdminNotificationLink: string
{  case BANNER_SHOW = 'banner_show';
    case USER_PROFILE = 'user_profile';
    case ORDER_DETAILS = 'order_details';

    public function url(array $data = []): string
    {
        return match($this) {
            self::BANNER_SHOW => route('admin.superadmin-banner-requests.index'),
            self::USER_PROFILE => route('admin.users.show', $data['user_id'] ?? 0),
            self::ORDER_DETAILS => route('admin.orders.show', $data['order_id'] ?? 0),
        };
    }
}
