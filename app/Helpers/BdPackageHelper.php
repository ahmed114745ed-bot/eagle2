<?php

namespace App\Helpers;

use App\Support\PackageHelper;

/**
 * BdPackageHelper
 *
 * Helper للتعامل الآمن مع حزمة BD
 */
class BdPackageHelper
{
    /**
     * الحصول على BD class بشكل آمن
     */
    public static function getBdClass(): ?string
    {
        if (!PackageHelper::isInstalled('bd')) {
            return null;
        }

        if (class_exists(\Utd\Bd\Entities\Bd::class)) {
            return \Utd\Bd\Entities\Bd::class;
        }

        return null;
    }

    /**
     * البحث عن BD
     */
    public static function find($id)
    {
        $bdClass = self::getBdClass();

        if (!$bdClass) {
            return null;
        }

        return $bdClass::find($id);
    }

    /**
     * البحث عن BD الافتراضي
     */
    public static function findDefault(?int $countryId = null)
    {
        $bdClass = self::getBdClass();

        if (!$bdClass) {
            return null;
        }

        $query = $bdClass::where('default', 1);

        if ($countryId) {
            $query->where('country_id', $countryId);
        }

        return $query->first();
    }

    /**
     * البحث عن BD حسب الدولة
     */
    public static function findByCountry(int $countryId)
    {
        $bdClass = self::getBdClass();

        if (!$bdClass) {
            return null;
        }

        return $bdClass::where('country_id', $countryId)
            ->where('default', 1)
            ->first();
    }

    /**
     * جلب كل BDs
     */
    public static function all()
    {
        $bdClass = self::getBdClass();

        if (!$bdClass) {
            return collect([]);
        }

        return $bdClass::all();
    }

    /**
     * جلب BDs بشروط معينة
     */
    public static function where($column, $value = null)
    {
        $bdClass = self::getBdClass();

        if (!$bdClass) {
            return collect([]);
        }

        if (is_callable($column)) {
            return $bdClass::query()->where($column)->get();
        }

        return $bdClass::where($column, $value)->get();
    }

    /**
     * التحقق من تثبيت BD
     */
    public static function isInstalled(): bool
    {
        return PackageHelper::isInstalled('bd');
    }

    /**
     * الحصول على options للـ select field
     */
    public static function getOptions(bool $includeEmpty = false): array
    {
        if (!self::isInstalled()) {
            return [];
        }

        $bdClass = self::getBdClass();

        if (!$bdClass) {
            return [];
        }

        $options = [];

        if ($includeEmpty) {
            $options[''] = __('Select BD');
        }

        foreach ($bdClass::all() as $bd) {
            $options[$bd->id] = $bd->name ?? $bd->username ?? "BD #{$bd->id}";
        }

        return $options;
    }

    /**
     * تنسيق بيانات BD بشكل آمن
     */
    public static function formatBd($bd): ?array
    {
        if (!$bd) {
            return null;
        }

        return [
            'id' => $bd->id ?? null,
            'name' => $bd->name ?? $bd->username ?? '',
            'username' => $bd->username ?? '',
            'country_id' => $bd->country_id ?? null,
            'default' => $bd->default ?? false,
        ];
    }

    /**
     * إنشاء query builder آمن
     */
    public static function query()
    {
        $bdClass = self::getBdClass();

        if (!$bdClass) {
            // إرجاع query builder فارغ
            return new class {
                public function where(...$args) { return $this; }
                public function find($id) { return null; }
                public function first() { return null; }
                public function get() { return collect([]); }
                public function select(...$args) { return $this; }
                public function __call($method, $args) { return $this; }
            };
        }

        return $bdClass::query();
    }
}
