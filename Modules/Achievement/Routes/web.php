<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

use Modules\Achievement\Http\Controllers\web\AchievementLevelsModuleController;
use Modules\Achievement\Http\Controllers\web\GiftAchievemntController;

Route::group(
    [
        'prefix'     => config('admin.route.prefix'),
        'namespace'  => 'web',
        'middleware' => [
            'web',
            'admin',
            'adminIp',
            //            'adminGeneralBan',
            'multiLanguage',
            'appFeatureEnable:achievement',
        ],
        'as'         => config('admin.route.prefix') . '.',
    ],
    function (\Illuminate\Routing\Router $router) {
        $router->get('/test-calculat', function (){
            $gift=\App\Models\Gift::first();
            $user_owner=\App\Models\User::find(1260);
            \Modules\Achievement\Jobs\CalculateAchievement::dispatch($gift,5, $user_owner);
            dd("dsdsdsds");
        });

        $router->resource('achievements', AchievementsController::class);
        $router->post('/store-user-achievement', [AchievementLevelsModuleController::class, 'store'])->name('store-user-achievement');
        $router->get('/get-achievement-levels/{achievementId}', [AchievementLevelsModuleController::class,'getAchievementLevels'])->name('get-achievement-levels');
        $router->get('/get-view-page', [AchievementLevelsModuleController::class,'viewPage'])->name('get-view-page');
       $router->resource('user-achievement-levels', UserAchievementLevelController::class);
      // $router->post('postAddGiftAchievementLevel', [GiftAchievemntController::class,'postAddGiftAchievementLevel'])->name('postAddGiftAchievementLevel');
       $router->get('achievement-levels/create/{id}', 'AchievementsLevelsController@create')->name('achievement-levels.create');
        $router->resource('gift-achievements','UserGiftAchController');
        $router->resource('gift-achievment', 'GiftAchiementController');
        $router->post('postAddGiftAchievement', [GiftAchievemntController::class,'postAddGiftAchievemnt'])->name('postAddGiftAchievement');
        $router->post('postAddGiftAchievementLevel', [GiftAchievemntController::class,'postAddGiftAchievementLevel'])->name('postAddGiftAchievementLevel');
        $router->post('posteditGiftAchievementLevel', [GiftAchievemntController::class,'posteditGiftAchievementLevel'])->name('posteditGiftAchievementLevel');
        $router->resource('achievement-levels', AchievementsLevelsController::class,['name'=>['create'=>'create2']]);
    });
