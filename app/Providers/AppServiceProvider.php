<?php

namespace App\Providers;

use App\Classes\UserHandling;
use App\Helpers\CustomNotification;
use App\Helpers\ManagerHelper;
use App\Helpers\RoomHelper;
use App\Models\Agency;
use App\Models\AgencyJoinRequest;
use App\Models\Emoji;
use App\Models\Family;
use App\Models\FamilyUser;
use App\Models\Gift;
use App\Models\Pk;
use App\Models\Room;
use App\Models\Setting;
use App\Models\User;
use App\Models\UserSallary;
use App\Models\Ware;
use App\Observers\Api\V1\AgencyJoinRequestObserver;
use App\Observers\Api\V1\AgencyObserver;
use App\Observers\Api\V1\FamilyObserver;
use App\Observers\Api\V1\FamilyUserObserver;
use App\Observers\Api\V1\PKObserver;
use App\Observers\Api\V1\RoomObserver;
use App\Observers\Api\V1\UserObserver;
use App\Observers\Api\V1\UserSallaryObserver;
use App\Observers\EmojiObserver;
use App\Observers\GiftObserver;
use App\Observers\WareObserver;
use App\Repositories\Room\RoomRepo;
use App\Repositories\Room\RoomRepoInterface;
use App\Repositories\User\UserRepo;
use App\Repositories\User\UserRepoInterface;
use App\Services\RedisService;
use Illuminate\Support\ServiceProvider;
use App\Repositories\Community\SearchRepository;
use App\Repositories\Community\SearchRepositoryInterface;
use Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use App\Models\Language;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        if ($this->app->isLocal()) {
            $this->app->register(\Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider::class);
            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
            $this->app->register(TelescopeServiceProvider::class);
        }

        $this->app->bind(RoomRepoInterface::class, RoomRepo::class);
        $this->app->bind(UserRepoInterface::class, UserRepo::class);
        $this->app->bind('RedisService', fn ($app) => new RedisService());
        $this->app->bind('UserHandling', fn ($app) => new UserHandling());
        $this->app->bind('CustomNotification', fn ($app) => new CustomNotification());
        $this->app->bind('RoomHelper', fn ($app) => new RoomHelper());
        $this->app->bind('ManagerHelper', fn ($app) => new ManagerHelper());
        $this->app->bind(SearchRepositoryInterface::class, SearchRepository::class);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);

        if (Schema::hasTable('settings')) {
            if (app()->getLocale() === 'en'){
                $appName = Setting::where('key', 'app_title_en')->value('value') ?? 'Default';
            }
            if (app()->getLocale() === 'ar'){
                $appName = Setting::where('key', 'app_title_ar')->value('value') ?? 'Default';
            }

            config(['app.name' => $appName]);

            $settings = DB::table('settings')->pluck('value', 'key')->toArray();

            config([
                'themes.primaryColor' => $settings['primary_color'] ?? '#FF9428',
                'themes.secondaryColor' => $settings['secondary_color'] ?? '#1A1A1A',
                'themes.textPrimaryColor' => $settings['text_primary_color'] ?? '#fdf8f8',
                'themes.textSecondaryColor' => $settings['text_secondary_color'] ?? '#c1b9b9',
                'themes.boxBackgroundColor' => $settings['box_background_color'] ?? '#222222',
                'themes.backgroundImage' => $settings['app_background'] ?? '',
                'themes.brandBackgroundImage' => $settings['brand_background_image'] ?? '',
                'themes.tableBackGroundColor' => $settings['table_background_color'] ?? '#c88213',

                'services.fawry.fawry_secret' => $settings['fawry_secret'] ?? '6ed92079-a485-4373-9453-505e20f6ef48',
                'services.fawry.fawry_merchant_code' => $settings['fawry_merchant_code'] ?? '770000019812',
                'services.fawry.utd_url' => $settings['fawry_utd_url'] ?? 'http://utd_backend.test/api/fawry-initial',
                'services.fawry.fawry_return_url' => $settings['fawry_return_url'] ?? '/admin/payment-with-method',
                'services.fawry.fawry_url' => $settings['fawry_url'] ?? 'https://atfawry.fawrystaging.com/fawrypay-api/api/payments/init',


                'paysky.api_key'     => $settings['paysky_api_key'] ?? '',
                'paysky.merchant_id' => $settings['paysky_merchant_id'] ?? '',
                'paysky.terminal_id' => $settings['paysky_terminal_id'] ?? '',
                'paysky.base_url'    => $settings['paysky_base_url'] ?? '',

                'stripe.test_secret_key' => $settings['stripe_test_secret_key'] ?? '',
                'stripe.success_url' => $settings['stripe_success_url'] ?? '',
                'stripe.cancel_url' => $settings['stripe_cancel_url'] ?? '',
                'stripe.currency' => $settings['stripe_currency'] ?? '',
                'stripe.webhook_secret' => $settings['stripe_webhook_secret'] ?? '',

                'nafezly-payments.OPAY_CURRENCY' => $settings['opay_currency'] ?? 'EGP',
                'nafezly-payments.OPAY_SECRET_KEY' => $settings['opay_secret_key'] ?? '',
                'nafezly-payments.OPAY_PUBLIC_KEY' => $settings['opay_public_key'] ?? '',
                'nafezly-payments.OPAY_MERCHANT_ID' => $settings['opay_merchant_id'] ?? '',
                'nafezly-payments.OPAY_COUNTRY_CODE' => $settings['opay_country_code'] ?? 'EG',
                'nafezly-payments.OPAY_BASE_URL' => $settings['opay_base_url'] ?? 'https://sandboxapi.opaycheckout.com',
            ]);

            if (!Cache::has('app_title')) {
                Cache::put('app_title', $appName, now()->addHours(24));
            }
        } else {
            config(['app.name' => 'Default']);
            Cache::put('app_title', 'Default Title', now()->addHours(24));
        }

        if (Schema::hasTable('languages')) {
            $enabledLanguages = Cache::rememberForever('languages', function () {
                return Language::where('is_enabled', true)->pluck('name', 'code')->toArray();
            });
            Config::set('admin.extensions.multi-language.languages', $enabledLanguages);
        }

        Config::set('admin.logo', Cache::get('app_title', 'Default Title'));

        // تسجيل الـ Observers
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
    }
}
