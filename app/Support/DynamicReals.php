<?php

namespace App\Support;

/**
 * Dynamic Reals Helper
 * 
 * يوفر طريقة آمنة للوصول إلى كلاسات الريلز
 * يعمل بشكل طبيعي عند وجود الحزمة
 * ويرجع null أو fallback عند عدم وجودها
 */
class DynamicReals
{
    /**
     * Get Real Entity class
     */
    public static function getRealClass(): ?string
    {
        // Try new package first
        if (class_exists(\Utd\Reals\Entities\Real::class)) {
            return \Utd\Reals\Entities\Real::class;
        }
        // Fallback to old module
        if (class_exists(\Modules\Reals\Entities\Real::class)) {
            return \Modules\Reals\Entities\Real::class;
        }
        return null;
    }

    /**
     * Get RealUserLike Entity class
     */
    public static function getRealUserLikeClass(): ?string
    {
        if (class_exists(\Utd\Reals\Entities\RealUserLike::class)) {
            return \Utd\Reals\Entities\RealUserLike::class;
        }
        if (class_exists(\Modules\Reals\Entities\RealUserLike::class)) {
            return \Modules\Reals\Entities\RealUserLike::class;
        }
        return null;
    }

    /**
     * Get RealUserComment Entity class
     */
    public static function getRealUserCommentClass(): ?string
    {
        if (class_exists(\Utd\Reals\Entities\RealUserComment::class)) {
            return \Utd\Reals\Entities\RealUserComment::class;
        }
        if (class_exists(\Modules\Reals\Entities\RealUserComment::class)) {
            return \Modules\Reals\Entities\RealUserComment::class;
        }
        return null;
    }

    /**
     * Get ReportReals Entity class
     */
    public static function getReportRealsClass(): ?string
    {
        if (class_exists(\Utd\Reals\Entities\ReportReals::class)) {
            return \Utd\Reals\Entities\ReportReals::class;
        }
        if (class_exists(\Modules\Reals\Entities\ReportReals::class)) {
            return \Modules\Reals\Entities\ReportReals::class;
        }
        return null;
    }

    /**
     * Get FfmpegService class
     */
    public static function getFfmpegServiceClass(): ?string
    {
        if (class_exists(\Utd\Reals\Http\Services\FfmpegService::class)) {
            return \Utd\Reals\Http\Services\FfmpegService::class;
        }
        if (class_exists(\Modules\Reals\Http\Services\FfmpegService::class)) {
            return \Modules\Reals\Http\Services\FfmpegService::class;
        }
        return null;
    }

    /**
     * Get RealsService class
     */
    public static function getRealsServiceClass(): ?string
    {
        if (class_exists(\Utd\Reals\Services\RealsService::class)) {
            return \Utd\Reals\Services\RealsService::class;
        }
        if (class_exists(\Modules\Reals\Http\Services\RealsService::class)) {
            return \Modules\Reals\Http\Services\RealsService::class;
        }
        return null;
    }

    /**
     * Get InterventionImage class
     */
    public static function getInterventionImageClass(): ?string
    {
        if (class_exists(\Utd\Reals\Http\Services\InterventionImage::class)) {
            return \Utd\Reals\Http\Services\InterventionImage::class;
        }
        if (class_exists(\Modules\Reals\Http\Services\InterventionImage::class)) {
            return \Modules\Reals\Http\Services\InterventionImage::class;
        }
        return null;
    }

    /**
     * Check if Reals feature is available
     */
    public static function isAvailable(): bool
    {
        return self::getRealClass() !== null;
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
}
