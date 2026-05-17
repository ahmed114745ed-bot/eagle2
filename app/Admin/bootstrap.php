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

 use Encore\Admin\Form;
 use App\Admin\Extensions\Form\Field\DynamicFields;
 use Encore\Admin\Facades\Admin;
use Encore\Admin\Widgets\Navbar;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;



//Encore\Admin\Form::forget( ['map', 'editor']);
//Admin::js('/packages/customization/js/main.js');

Admin::favicon(getFavIcon());


Admin::css ('css/admin.css');
Admin::js(asset('js/laravel_admin.js'));

app('view')->prependNamespace('admin', resource_path('views/admin'));
view()->composer('admin::partials.menu', function (Illuminate\View\View $view) {
    $view->setPath(resource_path('views/vendor/admin/partials/menu.blade.php'));
});
view()->composer('admin::partials.footer', function (Illuminate\View\View $view) {
    $view->setPath(resource_path('views/admin/views/partials/footer.blade.php'));
});

view()->composer('admin::partials.js', function (Illuminate\View\View $view) {
    $view->setPath(resource_path('views/admin/views/partials/js.blade.php'));
});
view()->composer('admin::partials.cdn', function (Illuminate\View\View $view) {
    $view->setPath(resource_path('views/admin/views/partials/cdn.blade.php'));
});

view()->composer('admin::partials.css', function (Illuminate\View\View $view) {
    $view->setPath(resource_path('views/admin/views/partials/css.blade.php'));
});

Form::extend('dynamicFields', DynamicFields::class);


Encore\Admin\Admin::script(<<<'JS'
    $(document).on('pjax:start', function () {
        $('.select2-container--open').each(function () {
            $(this).remove();
        });
    });
JS);

// Filter menu to hide AreaManager items when package is not installed
view()->composer('admin::partials.menu', function ($view) {
    $menu = $view->getData()['menu'] ?? null;

    if ($menu && !\App\Support\PackageHelper::isInstalled('areaManager')) {
        // Hide AreaManager menu items (IDs: 277, 278, 279)
        $filteredMenu = $menu->filter(function ($item) {
            return !in_array($item->id, [277, 278, 279]);
        });

        $view->with('menu', $filteredMenu);
    }
});
