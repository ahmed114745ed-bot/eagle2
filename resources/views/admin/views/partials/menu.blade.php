@php
    $anyChild = false;
    $permissionExists = false;
            $roles = \Illuminate\Support\Arr::get($item, 'roles', []);
            $roles = count($roles) > 0 ? $roles : null;


            if (!function_exists('getPermissions')){
                function getPermissions($child) {
                    if (!$child) {
                        return ['permission' => [], 'roles' => []];
                    }

                    $permissions = [
                        'permission' => Arr::get($child, 'permission') ? [Arr::get($child, 'permission')] : [],
                        'roles' => Arr::get($child, 'roles', [])
                    ];

                    // Check if there are children and merge their permissions and roles recursively
                    if (isset($child['children']) && is_array($child['children'])) {
                        foreach ($child['children'] as $subChild) {
                            $childPermissions = getPermissions($subChild);
                            $permissions['permission'] = array_merge($permissions['permission'], $childPermissions['permission']);
                            $permissions['roles'] = array_merge($permissions['roles'], $childPermissions['roles']);
                        }
                    }

                    return $permissions;
                }
                }
@endphp

@if(isset($item['children']))
    @php

        $data = getPermissions(@$item);

                    $rolesL = $data['roles'];
                    $rolesL = count($rolesL) > 0 ? $rolesL : null;

                    $isRoleVisible = $rolesL && Admin::user()->visible($rolesL);

                    foreach ($data['permission'] as $permission){

                        if (!$permission)  continue;


                        $permissionExists =   ( Admin::user()->can($permission));

                        if ($permissionExists) break;
                    }

                    if (@$permissionExists || $isRoleVisible ){
                        $anyChild = true;
                    }
    @endphp
@endif


@php
    $hasRoles = $roles && Admin::user()->visible($roles);
    $hasPermission = !empty(Arr::get($item, 'permission')) && Admin::user()->can(Arr::get($item, 'permission'));
    $anyChildExists = $anyChild ?? false;
    $allPermission = Admin::user()->can('*');
    $isVisible = ($hasRoles || $hasPermission|| $allPermission || $anyChildExists );


    // if (Arr::get($item, 'id') == '13'){
    //     dump(Admin::user()->can(Arr::get($item, 'permission')));

    //         dump($isVisible, $hasRoles , $hasPermission, $allPermission , $anyChildExists);
    //     }
@endphp

@php
    // Your existing PHP logic for permissions and roles...
    $isRtl = app()->getLocale() === 'ar' || config('app.direction') === 'rtl';
@endphp

<div class="modern-sidebar {{ $isRtl ? 'rtl' : 'ltr' }}">
    @if($isVisible)
        @if(!isset($item['children']))
            <div class="menu-item">
                <a href="{{ url()->isValidUrl($item['uri']) ? $item['uri'] : admin_url($item['uri']) }}"
                    {{ url()->isValidUrl($item['uri']) ? 'target="_blank"' : '' }}>
                    <i class="fa {{$item['icon']}}"></i>
                    <span class="menu-text">
                        @if (Lang::has($titleTranslation = 'admin.menu_titles.' . trim(str_replace(' ', '_', strtolower($item['title'])))))
                            {{ __($titleTranslation) }}
                        @else
                            {{ admin_trans($item['title']) }}
                        @endif
                    </span>
                </a>
            </div>
        @else
            <div class="menu-item has-submenu">
                <a href="#">
                    <i class="fa {{ $item['icon'] }}"></i>
                    <span class="menu-text">
                        @if (Lang::has($titleTranslation = 'admin.menu_titles.' . trim(str_replace(' ', '_', strtolower($item['title'])))))
                            {{ __($titleTranslation) }}
                        @else
                            {{ admin_trans($item['title']) }}
                        @endif
                    </span>
                    <i class="fa fa-angle-{{ $isRtl ? 'left' : 'right' }} submenu-indicator"></i>
                </a>
                <div class="submenu-popup">
                    @foreach($item['children'] as $subItem)
                        @php
                            $hasSubChildren = isset($subItem['children']) && count($subItem['children']) > 0;
                        @endphp
                        <div class="menu-item {{ $hasSubChildren ? 'has-submenu' : '' }}">
                            <a href="{{ $hasSubChildren ? '#' : (url()->isValidUrl($subItem['uri']) ? $subItem['uri'] : admin_url($subItem['uri'])) }}">
                                <i class="fa {{ $subItem['icon'] ?? 'fa-circle' }}"></i>
                                <span class="menu-text">
                                    @if (Lang::has($subTitleTranslation = 'admin.menu_titles.' . trim(str_replace(' ', '_', strtolower($subItem['title'])))))
                                        {{ __($subTitleTranslation) }}
                                    @else
                                        {{ admin_trans($subItem['title']) }}
                                    @endif
                                </span>
                                @if($hasSubChildren)
                                    <i class="fa fa-angle-{{ $isRtl ? 'left' : 'right' }} submenu-indicator"></i>
                                @endif
                            </a>
                            @if($hasSubChildren)
                                <div class="submenu-popup submenu-level-2">
                                    @foreach($subItem['children'] as $subSubItem)
                                        <div class="menu-item">
                                            <a href="{{ url()->isValidUrl($subSubItem['uri']) ? $subSubItem['uri'] : admin_url($subSubItem['uri']) }}">
                                                <i class="fa {{ $subSubItem['icon'] ?? 'fa-circle' }}"></i>
                                                <span class="menu-text">
                                                    @if (Lang::has($subSubTitleTranslation = 'admin.menu_titles.' . trim(str_replace(' ', '_', strtolower($subSubItem['title'])))))
                                                        {{ __($subSubTitleTranslation) }}
                                                    @else
                                                        {{ admin_trans($subSubItem['title']) }}
                                                    @endif
                                                </span>
                                            </a>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    @endif
</div>

<style>
    .modern-sidebar.ltr .menu-item > a {
        display: flex;
        align-items: center;
        color: #fff;
        text-decoration: none;
        padding: 12px 20px;
        font-weight: 500;
    }

    .modern-sidebar.ltr .menu-item i {
        margin-right: 10px;
        font-size: 16px;
        width: 20px;
        text-align: center;
    }

    .modern-sidebar.ltr .submenu-popup {
        position: absolute;
        top: 0;
        left: 100%;
        width: 220px;
        background-color: #00b09b;
        padding: 8px 0;
        opacity: 0;
        visibility: hidden;
        transition: opacity 0.2s ease, visibility 0.2s ease;
        z-index: 100;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    }

    .modern-sidebar.ltr .submenu-indicator {
        margin-left: auto;
    }

    .modern-sidebar.ltr .menu-item.has-submenu > a:after {
        content: '';
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        border-top: 5px solid transparent;
        border-bottom: 5px solid transparent;
        border-left: 5px solid #fff;
        transition: transform 0.2s;
    }

    .modern-sidebar.ltr .menu-item.has-submenu > a:after {
        border-left-color: var(--primary-color);
    }

    .fa-angle-right:before{
        content: none; !important;
    }

    .fa-angle-left:before{
        content: none; !important;
    }

    .modern-sidebar.rtl {
        direction: rtl;
        text-align: right;
    }

    .modern-sidebar.rtl .menu-item > a {
        display: flex;
        align-items: center;
        color: #fff;
        text-decoration: none;
        padding: 12px 20px;
        font-weight: 500;
    }

    .modern-sidebar.rtl .menu-item i {
        margin-left: 10px;
        margin-right: 0;
        font-size: 16px;
        width: 20px;
        text-align: center;
    }

    .modern-sidebar.rtl .submenu-popup {
        position: absolute;
        top: 0;
        right: 100%;
        left: auto;
        width: 220px;
        background-color: #00b09b;
        padding: 8px 0;
        opacity: 0;
        visibility: hidden;
        transition: opacity 0.2s ease, visibility 0.2s ease;
        z-index: 100;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    }

    .modern-sidebar.rtl .submenu-indicator {
        margin-right: auto;
        margin-left: 0;
    }

    .modern-sidebar.ltr .menu-item.has-submenu > a:after {
        content: '';
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        border-top: 5px solid transparent;
        border-bottom: 5px solid transparent;
        border-left: 5px solid var(--primary-color);
        transition: transform 0.2s;
    }

    .modern-sidebar.rtl .menu-item.has-submenu > a:after {
        content: '';
        position: absolute;
        left: 10px;
        right: auto;
        top: 50%;
        transform: translateY(-50%);
        border-top: 5px solid transparent;
        border-bottom: 5px solid transparent;
        border-right: 5px solid var(--primary-color);
        border-left: none;
        transition: transform 0.2s;
    }

    .menu-item {
        position: relative;
        transition: all 0.3s ease;
    }

    .menu-text {
        font-size: 14px;
    }

    .has-submenu {
        position: relative;
    }

    .has-submenu:hover > .submenu-popup {
        opacity: 1;
        visibility: visible;
    }

    .submenu-level-2 {
        background-color: #00c4aa;
    }

    .menu-item .has-submenu .submenu-popup {
        opacity: 0;
        visibility: hidden;
    }

    .menu-item .has-submenu:hover > .submenu-popup {
        opacity: 1;
        visibility: visible;
    }

    .menu-item:hover > a {
        background-color: rgba(255, 255, 255, 0.1);
    }

    @media (max-width: 768px) {
        .submenu-popup {
            position: static !important;
            width: 100% !important;
            box-shadow: none !important;
            background-color: transparent !important;
        }

        .modern-sidebar.ltr .submenu-popup {
            padding-left: 20px;
        }

        .modern-sidebar.rtl .submenu-popup {
            padding-right: 20px;
        }
    }
</style>
