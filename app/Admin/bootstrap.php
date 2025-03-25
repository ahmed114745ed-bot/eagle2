<?php

/**
 * Laravel-admin - admin builder based on Laravel.
 * @author z-song <https://github.com/z-song>
 *
 * Bootstraper for Admin.
 *
 * Here you can remove builtin form field:
 * Encore\Admin\Form::forget(['map', 'editor']);
 *
 * Or extend custom form field:
 * Encore\Admin\Form::extend('php', PHPEditor::class);
 *
 * Or require js and css assets:
 * Admin::css('/packages/prettydocs/css/styles.css');
 * Admin::js('/packages/prettydocs/js/main.js');
 *
 */

use Encore\Admin\Facades\Admin;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;



//Encore\Admin\Form::forget( ['map', 'editor']);
//Admin::js('/packages/customization/js/main.js');
if (Schema::hasTable('settings')) {
    $favicon = DB::table('settings')->where('key', 'app_fav_icon')->value('value');

    if ($favicon) {
        Admin::favicon(getImagePath($favicon)); // Use the correct path
    } else {
        Admin::favicon(asset('images/app-logo.png')); // Default favicon
    }
} else {
    Admin::favicon(asset('images/app-logo.png')); // Default favicon
}

Admin::css ('css/admin.css');
Admin::js(asset('js/laravel_admin.js'));

app('view')->prependNamespace('admin', resource_path('views/admin'));
view()->composer('admin::partials.menu', function (Illuminate\View\View $view) {
    $view->setPath(resource_path('views/admin/views/partials/menu.blade.php'));
});
view()->composer('admin::partials.footer', function (Illuminate\View\View $view) {
    $view->setPath(resource_path('views/admin/views/partials/footer.blade.php'));
});

view()->composer('admin::partials.js', function (Illuminate\View\View $view) {
    $view->setPath(resource_path('views/admin/views/partials/js.blade.php'));
});

view()->composer('admin::partials.css', function (Illuminate\View\View $view) {
    $view->setPath(resource_path('views/admin/views/partials/css.blade.php'));
});




