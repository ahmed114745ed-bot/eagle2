<?php

namespace App\Listeners;

use App\Models\Language;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Cookie;
use Laravel\Octane\Events\RequestReceived;

class SetupMultiLanguageListener
{
    /**
     * Handle the event.
     * يتم تنفيذه مع كل request في Octane لضبط اللغات
     */
    public function handle(RequestReceived $event): void
    {
        $this->setupLanguages();
        $this->setupAppTitle();
    }

    /**
     * ضبط اللغات المتاحة للـ multi-language extension
     */
    protected function setupLanguages(): void
    {
        $enabledLanguages = Cache::rememberForever('languages', function () {
            return Language::where('is_enabled', true)
                ->pluck('name', 'code')
                ->toArray();
        });

        // ضبط الـ config
        Config::set('admin.extensions.multi-language.languages', $enabledLanguages);

        // ضبط الـ current locale
        $cookieName = Config::get('admin.extensions.multi-language.cookie-name', 'locale');
        $default = Config::get('admin.extensions.multi-language.default', 'en');
        $current = Cookie::get($cookieName, $default);

        // Share مع كل الـ views
        View::share('languages', $enabledLanguages);
        View::share('current', $current);
    }

    /**
     * ضبط عنوان التطبيق حسب اللغة الحالية
     */
    protected function setupAppTitle(): void
    {
        $locale = app()->getLocale();
        $key = $locale === 'ar' ? 'app_title_ar' : 'app_title_en';

        $appName = Cache::rememberForever("settings.{$key}", function () use ($key) {
            return \App\Models\Setting::where('key', $key)->value('value') ?? 'Default';
        });

        Config::set('app.name', $appName);
        Config::set('admin.logo', $appName);
    }
}
