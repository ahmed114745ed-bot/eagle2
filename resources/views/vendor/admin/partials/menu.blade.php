 <style>
     :root {
        --primary-color: {{ config('themes.primaryColor') ?: '#2563eb' }};
        --secondary-color: {{ config('themes.secondaryColor') ?: '#1f2937' }};
        --green-color: {{ config('themes.greenColor') ?: '#10b981' }};
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
        --crs-white-faint: rgba(255,255,255,0.06);
        --crs-white-faint-2: rgba(255,255,255,0.16);
        --crs-transition: 320ms;
        --crs-ease: cubic-bezier(0.25,0.8,0.25,1);
        --crs-font: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
    }
 </style>
<link rel="stylesheet" href="{{ asset('css/admin-menu.css') }}">



@php
    $uri = \Illuminate\Support\Arr::get($item, 'uri', '');
    $shouldHideBd = Str::startsWith($uri, 'bd/') && !Admin::user()->inRoles(['bd']);

    $renderedMenu = $renderedMenu ?? [];
    $itemId = $item['id'] ?? null;
@endphp

@if(!$shouldHideBd
    && Admin::user()->visible(\Illuminate\Support\Arr::get($item, 'roles', []))
    && Admin::user()->can(\Illuminate\Support\Arr::get($item, 'permission'))
    && (!is_null($itemId) && !in_array($itemId, $renderedMenu)))

    @php
        $renderedMenu[] = $itemId;
        $href = url()->isValidUrl($item['uri']) ? $item['uri'] : admin_url($item['uri']);
        $titleTranslation = 'admin.menu_titles.' . trim(str_replace(' ', '_', strtolower($item['title'])));
    @endphp

    @if(!isset($item['children']))
        <li class="crs-item" data-crs-id="{{ $itemId }}">
            <a href="{{ $href }}" class="crs-link crs-leaf">
                <i class="fa {{ $item['icon'] }} crs-icon" aria-hidden="true"></i>
                <span class="crs-title">{{ Lang::has($titleTranslation) ? __($titleTranslation) : admin_trans($item['title']) }}</span>
            </a>
        </li>
    @else
        <li class="crs-tree crs-item" data-crs-id="{{ $itemId }}">
            <a href="#" class="crs-link crs-toggle" role="button" aria-expanded="false" aria-controls="crs-sub-{{ $itemId }}">
                <i class="fa {{ $item['icon'] }} crs-icon" aria-hidden="true"></i>
                <span class="crs-title">{{ Lang::has($titleTranslation) ? __($titleTranslation) : admin_trans($item['title']) }}</span>
                <i class="fa fa-angle-left crs-arrow" aria-hidden="true"></i>
            </a>

            <ul id="crs-sub-{{ $itemId }}" class="crs-submenu" data-crs-parent="{{ $itemId }}">
                @foreach($item['children'] as $child)
                    @include('vendor.admin.partials.menu', [
                        'item' => $child,
                        'renderedMenu' => $renderedMenu
                    ])
                @endforeach
            </ul>
        </li>
    @endif
@endif
