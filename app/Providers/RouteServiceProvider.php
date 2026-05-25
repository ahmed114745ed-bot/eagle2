<?php

namespace App\Providers;

use App\Services\AppFeatureService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     *
     * This is used by Laravel authentication to redirect users after login.
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        $this->configureRateLimiting();

        $this->routes(function () {
            Route::prefix('api')
                ->middleware(['api', 'localization'])
                ->namespace($this->namespace)
                ->group(base_path('routes/api.php'));

            Route::prefix('api/utd')
                ->middleware(['api', 'localization',])
                ->namespace($this->namespace)
                ->group(base_path('routes/utd.php'));

            // ⚠️ TEMPORARY: Load test tokens route (only in non-production)
            if (config('app.env') !== 'production' && file_exists(base_path('routes/load-test-tokens.php'))) {
                Route::prefix('api')
                    ->middleware(['api'])
                    ->group(base_path('routes/load-test-tokens.php'));
            }

            if (AppFeatureService::isEnable('login')) {
                Route::prefix('api')
                    ->middleware('api')
                    ->middleware('localization')
                    ->namespace($this->namespace)
                    ->group(base_path('routes/game.php'));

                Route::middleware(['throttle'])
                    ->prefix('preview')
                    ->name('.preview.')
                    ->namespace($this->namespace)
                    ->group(base_path('app/Admin/preview-routes.php'));

                Route::middleware(['web', 'throttle:40,1'])
                    ->namespace($this->namespace)
                    ->group(base_path('routes/web.php'));
            }
        });
    }

    /**
     * Configure the rate limiters for the application.
     *
     * @return void
     */
    protected function configureRateLimiting()
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(90)->by(optional($request->user())->id ?: $request->ip());
        });

        RateLimiter::for('auth-login', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });

        RateLimiter::for('auth-register', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });

        RateLimiter::for('admin-login', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });

        RateLimiter::for('auth-otp', function (Request $request) {
            return Limit::perMinute(3)->by($request->ip());
        });

        RateLimiter::for('lucky-gift', function (Request $request) {
            return Limit::perMinute(20)
                ->by(optional($request->user())->id ?: $request->ip())
                ->response(function (Request $request, array $headers) {
                    return response()->json([
                        'status' => 0,
                        'message' => __('Too many gift requests. Please slow down.'),
                    ], 429, $headers);
                });
        });

        RateLimiter::for('game-balance', function (Request $request) {
            return Limit::perMinute(100)
                ->by($request->input('uid') ?: $request->ip())
                ->response(function (Request $request, array $headers) {
                    return response()->json([
                        'errorCode' => 429,
                        'errorMsg' => 'Too many balance requests. Please slow down.',
                        'data' => []
                    ], 429, $headers);
                });
        });

        RateLimiter::for('zego-room-count', function (Request $request) {
            return Limit::perMinute(60)
                ->by($request->input('room_id') ?: $request->ip())
                ->response(function (Request $request, array $headers) {
                    return response()->json([
                        'status' => 0,
                        'message' => 'Too many room count update requests.',
                    ], 429, $headers);
                });
        });
    }
}
