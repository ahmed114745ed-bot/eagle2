<?php
namespace App\Support;

class PackageHelper
{
    /**
     * Check if a package is installed
     */
    public static function isInstalled(string $package): bool
    {
        $packages = [
            'achievements' => 'Utd\\Achievements\\AchievementsServiceProvider',
            'vip' => 'Utd\\Vip\\VipServiceProvider',
            'wallet' => 'Utd\\Wallet\\WalletServiceProvider',
        ];
        return isset($packages[$package]) && class_exists($packages[$package]);
    }

    /**
     * Get entity class if package installed
     */
    public static function getEntity(string $package, string $entity): ?string
    {
        if (!self::isInstalled($package)) {
            return null;
        }
        $entities = [
            'achievements' => [
                'Achievement' => 'Utd\\Achievements\\Entities\\Achievement',
                'UserAchievementLevel' => 'Utd\\Achievements\\Entities\\UserAchievementLevel',
            ],
        ];
        return $entities[$package][$entity] ?? null;
    }

    /**
     * Query entity safely
     */
    public static function query(string $package, string $entity)
    {
        $class = self::getEntity($package, $entity);
        if (!$class) {
            return collect([]); // Return empty collection
        }
        return $class::query();
    }
}
