@php
    $uri = \Illuminate\Support\Arr::get($item, 'uri', '');
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
        // Pick current locale or first available
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
        // pick first permission if it's an array
        $permission = reset($permission);
    }

    $isVisible =
        !$shouldHideBd &&
        Admin::user()->visible($roles) &&
        Admin::user()->can($permission) &&
        (!is_null($itemId) && !in_array($itemId, $renderedMenu));

    $badgeCount = 0;

    $badgeConfig = [
        'form-requests' => fn() => \Illuminate\Support\Facades\Cache::remember('menu_badge_form_requests', 60, fn() => \Modules\Form\Entities\FormRequest::where('status', 'pending')->count()),
        'superadmin-banner-requests' => fn() => \Illuminate\Support\Facades\Cache::remember('menu_badge_banner_requests', 60, fn() => \Modules\SuperAdmin\Entities\SuperadminBannerRequest::where('status', 'pending')->count()),
        'country-requests' => fn() => \Illuminate\Support\Facades\Cache::remember('menu_badge_country_requests', 60, fn() => \App\Models\ChangeCountryRequest::where('status', 'pending')->count()),
    ];

    $getBadgeCount = function($uri) use ($badgeConfig) {
        foreach ($badgeConfig as $badgeUri => $countCallback) {
            if (Str::contains($uri, $badgeUri)) {
                return $countCallback();
            }
        }
        return 0;
    };

    $getChildrenBadgeCount = function($children) use ($getBadgeCount, &$getChildrenBadgeCount) {
        $total = 0;
        foreach ($children as $child) {
            $childUri = Arr::get($child, 'uri', '');
            $total += $getBadgeCount($childUri);

            if (isset($child['children']) && is_array($child['children'])) {
                $total += $getChildrenBadgeCount($child['children']);
            }
        }
        return $total;
    };

    if (isset($item['children']) && is_array($item['children'])) {
        $badgeCount = $getChildrenBadgeCount($item['children']);
    } else {
        $badgeCount = $getBadgeCount($uri);
    }

@endphp

@if(!$shouldHideBd && Admin::user()->visible(\Illuminate\Support\Arr::get($item, 'roles', [])) && Admin::user()->can(\Illuminate\Support\Arr::get($item, 'permission')))

@if(!isset($item['children']))
        <li>
            @if(url()->isValidUrl($item['uri']))
                <a href="{{ $item['uri'] }}" target="_blank">
            @else
                 <a href="{{ admin_url($item['uri']) }}">
            @endif
                <i class="fa {{$item['icon']}}"></i>
                @if (Lang::has($titleTranslation = 'admin.menu_titles.' . trim(str_replace(' ', '_', strtolower($item['title'])))))
                    <span>{{ __($titleTranslation) }}</span>
                @else
                    <span>{{ admin_trans($item['title']) }}</span>
                @endif
            </a>
        </li>
    @else
        <li class="treeview">
            <a href="#">
                <i class="fa {{ $item['icon'] }}"></i>
                @if (Lang::has($titleTranslation = 'admin.menu_titles.' . trim(str_replace(' ', '_', strtolower($item['title'])))))
                    <span>{{ __($titleTranslation) }}</span>
                @else
                    <span>{{ admin_trans($item['title']) }}</span>
                @endif
                <i class="fa fa-angle-left pull-right"></i>

            </a>
            <ul class="treeview-menu">
                @foreach($item['children'] as $item)
                    @include('admin::partials.menu', $item)
                @endforeach
            </ul>
        </li>
    @endif
    @endif


