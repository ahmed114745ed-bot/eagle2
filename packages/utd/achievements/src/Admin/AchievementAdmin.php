<?php

namespace Utd\Achievements\Admin;

use Encore\Admin\Admin;

/**
 * Achievement Admin Integration
 *
 * This class registers admin routes and resources ONLY if
 * Encore Laravel-Admin is installed.
 */
class AchievementAdmin
{
    /**
     * Check if Laravel Admin is available
     */
    public static function isAvailable(): bool
    {
        return class_exists(\Encore\Admin\Admin::class);
    }

    /**
     * Register admin routes
     */
    public static function routes(): void
    {
        if (!self::isAvailable()) {
            return;
        }

        Admin::routes();

        // Achievement routes
        \Route::group([
            'prefix' => config('admin.route.prefix') . '/achievements',
            'middleware' => config('admin.route.middleware'),
            'namespace' => 'Utd\Achievements\Admin\Controllers',
        ], function () {
            \Route::get('/', 'AchievementController@index')->name('admin.achievements.index');
            \Route::get('/create', 'AchievementController@create')->name('admin.achievements.create');
            \Route::post('/', 'AchievementController@store')->name('admin.achievements.store');
            \Route::get('/{id}/edit', 'AchievementController@edit')->name('admin.achievements.edit');
            \Route::put('/{id}', 'AchievementController@update')->name('admin.achievements.update');
            \Route::delete('/{id}', 'AchievementController@destroy')->name('admin.achievements.destroy');

            // Achievement Levels
            \Route::get('/{id}/levels', 'AchievementLevelController@index')->name('admin.achievement-levels.index');
            \Route::post('/{id}/levels', 'AchievementLevelController@store')->name('admin.achievement-levels.store');

            // User Achievement Levels
            \Route::get('/users', 'UserAchievementController@index')->name('admin.user-achievements.index');
        });
    }

    /**
     * Register admin menu
     */
    public static function menu(): array
    {
        if (!self::isAvailable()) {
            return [];
        }

        return [
            [
                'title' => 'Achievements',
                'icon' => 'fa-trophy',
                'uri' => 'achievements',
                'children' => [
                    ['title' => 'All Achievements', 'uri' => 'achievements'],
                    ['title' => 'User Achievements', 'uri' => 'achievements/users'],
                ],
            ],
        ];
    }
}
