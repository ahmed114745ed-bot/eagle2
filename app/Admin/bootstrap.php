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
use KevinSoft\MultiLanguage\MultiLanguage;
use App\Services\Admin\AdminMenuService;


//Encore\Admin\Form::forget( ['map', 'editor']);
//Admin::js('/packages/customization/js/main.js');

Admin::favicon(getFavIcon());

Admin::navbar(function ($navbar) {

    $languages = MultiLanguage::config('languages');
    $cookieName = MultiLanguage::config('cookie-name', 'locale');
    $current = request()->cookie($cookieName, config('app.locale'));

    $navbar->right(
        view('vendor.multi-language.language-menu', compact('languages', 'current'))
    );
});

Admin::css ('css/admin.css');
Admin::js(asset('js/laravel_admin.js'));

app('view')->prependNamespace('admin', resource_path('views/admin'));

/*
 | Sanitize admin menu: validate for circular references before rendering.
 | Uses AdminMenuService (Octane-safe, no static state).
 */
view()->composer('vendor.admin.partials.sidebar', function (Illuminate\View\View $view) {
    try {
        $menuService = new AdminMenuService();
        $data = $view->getData();
        if (isset($data['filteredMenu']) && is_array($data['filteredMenu'])) {
            $view->with('filteredMenu', $menuService->validateAndSanitize($data['filteredMenu']));
        }
    } catch (\Throwable $e) {
        \Log::error('AdminMenuService: failed to sanitize menu', [
            'error' => $e->getMessage(),
        ]);
    }
});

view()->composer('admin::partials.menu', function (Illuminate\View\View $view) {
    $view->setPath(resource_path('views/admin/views/partials/menu.blade.php'));
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
