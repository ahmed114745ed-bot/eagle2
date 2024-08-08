<?php

namespace App\Observers;

use App\Models\Emoji;

class EmojiObserver
{
    /**
     * Handle the Emoji "created" event.
     *
     * @param  \App\Models\Emoji  $emoji
     * @return void
     */
    public function created(Emoji $emoji)
    {
        if ($emoji->enable) {
            settings()->set('emoji_updated', time());
        }

    }

    /**
     * Handle the Emoji "updated" event.
     *
     * @param  \App\Models\Emoji  $emoji
     * @return void
     */
    public function updated(Emoji $emoji)
    {
        $isEnableOld = $emoji->getOriginal('enable');
        $svgOld = $emoji->getOriginal('emoji');

        if ((!$isEnableOld && $emoji->enable) ||($isEnableOld && !$emoji->enable) || $emoji->emoji != $svgOld) {
            settings()->set('emoji_updated_at', time());
        }
    }

    /**
     * Handle the Emoji "deleted" event.
     *
     * @param  \App\Models\Emoji  $emoji
     * @return void
     */
    public function deleted(Emoji $emoji)
    {
        if ($emoji->enable) {
            settings()->set('emoji_updated_at', time());
        }

    }

    /**
     * Handle the Emoji "restored" event.
     *
     * @param  \App\Models\Emoji  $emoji
     * @return void
     */
    public function restored(Emoji $emoji)
    {
        //
    }

    /**
     * Handle the Emoji "force deleted" event.
     *
     * @param  \App\Models\Emoji  $emoji
     * @return void
     */
    public function forceDeleted(Emoji $emoji)
    {
        //
    }
}
