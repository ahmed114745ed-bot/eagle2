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

//Encore\Admin\Form::forget( ['map', 'editor']);
//Admin::js('/packages/customization/js/main.js');
Admin::css ('css/admin.css');
app('view')->prependNamespace('admin', resource_path('views/admin'));
view()->composer('admin::partials.menu', function (Illuminate\View\View $view) {
    $view->setPath(resource_path('views/admin/views/partials/menu.blade.php'));
});
view()->composer('admin::partials.footer', function (Illuminate\View\View $view) {
    $view->setPath(resource_path('views/admin/views/partials/footer.blade.php'));
});
