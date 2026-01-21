<?php

namespace App\Providers;

use App\Models\Pk;
use Carbon\Carbon;
use App\Models\Gift;
use App\Models\Room;
use App\Models\User;
use App\Models\Ware;
use App\Models\Emoji;
use App\Models\Agency;
use App\Models\Family;
use Encore\Admin\Form;
use App\Helpers\Common;
use App\Models\Setting;
use App\Models\Language;
use App\Models\FamilyUser;
use App\Admin\Fields\Image;
use App\Helpers\RoomHelper;
use App\Models\UserSallary;
use App\Helpers\CacheHelper;
use Illuminate\Http\Request;
use App\Classes\UserHandling;
use App\Observers\PKObserver;
use Modules\Vip\Entities\Vip;
use App\Helpers\ManagerHelper;
use App\Observers\VipObserver;
use App\Services\RedisService;
use App\Admin\Fields\ImagePath;
use App\Observers\GiftObserver;
use App\Observers\RoomObserver;
use App\Observers\UserObserver;
use App\Observers\WareObserver;
use Encore\Admin\Facades\Admin;
use App\Observers\EmojiObserver;
use App\Models\AgencyJoinRequest;
use App\Observers\AgencyObserver;
use App\Observers\ConfigObserver;
use App\Observers\FamilyObserver;
use App\Observers\SettingObserver;
use Illuminate\Support\Facades\DB;
use App\Helpers\CustomNotification;
use App\Repositories\Room\RoomRepo;
use App\Repositories\User\UserRepo;
use Illuminate\Support\Facades\URL;
use App\Observers\FamilyUserObserver;
use Illuminate\Support\Facades\Cache;
use App\Observers\UserSallaryObserver;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use App\Observers\RoomBoomLevelObserver;
use App\Services\Gifts\LuckyGiftService;
use App\Observers\AgencyJoinRequestObserver;
use App\Repositories\Room\RoomRepoInterface;
use App\Repositories\User\UserRepoInterface;
use Illuminate\Database\Eloquent\Collection;
use Modules\RoomBoom\Entities\RoomBoomLevel;
use App\Repositories\Community\SearchRepository;
use App\Repositories\Community\SearchRepositoryInterface;
use Illuminate\Support\Str;
use Illuminate\Broadcasting\BroadcastManager;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Schema::createIfNotExists macro - skip if table already exists
        \Illuminate\Database\Schema\Builder::macro('createIfNotExists', function (string $table, \Closure $callback) {
            if (!Schema::hasTable($table)) {
                Schema::create($table, $callback);
            }
        });

        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }

        Form::extend('image', Image::class);
        Form::extend('imagePath', ImagePath::class);

        if ($this->app->isLocal()) {
            //            $this->app->register(\Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider::class);
            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
            $this->app->register(TelescopeServiceProvider::class);
        }

        $this->app->bind(RoomRepoInterface::class, RoomRepo::class);
        $this->app->bind(UserRepoInterface::class, UserRepo::class);
        $this->app->bind('RedisService', fn($app) => new RedisService());
        $this->app->bind('UserHandling', fn($app) => new UserHandling());
        $this->app->bind('CustomNotification', fn($app) => new CustomNotification());
        $this->app->bind('RoomHelper', fn($app) => new RoomHelper());
        $this->app->bind('ManagerHelper', fn($app) => new ManagerHelper());
        $this->app->bind(SearchRepositoryInterface::class, SearchRepository::class);

        // Register custom event dispatcher for Octane broadcaster refresh
        if (\App\Services\OctaneBroadcasterService::isOctane()) {
            $this->app->singleton('events', \App\Services\OctaneEventDispatcher::class);
        }

        $this->defineCarbonMacros();
    }

    public function boot(): void
    {
        $this->dashboardAdminConfig();
        $this->setupAppSettings();
        $this->setupLanguages();
        $this->registerModelObservers();
        $this->cacheLuckyGiftProbabilities();
        
        // ⭐ CRITICAL: Register Queue Job listener for Pusher config refresh
        // This ensures ALL queue jobs use fresh Pusher config from database
        $this->registerQueuePusherConfigRefresh();

        // Load your custom settings
        $start = Common::getSettingValue('week_start') ?? 'monday';
        $end   = Common::getSettingValue('week_end')   ?? 'sunday';

        Carbon::setWeekStartsAt(constant('Carbon\\Carbon::' . strtoupper($start)));
        Carbon::setWeekEndsAt(constant('Carbon\\Carbon::' . strtoupper($end)));
        if (!Str::hasMacro('unescape')) {
            Str::macro('unescape', function ($value) {
                return htmlspecialchars_decode($value, ENT_QUOTES);
            });
        }
    }
    
    /**
     * Register Queue Job listener to refresh Pusher config before each job
     * This is critical for ensuring broadcast events use fresh credentials
     */
    protected function registerQueuePusherConfigRefresh(): void
    {
        $this->app['events']->listen(
            \Illuminate\Queue\Events\JobProcessing::class,
            function ($event) {
                static $lastConfigHash = null;
                
                try {
                    // Check if Pusher config changed
                    $forceUpdate = \Illuminate\Support\Facades\Cache::has('pusher_config_changed');
                    
                    // Get fresh config from DB
                    $freshConfig = getPusherConfig();
                    $currentHash = md5(json_encode([
                        $freshConfig['app_key'] ?? '',
                        $freshConfig['app_secret'] ?? '',
                        $freshConfig['app_id'] ?? '',
                    ]));
                    
                    // Update if changed or forced
                    if ($forceUpdate || $lastConfigHash !== $currentHash) {
                        // Update Laravel runtime config
                        \Illuminate\Support\Facades\Config::set([
                            'broadcasting.connections.pusher.key' => $freshConfig['app_key'],
                            'broadcasting.connections.pusher.secret' => $freshConfig['app_secret'],
                            'broadcasting.connections.pusher.app_id' => $freshConfig['app_id'],
                            'broadcasting.connections.pusher.options.cluster' => $freshConfig['app_cluster'] ?? 'mt1',
                        ]);
                        

                            $broadcastManager = app(BroadcastManager::class);
                            $broadcastManager->forgetDrivers();
                            $broadcastManager->driver('pusher');

                            $lastConfigHash = $currentHash;

                            if ($forceUpdate) {
                                Cache::forget('pusher_config_changed');
                            }
                 
                        
                    }
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::error('Queue: Pusher config refresh failed', [
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        );
    }

    public function dashboardAdminConfig(): void
    {
        $prefix = request()->segment(1);

        $originalConfig = config('admin.route');

        if ($prefix === 'superadmin') {
            config(['admin.route' => config('admin.superadmin_route')]);
            Admin::routes();
        } elseif ($prefix === 'areaManager') {
            config(['admin.route' => config('admin.area_manager_route')]);
            Admin::routes();
        } elseif ($prefix === 'admin') {
            // Admin::routes();
            config(['admin.route' => $originalConfig]);
        }
    }

    protected function defineCarbonMacros(): void
    {
        Carbon::macro('startAndEndOfMonthUTC', function ($year, $month, $timezone = 'UTC') {
            $firstDay = Carbon::create($year, $month, 1, 0, 0, 0, $timezone);

            return [
                $firstDay->copy()->setTimezone('UTC'),
                $firstDay->copy()->endOfMonth()->setTimezone('UTC'),
            ];
        });
    }

    protected function setupAppSettings(): void
    {

        $locale = app()->getLocale();
        $key = $locale === 'ar' ? 'app_title_ar' : 'app_title_en';

        $appName = Cache::rememberForever("settings.{$key}", function () use ($key) {
            return Setting::where('key', $key)->value('value') ?? 'Default';
        });

        config(['app.name' => $appName]);

        $settings = CacheHelper::cacheSettings();

        /** @var Collection $rememberForever*/
        if (gettype($settings) !== 'array') {
            $settings = $settings->pluck('value', 'key')->toArray();
        }

        Config::set([
            'themes.primaryColor' => $settings['primary_color'] ?? '#FF9428',
            'themes.secondaryColor' => $settings['secondary_color'] ?? '#1A1A1A',
            'themes.textPrimaryColor' => $settings['text_primary_color'] ?? '#fdf8f8',
            'themes.textSecondaryColor' => $settings['text_secondary_color'] ?? '#c1b9b9',
            'themes.boxBackgroundColor' => $settings['box_background_color'] ?? '#222222',
            'themes.backgroundImage' => $settings['app_background'] ?? '',
            'themes.brandBackgroundImage' => $settings['brand_background_image'] ?? '',
            'themes.tableBackGroundColor' => $settings['table_background_color'] ?? '#c88213',

            'services.fawry' => [
                'fawry_secret' => $settings['fawry_secret'] ?? '',
                'fawry_merchant_code' => $settings['fawry_merchant_code'] ?? '',
                'fawry_return_url' => $settings['fawry_return_url'] ?? '',
                'fawry_url' => $settings['fawry_url'] ?? '',
                'fawry_webhook_url' => $settings['fawry_webhook_url'] ?? '',
            ],

            'services.utd_fawry' => [
                'utd_fawry_secret' => $settings['utd_fawry_secret'] ?? '',
                'utd_fawry_merchant_code' => $settings['utd_fawry_merchant_code'] ?? '',
                'utd_url' => $settings['utd_url'] ?? '',
                'utd_fawry_return_url' => $settings['utd_fawry_return_url'] ?? '',
                'utd_fawry_url' => $settings['utd_fawry_url'] ?? '',
            ],

            'paysky' => [
                'api_key' => $settings['paysky_api_key'] ?? '',
                'merchant_id' => $settings['paysky_merchant_id'] ?? '',
                'terminal_id' => $settings['paysky_terminal_id'] ?? '',
                'base_url' => $settings['paysky_base_url'] ?? '',
            ],

            'stripe' => [
                'test_secret_key' => $settings['stripe_test_secret_key'] ?? '',
                'success_url' => $settings['stripe_success_url'] ?? '',
                'cancel_url' => $settings['stripe_cancel_url'] ?? '',
                'currency' => $settings['stripe_currency'] ?? '',
                'webhook_secret' => $settings['stripe_webhook_secret'] ?? '',
                'webhook_url' => $settings['stripe_webhook_url'] ?? '',
            ],

            'nafezly-payments' => [
                'OPAY_CURRENCY' => $settings['opay_currency'] ?? '',
                'OPAY_SECRET_KEY' => $settings['opay_secret_key'] ?? '',
                'OPAY_PUBLIC_KEY' => $settings['opay_public_key'] ?? '',
                'OPAY_MERCHANT_ID' => $settings['opay_merchant_id'] ?? '',
                'OPAY_COUNTRY_CODE' => $settings['opay_country_code'] ?? '',
                'OPAY_BASE_URL' => $settings['opay_base_url'] ?? '',
                'OPAY_WEBHOOK_URL' => $settings['opay_webhook_url'] ?? '',
            ],

            'apple' => [
                'apple_team_id' => $settings['apple_team_id'] ?? '',
                'apple_key_id' => $settings['apple_key_id'] ?? '',
                'apple_client_id' => $settings['apple_client_id'] ?? '',
                'apple_redirect_uri' => $settings['apple_redirect_uri'] ?? '',
                'apple_service_file' => $settings['apple_service_file'] ?? '',
            ],

            'paypal' => [
                'base_url' => $settings['paypal_base_url'] ?? '',
                'client_id' => $settings['paypal_client_id'] ?? '',
                'client_secret' => $settings['paypal_client_secret'] ?? '',
                'currency' => $settings['paypal_currency'] ?? '',
                'webhook_id' => $settings['paypal_webhook_id'] ?? '',
            ],

            'codapay' => [
                'base_url'   => $settings['codapay_base_url'] ?? '',
                'api_key'    => $settings['codapay_api_key'] ?? '',
                'project_id' => $settings['codapay_project_id'] ?? '',
                'country'    => $settings['codapay_country'] ?? '',
                'pay_type'   => $settings['codapay_pay_type'] ?? '',
                'currency'   => $settings['codapay_currency'] ?? '',
            ],

            'googlePay' => [
                'payment_url' => $settings['google_pay_payment_url'] ?? '',
                'node_server_name' => $settings['google_pay_node_server_name'] ?? '',
            ],

            'is_fawry_active' => $settings['is_fawry_active'] ?? 0,
            'is_paypal_active' => $settings['is_paypal_active'] ?? 0,
            'is_utd_fawry_active' => $settings['is_utd_fawry_active'] ?? 0,
            'is_paysky_active' => $settings['is_paysky_active'] ?? 0,
            'is_strip_active' => $settings['is_strip_active'] ?? 0,
            'is_opay_active' => $settings['is_opay_active'] ?? 0,
            'is_applepay_active' => $settings['is_applepay_active'] ?? 0,
            'is_google_pay_active' => $settings['is_google_pay_active'] ?? 0,
            'is_codapay_active' => $settings['is_codapay_active'] ?? 0,
        ]);

        Cache::put('app_title', $appName, now()->addHours(24));
        Cache::put('host_agency', $settings['host_agency'] ?? 1);
    }

    protected function setupLanguages(): void
    {
        $enabledLanguages = Cache::rememberForever('languages', function () {
            return Language::where('is_enabled', true)
                ->pluck('name', 'code')
                ->toArray();
        });

        Config::set('admin.extensions.multi-language.languages', $enabledLanguages);
        Config::set('admin.logo', Cache::get('app_title', 'Default Title'));
    }

    protected function registerModelObservers(): void
    {
        User::observe(UserObserver::class);
        Gift::observe(GiftObserver::class);
        Emoji::observe(EmojiObserver::class);
        Ware::observe(WareObserver::class);
        Room::observe(RoomObserver::class);
        UserSallary::observe(UserSallaryObserver::class);
        Family::observe(FamilyObserver::class);
        FamilyUser::observe(FamilyUserObserver::class);
        Pk::observe(PKObserver::class);
        Agency::observe(AgencyObserver::class);
        AgencyJoinRequest::observe(AgencyJoinRequestObserver::class);
        Vip::observe(VipObserver::class);
        RoomBoomLevel::observe(RoomBoomLevelObserver::class);
        Setting::observe(SettingObserver::class);
        \App\Models\Config::observe(ConfigObserver::class);
    }

    protected function cacheLuckyGiftProbabilities(): void
    {
        $probabilities = app(LuckyGiftService::class)->getProbabilityTimes();

        foreach ($probabilities as $index => $value) {
            Cache::put('probability_times_' . ($index + 1), $value, now()->addMinutes(60));
        }
    }
}
