<?php

namespace Utd\Moments\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static momentComment(\Utd\Moments\Entities\Moment $moment, \App\Models\User $userSender)
 * @method static likeMoment(\Utd\Moments\Entities\Moment $moment, \App\Models\User $user)
 * @method static sendMomentGift(\App\Models\User $senderUser, \App\Models\Gift $gift, $receivedUser, $momentId)
 */
class CustomNotification extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'MomentCustomNotification';
    }
}
