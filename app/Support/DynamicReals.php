<?php

namespace App\Support;

use Illuminate\Support\Facades\Schema;
use Utd\Reals\Entities\Real;

class DynamicReals
{

    private static ?bool $packageAvailable = null;
    private static ?bool $tablesExist = null;


    private static function packageExists(): bool
    {
        if (self::$packageAvailable === null) {
            self::$packageAvailable = file_exists(base_path('packages/Utd/Reals/src/Entities/Real.php'));
        }
        return self::$packageAvailable;
    }

    private static function tablesExist(): bool
    {
        if (self::$tablesExist === null) {
            try {
                self::$tablesExist = Schema::hasTable('reals');
            } catch (\Throwable $e) {
                self::$tablesExist = false;
            }
        }
        return self::$tablesExist;
    }


    public static function resetCache(): void
    {
        self::$packageAvailable = null;
        self::$tablesExist = null;
    }


    public static function getRealClass(): ?string
    {
        if (self::packageExists() && class_exists('Utd\Reals\Entities\Real', false)) {
            return 'Utd\Reals\Entities\Real';
        }
        if (self::packageExists()) {
            try {
                if (class_exists('Utd\Reals\Entities\Real')) {
                    return 'Utd\Reals\Entities\Real';
                }
            } catch (\Throwable $e) {
                // Ignore
            }
        }
        if (class_exists('Modules\Reals\Entities\Real', false) || class_exists('Modules\Reals\Entities\Real')) {
            return 'Modules\Reals\Entities\Real';
        }
        return null;
    }


    public static function getRealUserLikeClass(): ?string
    {
        if (self::packageExists() && class_exists('Utd\Reals\Entities\RealUserLike')) {
            return 'Utd\Reals\Entities\RealUserLike';
        }
        if (class_exists('Modules\Reals\Entities\RealUserLike')) {
            return 'Modules\Reals\Entities\RealUserLike';
        }
        return null;
    }


    public static function getRealUserCommentClass(): ?string
    {
        if (self::packageExists() && class_exists('Utd\Reals\Entities\RealUserComment')) {
            return 'Utd\Reals\Entities\RealUserComment';
        }
        if (class_exists('Modules\Reals\Entities\RealUserComment')) {
            return 'Modules\Reals\Entities\RealUserComment';
        }
        return null;
    }


    public static function getReportRealsClass(): ?string
    {
        if (self::packageExists() && class_exists('Utd\Reals\Entities\ReportReals')) {
            return 'Utd\Reals\Entities\ReportReals';
        }
        if (class_exists('Modules\Reals\Entities\ReportReals')) {
            return 'Modules\Reals\Entities\ReportReals';
        }
        return null;
    }


    public static function getFfmpegServiceClass(): ?string
    {
        if (self::packageExists() && class_exists('Utd\Reals\Http\Services\FfmpegService')) {
            return 'Utd\Reals\Http\Services\FfmpegService';
        }
        if (class_exists('Modules\Reals\Http\Services\FfmpegService')) {
            return 'Modules\Reals\Http\Services\FfmpegService';
        }
        return null;
    }


    public static function getRealsServiceClass(): ?string
    {
        if (self::packageExists() && class_exists('Utd\Reals\Services\RealsService')) {
            return 'Utd\Reals\Services\RealsService';
        }
        if (class_exists('Modules\Reals\Http\Services\RealsService')) {
            return 'Modules\Reals\Http\Services\RealsService';
        }
        return null;
    }

    /**
     * Get InterventionImage class
     */
    public static function getInterventionImageClass(): ?string
    {
        if (self::packageExists() && class_exists('Utd\Reals\Http\Services\InterventionImage')) {
            return 'Utd\Reals\Http\Services\InterventionImage';
        }
        if (class_exists('Modules\Reals\Http\Services\InterventionImage')) {
            return 'Modules\Reals\Http\Services\InterventionImage';
        }
        return null;
    }

    /**
     * Check if Reals feature is available
     */
    public static function isAvailable(): bool
    {
        return self::packageExists() && self::tablesExist() && self::getRealClass() !== null;
    }

    /**
     * Get new Real model instance
     */
    public static function newReal()
    {
        $class = self::getRealClass();
        return $class ? new $class : null;
    }

    /**
     * Query Real model
     */
    public static function queryReal()
    {
        $class = self::getRealClass();
        return $class ? $class::query() : null;
    }

    /**
     * Get new FfmpegService instance
     */
    public static function newFfmpegService()
    {
        $class = self::getFfmpegServiceClass();
        return $class ? new $class : null;
    }

    /**
     * Get new RealsService instance
     */
    public static function newRealsService()
    {
        $class = self::getRealsServiceClass();
        return $class ? new $class : null;
    }

    /**
     * Get new InterventionImage instance
     */
    public static function newInterventionImage()
    {
        $class = self::getInterventionImageClass();
        return $class ? new $class : null;
    }

    /**
     * Query ReportReals model
     */
    public static function queryReportReals()
    {
        $class = self::getReportRealsClass();
        return $class ? $class::query() : null;
    }

    public static function modelExists(): bool
    {
        return class_exists(Real::class);
    }
}
