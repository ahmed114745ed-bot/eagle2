<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;
use Modules\TaskStream\Entities\TaskStream;
use Utd\Achievements\Entities\Achievement;
use Utd\Moments\Entities\Moment;
use Utd\Reals\Entities\Real;
use Utd\Room\Entities\Pk;
use Utd\Room\Entities\Room;

class PackageHelper
{
    private static array $packages = [
        'achievement' => Achievement::class,
        'moment' => Moment::class,
        'real' => Real::class,
        'room' => Room::class,
        'pk' => Pk::class,
        'taskStream' => TaskStream::class,
    ];

    /**
     * Check if a package is installed
     */
    public static function isInstalled(string $package): bool
    {
        return isset(self::$packages[$package]) && class_exists(self::$packages[$package]);
    }

    /**
     * Get model class if package installed
     */
    public static function getEntity(string $package): ?string
    {
        if (!self::isInstalled($package)) {
            return null;
        }

        return self::$packages[$package] ?? null;
    }

    /**
     * Query model safely
     */
    public static function checkRelation(Model $model, string $package, string $relationType)
    {
        if (!self::isInstalled($package)) {
            return $model->$relationType($model::class, 'id', 'id')->whereRaw('1 = 0');
        }

        return null;
    }
}
