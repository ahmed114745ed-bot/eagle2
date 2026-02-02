<style>
    :root {
        --primary-color: {{ config('themes.primaryColor') ?: '#2563eb' }};
        --secondary-color: {{ config('themes.secondaryColor') ?: '#1f2937' }};
        --text-primary-color: {{ config('themes.textPrimaryColor') ?: '#ffffff' }};
        --text-secondary-color: {{ config('themes.textSecondaryColor') ?: '#9ca3af' }};
        --box-background-color: {{ config('themes.boxBackgroundColor') ?: '#ffffff' }};
        --table-background-color: {{ config('themes.tableBackGroundColor') ?: '#f9fafb' }};
        --background-image: {{ config('themes.backgroundImage') ?: 'none' }};
        --brand_background-image: url({{ getImagePath(config('themes.brandBackgroundImage')) ?: '' }});
        --second-alpha: rgba(31, 41, 55, 0.1);
        --primary-hover-alpha: rgba(37, 99, 235, 0.1);
        --scroll-second-color: rgba(255, 255, 255, 0.8);
        --scroll-first-color: rgba(37, 99, 235, 0.2);

        --inverse-color: #ffffff;
        --inverse-box-color: #1f2937;
        --success-button: linear-gradient(135deg, #10b981 0%, #059669 100%);
        --primary-button: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);

        /* Additional unified color variables */
        --white: #ffffff;
        --gray-800: #1f2937;
        --gray-700: #374151;
        --gray-50: #f9fafb;
        --gray-200: #e5e7eb;
        --gray-300: #d1d5db;
        --gray-900: #111827;

        /* Modern Design Variables */
        --sidebar-width: 280px;
        --header-height: 70px;
        --border-radius: 12px;
        --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        --crs-red-hover: #e74c3c;
        --crs-white-faint: rgba(255, 255, 255, 0.06);
        --crs-white-faint-2: rgba(255, 255, 255, 0.16);
        --crs-transition: 320ms;
        --crs-ease: cubic-bezier(0.25, 0.8, 0.25, 1);
        --crs-font: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
    }
</style>


@php
    use Illuminate\Support\Arr;
    use Illuminate\Support\Str;
    use Illuminate\Support\Facades\Cache;

    /* -------------------------------
     | URI / BD Visibility
     |-------------------------------*/
    $uri = Arr::get($item, 'uri', '');
    $shouldHideBd = Str::startsWith($uri, 'bd/') && !Admin::user()->inRoles(['bd']);

    /* -------------------------------
     | Prevent duplicate rendering
     |-------------------------------*/
    $renderedMenu = $renderedMenu ?? [];
    $itemId = $item['id'] ?? null;

    /* -------------------------------
     | Normalize title
     |-------------------------------*/
    $rawTitle = Arr::get($item, 'title', '');
    if (is_array($rawTitle)) {
        $rawTitle = $rawTitle[app()->getLocale()] ?? reset($rawTitle);
    }
    $normalizedTitle = is_string($rawTitle) ? $rawTitle : '';

    /* -------------------------------
     | Normalize roles
     |-------------------------------*/
    $roles = Arr::get($item, 'roles', []);
    if (!is_array($roles)) {
        $roles = [];
    }

    /* -------------------------------
     | Normalize permission
     |-------------------------------*/
    $permission = Arr::get($item, 'permission');
    if (is_array($permission)) {
        $permission = reset($permission);
    }

    /* -------------------------------
     | CHILD VISIBILITY CHECK
     |-------------------------------*/
    $hasVisibleChild = false;

    if (isset($item['children']) && is_array($item['children'])) {
        foreach ($item['children'] as $child) {
            $childRoles = Arr::get($child, 'roles', []);
            $childPermission = Arr::get($child, 'permission');

            if (is_array($childPermission)) {
                $childPermission = reset($childPermission);
            }

            if (
                Admin::user()->visible($childRoles) &&
                (
                    empty($childPermission) ||
                    Admin::user()->can($childPermission)
                )
            ) {
                $hasVisibleChild = true;
                break;
            }
        }
    }

    /* -------------------------------
     | FINAL VISIBILITY RULE
     |-------------------------------*/
    $isVisible =
        !$shouldHideBd &&
        Admin::user()->visible($roles) &&
        (
            !isset($item['children'])
                ? (empty($permission) || Admin::user()->can($permission))
                : $hasVisibleChild
        ) &&
        (!is_null($itemId) && !in_array($itemId, $renderedMenu));

    /* -------------------------------
     | BADGE LOGIC
     |-------------------------------*/
    $badgeCount = 0;

    $badgeConfig = [
        'form-requests' => fn() => Cache::remember('menu_badge_form_requests', 60, fn() =>
            \Modules\Form\Entities\FormRequest::where('status', 'pending')->count()
        ),
        'superadmin-banner-requests' => fn() => Cache::remember('menu_badge_banner_requests', 60, fn() =>
            \Modules\SuperAdmin\Entities\SuperadminBannerRequest::where('status', 'pending')->count()
        ),
        'country-requests' => fn() => Cache::remember('menu_badge_country_requests', 60, fn() =>
            \App\Models\ChangeCountryRequest::where('status', 'pending')->count()
        ),
    ];

    $getBadgeCount = function ($uri) use ($badgeConfig) {
        foreach ($badgeConfig as $badgeUri => $callback) {
            if (Str::contains($uri, $badgeUri)) {
                return $callback();
            }
        }
        return 0;
    };

    $getChildrenBadgeCount = function ($children) use (&$getChildrenBadgeCount, $getBadgeCount) {
        $total = 0;
        foreach ($children as $child) {
            $total += $getBadgeCount(Arr::get($child, 'uri', ''));
            if (isset($child['children'])) {
                $total += $getChildrenBadgeCount($child['children']);
            }
        }
        return $total;
    };

    if (isset($item['children'])) {
        $badgeCount = $getChildrenBadgeCount($item['children']);
    } else {
        $badgeCount = $getBadgeCount($uri);
    }
@endphp