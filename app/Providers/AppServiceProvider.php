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

        $this->app->bind (RoomRepoInterface::class,RoomRepo::class);
        $this->app->bind (UserRepoInterface::class,UserRepo::class);
        $this->app->bind ('RedisService',function($app){
            return new RedisService();
        });
        $this->app->bind ('UserHandling',function($app){
            return new UserHandling();
        });
        $this->app->bind ('CustomNotification',function($app){
            return new CustomNotification();
        });
        $this->app->bind ('RoomHelper',function($app){
            return new RoomHelper();
        });
        $this->app->bind ('ManagerHelper',function($app){
            return new ManagerHelper();
        });
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
        User::observe (UserObserver::class);
        Gift::observe (GiftObserver::class);
        Emoji::observe (EmojiObserver::class);
        Ware::observe (WareObserver::class);
        Room::observe(RoomObserver::class);
        UserSallary::observe(UserSallaryObserver::class);
        Family::observe (FamilyObserver::class);
        FamilyUser::observe (FamilyUserObserver::class);
        Pk::observe (PKObserver::class);
        Agency::observe (AgencyObserver::class);
        AgencyJoinRequest::observe (AgencyJoinRequestObserver::class);
    }
}
