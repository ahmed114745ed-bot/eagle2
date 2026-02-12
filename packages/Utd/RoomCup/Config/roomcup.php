<?php

return [
    'name' => 'RoomCup',

    'enabled' => env('ROOM_CUP_ENABLED', true),

    // Admin menu parent ID (0 = top-level, or set to existing menu ID)
    'menu_parent_id' => env('ROOM_CUP_MENU_PARENT_ID', 0),

    'settings' => [
        'type' => env('ROOM_CUP_TYPE', 'daily'), // daily, weekly, monthly
        'time' => env('ROOM_CUP_TIME', '00:00'),
        'day' => env('ROOM_CUP_DAY', 0),
        'interval' => env('ROOM_CUP_INTERVAL', 1),
    ],
];
