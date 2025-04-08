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
            $appName = Setting::where('key', 'app_title')->value('value') ?? 'Default';
            config(['app.name' => $appName]);

            $settings = DB::table('settings')->pluck('value', 'key')->toArray();

            config([
                'themes.primaryColor' => $settings['primary_color'] ?? '#FF9428',
                'themes.secondaryColor' => $settings['secondary_color'] ?? '#1A1A1A',
                'themes.textPrimaryColor' => $settings['text_primary_color'] ?? '#fdf8f8',
                'themes.textSecondaryColor' => $settings['text_secondary_color'] ?? '#c1b9b9',
                'themes.boxBackgroundColor' => $settings['box_background_color'] ?? '#222222',
                'themes.backgroundImage' => $settings['background_image'] ?? '',
                'themes.tableBackGroundColor' => $settings['table_background_color'] ?? '#c88213',
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
