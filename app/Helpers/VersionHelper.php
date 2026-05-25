<?php

namespace App\Helpers;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class VersionHelper
{
    /**
     * Check if app version is valid and up-to-date
     *
     * @param string $platform 'android' or 'ios'
     * @param string $currentVersion User's current app version
     * @return array ['valid' => bool, 'update_required' => bool, 'message' => string]
     */
    public static function checkVersion(string $platform, string $currentVersion): array
    {
        $platform = strtolower($platform);

        if (!in_array($platform, ['android', 'ios'])) {
            return [
                'valid' => false,
                'update_required' => false,
                'message' => 'Invalid platform',
            ];
        }

        $minVersion = self::getSetting("{$platform}_min_version", '1.0.0');
        $updateRequired = (bool) self::getSetting("{$platform}_update_required", false);

        $isValid = version_compare($currentVersion, $minVersion, '>=');

        if (!$isValid) {
            return [
                'valid' => false,
                'update_required' => true,
                'message' => __('Please update your app to the latest version'),
                'min_version' => $minVersion,
            ];
        }

        if ($updateRequired) {
            $latestVersion = self::getSetting("{$platform}_current_version", '1.0.0');
            $isLatest = version_compare($currentVersion, $latestVersion, '>=');

            if (!$isLatest) {
                return [
                    'valid' => true,
                    'update_required' => true,
                    'message' => __('A new version is available. Please update'),
                    'current_version' => $latestVersion,
                ];
            }
        }

        return [
            'valid' => true,
            'update_required' => false,
            'message' => __('Your app is up to date'),
        ];
    }

    /**
     * Get setting value with cache
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    protected static function getSetting(string $key, $default = null)
    {
        return Cache::remember("setting_{$key}", 3600, function () use ($key, $default) {
            $setting = Setting::where('key', $key)->first();
            return $setting ? $setting->value : $default;
        });
    }

    /**
     * Clear version settings cache
     *
     * @return void
     */
    public static function clearCache(): void
    {
        $platforms = ['android', 'ios'];
        $keys = ['current_version', 'min_version', 'update_required'];

        foreach ($platforms as $platform) {
            foreach ($keys as $key) {
                Cache::forget("setting_{$platform}_{$key}");
            }
        }
    }
}
