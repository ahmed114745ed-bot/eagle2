<?php

use Laravel\Octane\Octane;

// Check if Octane is installed
if (!class_exists(Octane::class)) {
    return [
        'server' => 'swoole',
        'https' => false,
        'listeners' => [],
        'warm' => [],
        'flush' => [],
        'swoole' => ['options' => []],
        'garbage' => 50,
        'max_execution_time' => 30,
        'tables' => [],
        'cache' => ['rows' => 1000, 'bytes' => 10000],
    ];
}

use Laravel\Octane\Events\RequestReceived;
use Laravel\Octane\Events\RequestTerminated;
use Laravel\Octane\Events\TaskReceived;
use Laravel\Octane\Events\TickReceived;
use Laravel\Octane\Events\WorkerStarting;
use Laravel\Octane\Events\WorkerStopping;
use Laravel\Octane\Listeners\CollectGarbage;
use Laravel\Octane\Listeners\DisconnectFromDatabases;
use Laravel\Octane\Listeners\EnsureUploadedFilesAreValid;
use Laravel\Octane\Listeners\EnsureUploadedFilesCanBeMoved;
use Laravel\Octane\Listeners\FlushTemporaryContainerInstances;
use Laravel\Octane\Listeners\ReportException;
use Laravel\Octane\Listeners\StopWorkerIfNecessary;
use Laravel\Octane\Listeners\FlushAuthenticationState;
use Laravel\Octane\Listeners\FlushSessionState;
use Laravel\Octane\Listeners\FlushLocaleState;
use Laravel\Octane\Listeners\FlushQueuedCookies;

return [

    /*
    |--------------------------------------------------------------------------
    | Octane Server
    |--------------------------------------------------------------------------
    */

    'server' => env('OCTANE_SERVER', 'swoole'),

    /*
    |--------------------------------------------------------------------------
    | Force HTTPS
    |--------------------------------------------------------------------------
    */

    'https' => env('OCTANE_HTTPS', false),

    /*
    |--------------------------------------------------------------------------
    | Octane Listeners
    |--------------------------------------------------------------------------
    |
    | هذه الـ listeners مهمة جداً لـ reset الـ state بين الـ requests
    | خاصة الـ Authentication state
    |
    */

    'listeners' => [
        WorkerStarting::class => [
            EnsureUploadedFilesAreValid::class,
            EnsureUploadedFilesCanBeMoved::class,
        ],

        RequestReceived::class => [
            ...Octane::prepareApplicationForNextOperation(),
            ...Octane::prepareApplicationForNextRequest(),
            FlushAuthenticationState::class,  // ⭐ مهم جداً لحل مشكلة "غير مصدق"
            FlushSessionState::class,
            FlushLocaleState::class,
            FlushQueuedCookies::class,
            // Removed RefreshPusherConfigListener - endpoint handles this directly
            // ConfigController::updateConfigAgoraZego() updates runtime config directly
        ],

        RequestTerminated::class => [
            FlushTemporaryContainerInstances::class,
            // DisconnectFromDatabases::class,
            CollectGarbage::class,
            ReportException::class,
            StopWorkerIfNecessary::class,
        ],

        TaskReceived::class => [
            ...Octane::prepareApplicationForNextOperation(),
        ],

        TickReceived::class => [
            ...Octane::prepareApplicationForNextOperation(),
            \App\Listeners\RefreshCacheListener::class,
            \App\Listeners\OctaneBroadcasterRefreshListener::class, // تحديث Pusher في كل Workers
        ],

        WorkerStopping::class => [
            //
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Warm Services
    |--------------------------------------------------------------------------
    |
    | هذه الـ services تبقى محملة في الذاكرة بين الـ requests
    |
    */

    'warm' => [
        ...Octane::defaultServicesToWarm(),
    ],

    /*
    |--------------------------------------------------------------------------
    | Watch Configuration
    |--------------------------------------------------------------------------
    |
    | Files to watch for changes and trigger worker reload
    | Ignore config files to prevent reloading on config changes
    |
    */

    'watch' => [
        'paths' => [
            base_path('app'),
            base_path('bootstrap'),
            base_path('database'),
            base_path('public'),
            base_path('resources'),
            base_path('routes'),
        ],

        'ignore' => [
            base_path('config'), 
            base_path('storage'),
            base_path('public/uploads'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Flush Services
    |--------------------------------------------------------------------------
    |
    | Services to reset after each request
    |
    */

    'flush' => [
        'auth',
        'auth.driver',
        'session',
        'session.store',
        'request',
        'Illuminate\Http\Response',
        \Illuminate\Broadcasting\BroadcastManager::class,  // ⭐ CRITICAL: Force fresh broadcaster creation
        // Config must NOT be flushed - runtime updates stay in memory
        // 'config' removed intentionally
    ],

    /*
    |--------------------------------------------------------------------------
    | Swoole Options
    |--------------------------------------------------------------------------
    */

    'swoole' => [
        'options' => [
            'worker_num' => env('OCTANE_WORKERS', swoole_cpu_num()),
            'task_worker_num' => env('OCTANE_TASK_WORKERS', swoole_cpu_num()),
            'max_request' => env('OCTANE_MAX_REQUESTS', 500),
            'package_max_length' => 10 * 1024 * 1024,
            'http_parse_post' => true,
            'http_parse_cookie' => true,
            'enable_coroutine' => true,
            'log_level' => env('APP_DEBUG', false) ? 0 : 3,
            'daemonize' => false,
            'open_tcp_nodelay' => true,
            'enable_reuse_port' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Garbage Collection
    |--------------------------------------------------------------------------
    */

    'garbage' => env('OCTANE_GARBAGE_COLLECTION', 50),

    /*
    |--------------------------------------------------------------------------
    | Maximum Execution Time
    |--------------------------------------------------------------------------
    */

    'max_execution_time' => 30,

    /*
    |--------------------------------------------------------------------------
    | Tables
    |--------------------------------------------------------------------------
    */

    'tables' => [
        'example:1000' => [
            'name' => 'string:1000',
            'votes' => 'int',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache
    |--------------------------------------------------------------------------
    |
    | ⭐ Enabled for general app performance
    | Broadcasting/Pusher config excluded via:
    | - ForcePusherRefresh middleware (reads DB every request)
    | - DatabaseDrivenPusherBroadcaster (reads DB on every broadcast)
    | - BroadcastManager flush (destroyed after each request)
    |
    */

    'cache' => [
        'rows' => 1000,   // ⭐ Normal cache for app performance
        'bytes' => 10000, // ⭐ Normal cache for app performance
    ],

];

