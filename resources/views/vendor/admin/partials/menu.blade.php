@php
    $uri = \Illuminate\Support\Arr::get($item, 'uri', '');
    $shouldHideBd = Str::startsWith($uri, 'bd/') && !Admin::user()->inRoles(['bd']);
@endphp

@if(!$shouldHideBd 
    && Admin::user()->visible(\Illuminate\Support\Arr::get($item, 'roles', [])) 
    && Admin::user()->can(\Illuminate\Support\Arr::get($item, 'permission')))
    
    @if(!isset($item['children']))
        <li class="menu-item">
            @php
                $href = url()->isValidUrl($item['uri']) ? $item['uri'] : admin_url($item['uri']);
            @endphp
            <a href="{{ $href }}" class="menu-link">
                <i class="fa {{ $item['icon'] }} menu-icon"></i>
                @php
                    $titleTranslation = 'admin.menu_titles.' . trim(str_replace(' ', '_', strtolower($item['title'])));
                @endphp
                <span class="menu-title">
                    {{ Lang::has($titleTranslation) ? __($titleTranslation) : admin_trans($item['title']) }}
                </span>
            </a>
        </li>
    @else
        <li class="treeview menu-item">
            <a href="#" class="treeview-toggle menu-link">
                <i class="fa {{ $item['icon'] }} menu-icon"></i>
                @php
                    $titleTranslation = 'admin.menu_titles.' . trim(str_replace(' ', '_', strtolower($item['title'])));
                @endphp
                <span class="menu-title">
                    {{ Lang::has($titleTranslation) ? __($titleTranslation) : admin_trans($item['title']) }}
                </span>
                <i class="fa fa-angle-left pull-right treeview-arrow"></i>
            </a>

            <ul class="treeview-menu collapse">
                @foreach($item['children'] as $child)
                    @include('admin::partials.menu', $child)
                @endforeach
            </ul>
        </li>
    @endif
@endif
