<aside id="main-sidebar" class="main-sidebar">
<link rel="stylesheet" href="{{ asset('css/admin-menu.css') }}">
<script src="{{ asset('js/admin-menu.js') }}"></script></section>
    <section class="sidebar">



        @if(config('admin.enable_menu_search'))
            <!-- search form (Optional) -->
            <form class="sidebar-form" style="overflow: initial;" onsubmit="return false;">
                <div class="input-group">
                    <input type="text" autocomplete="off" class="form-control autocomplete" placeholder="Search...">
                    <span class="input-group-btn">
                <button type="submit" name="search" id="search-btn" class="btn btn-flat"><i>🔍</i>
                </button>
              </span>
                    <ul class="dropdown-menu" role="menu" style="min-width: 210px;max-height: 300px;overflow: auto;">
                        @if (Admin::user()->type == 'bd')
                            @foreach(Admin::menuLinks() as $link)
                                <li>
                                    <a href="{{ bd_url($link['uri']) }}"><i
                                            class="fa {{ $link['icon'] }}"></i>{{ admin_trans($link['title']) }}</a>
                                </li>
                            @endforeach
                        @endif

                        @if (Admin::user()->type == 'superadmin')
                            @foreach(Admin::menuLinks() as $link)
                                <li>
                                    <a href="{{ superadmin_url($link['uri']) }}"><i
                                            class="fa {{ $link['icon'] }}"></i>{{ admin_trans($link['title']) }}</a>
                                </li>
                            @endforeach
                        @endif

                        @if (Admin::user()->type != 'bd' || Admin::user()->type != 'superadmin')
                            @foreach(Admin::menuLinks() as $link)
                                <li>
                                    <a href="{{ admin_url($link['uri']) }}"><i
                                            class="fa {{ $link['icon'] }}"></i>{{ admin_trans($link['title']) }}</a>
                                </li>
                            @endforeach
                        @endif
                    </ul>
                </div>
            </form>
            <!-- /.search form -->
        @endif

        @php
            use Illuminate\Support\Str;
            $menu = Admin::menu();
            $filteredMenu = collect($menu)->filter(function ($item) {
                if ((Str::startsWith($item['uri'] ?? '', 'bd') || ($item['uri'] ?? '') === '*') && !Admin::user()->type == 'bd') {
                    return false;
                }
                return true;
            })->values()->all();
        @endphp

        <ul class="crs-menu">
            <!-- <li class="header">{{ trans('admin.menu') }}</li> -->

            @if (Admin::user()->type == 'bd')
                @php
                    $bdLinks = [
                        ['uri' => '/', 'icon' => '🏠', 'title' => __('Home')],
                        ['uri' => '/charges', 'icon' => '💸', 'title' => __('charges')],
                        ['uri' => '/agencies', 'icon' => '🏠', 'title' => __('agencies')],
                        ['uri' => '/salaries', 'icon' => 'fa-building', 'title' => __('salaries')],
                        ['uri' => '/request-agencies', 'icon' => 'fa-building', 'title' => __('request-agencies')],
                    ];
                @endphp

                @foreach($bdLinks as $link)
                    <li class="crs-item">
                        <a href="{{ bd_url($link['uri']) }}" class="crs-link crs-leaf">
                            @if(str_contains($link['icon'] ?? '', 'fa-'))
                                <i class="fa {{ $link['icon'] }} crs-icon" aria-hidden="true"></i>
                            @else
                                <span class="crs-icon emoji-icon">{{ $link['icon'] }}</span>
                            @endif
                            <span class="crs-title">{{ $link['title'] }}</span>
                        </a>
                    </li>
                @endforeach
            @endif

            @if (in_array(Admin::user()->type, ['superadmin', 'sub_super_admin']))
                @php
                    $superadminLinks = [
                        ['uri' => '/', 'icon' => '🏠', 'title' => __('Dashboard'), 'permission' => 'dashboard'],
                        ['uri' => '/users', 'icon' => '👥', 'title' => __('Users'), 'permission' => 'users'],
                        ['uri' => '/charges', 'icon' => '💸', 'title' => __('charges'), 'permission' => 'coin-recharge']
                        ,
                        [
                            'uri' => '#',
                            'icon' => '👨‍💼',
                            'title' => __('BD'),
                            'permission' => null,
                            'children' => [
                                ['uri' => '/usersBd', 'icon' => '💼', 'title' => __('BD'), 'permission' => 'Bds'],
                                ['uri' => '/professional-bd', 'icon' => '✈️', 'title' => __('Professional BD'), 'permission' => 'professional-bd'],
                            ],
                        ],
                        [
                            'uri' => '#',
                            'icon' => '🏢',
                            'title' => __('Agencies'),
                            'permission' => null,
                            'children' => [
                                ['uri' => '/agencies', 'icon' => '🏠', 'title' => __('Host Agencies'), 'permission' => 'agency'],
                                ['uri' => '/charge-agencies', 'icon' => '💳', 'title' => __('Shipping Agencies'), 'permission' => 'shipping-agency'],
                                ['uri' => '/ag/users', 'icon' => '👥', 'title' => __('Hosts'), 'permission' => 'host'],
                                ['uri' => '/ag/professional/users', 'icon' => '✈️', 'title' => __('Professional Host'), 'permission' => 'professional-users'],
                            ],
                        ],
                        [
                            'uri' => '#',
                            'icon' => '🏠',
                            'title' => __('rooms'),
                            'permission' => null,
                            'children' => [
                                ['uri' => '/rooms', 'icon' => '🚪', 'title' => __('rooms'), 'permission' => 'rooms'],
                                ['uri' => '/live-rooms', 'icon' => '📺', 'title' => __('Live Rooms'), 'permission' => 'live-rooms'],
                            ],
                        ],
                        [
                            'uri' => '#',
                            'icon' => '📢',
                            'title' => __('Advertisements'),
                            'permission' => null,
                            'children' => [
                                ['uri' => '/home-carousel', 'icon' => '🎠', 'title' => __('HomeCarousel'), 'permission' => 'banner'],
                                ['uri' => '/official-message', 'icon' => '📋', 'title' => __('Official messages'), 'permission' => 'banner'],

                            ],
                        ],
                        ['uri' => '/super-admin-rewards', 'icon' => '🎁', 'title' => __('reward dedicate'), 'permission' => 'reward-center'],
                        [
                            'uri' => '#',
                            'icon' => '👔',
                            'title' => __('Employees and Permissions'),
                            'permission' => null,
                            'children' => [
                                ['uri' => '/roles', 'icon' => '🔐', 'title' => __('roles'), 'permission' => 'roles'],
                                ['uri' => '/auth-users', 'icon' => '👥', 'title' => __('Sub Super Admin'), 'permission' => 'auth-users'],
                            ],
                        ],
                    ];

                    function hasPermission($permission) {
                        if (Admin::user()->can('*')) {
                            return true;
                        }

                        if (is_null($permission)) {
                            return true;
                        }

                        return Admin::user()->can('browse-' . $permission);
                    }

                    function hasVisibleChildren($children) {
                        foreach ($children as $child) {
                            if (hasPermission($child['permission'] ?? null)) {
                                return true;
                            }
                        }
                        return false;
                    }
                @endphp

                @foreach($superadminLinks as $link)

                    @if(isset($link['children']))
                        @if(hasVisibleChildren($link['children']))
                            <li class="crs-tree crs-item">
                                <a href="#" class="crs-link crs-toggle">
                                    @if(str_contains($link['icon'] ?? '', 'fa-'))
                                        <i class="fa {{ $link['icon'] }} crs-icon" aria-hidden="true"></i>
                                    @else
                                        <span class="crs-icon emoji-icon">{{ $link['icon'] }}</span>
                                    @endif
                                    <span class="crs-title">{{ $link['title'] }}</span>
                                    <i class="fa fa-angle-left crs-arrow"></i>
                                </a>
                                <ul class="crs-submenu">
                                    @foreach($link['children'] as $child)
                                        @if(hasPermission($child['permission'] ?? null))
                                            <li class="crs-item">
                                                <a href="{{ superadmin_url($child['uri']) }}" class="crs-link crs-leaf">
                                                    @if(str_contains($child['icon'] ?? '', 'fa-'))
                                                        <i class="fa {{ $child['icon'] }} crs-icon" aria-hidden="true"></i>
                                                    @else
                                                        <span class="crs-icon emoji-icon">{{ $child['icon'] }}</span>
                                                    @endif
                                                    <span class="crs-title">{{ $child['title'] }}</span>
                                                </a>
                                            </li>
                                        @endif
                                    @endforeach
                                </ul>
                            </li>
                        @endif
                    @else
                        @if(hasPermission($link['permission'] ?? null))
                            <li class="crs-item">
                                <a href="{{ superadmin_url($link['uri']) }}" class="crs-link crs-leaf">
                                    @if(str_contains($link['icon'] ?? '', 'fa-'))
                                        <i class="fa {{ $link['icon'] }} crs-icon" aria-hidden="true"></i>
                                    @else
                                        <span class="crs-icon emoji-icon">{{ $link['icon'] }}</span>
                                    @endif
                                    <span class="crs-title">{{ $link['title'] }}</span>
                                </a>
                            </li>
                        @endif
                    @endif
                @endforeach
            @endif
            {{--                area manager--}}
            @if (in_array(Admin::user()->type, ['area-manager', 'sub_area_manager']) )
                @php
                    $areaManagerLinks = [
                        ['uri' => '/', 'icon' => '', 'title' => __('Dashboard'), 'permission' => 'dashboard'],
                        [
                            'uri' => '#',
                            'icon' => 'fa-users',
                            'title' => __('Super Admin'),
                            'permission' => null,
                            'children' => [
                                ['uri' => '/superadmin-users', 'icon' => 'fa-users', 'title' => __('Super Admin'), 'permission' => 'superadmin'],
                            ],
                        ],
                        ['uri' => '/charges', 'icon' => '💸', 'title' => __('charges'), 'permission' => 'coin-recharge'],
                        [
                            'uri' => '#',
                            'icon' => 'fa-briefcase',
                            'title' => __('BD'),
                            'permission' => null,
                            'children' => [
                                ['uri' => '/user-Bds', 'icon' => 'fa-briefcase', 'title' => __('BD'), 'permission' => 'Bds'],
                                ['uri' => '/professional-bd', 'icon' => 'fa-plane', 'title' => __('Professional BD'), 'permission' => 'professional-bd'],
                            ],
                        ],
                        ['uri' => '/users', 'icon' => '👥', 'title' => __('Users'), 'permission' => 'users'],
                        [
                            'uri' => '#',
                            'icon' => 'fa-building',
                            'title' => __('Agencies'),
                            'permission' => null,
                            'children' => [
                                ['uri' => '/agencies', 'icon' => '🏠', 'title' => __('Host Agencies'), 'permission' => 'agency'],
                                ['uri' => '/charge-agencies', 'icon' => '💳', 'title' => __('Shipping Agencies'), 'permission' => 'shipping-agency'],
                                ['uri' => '/ag/users', 'icon' => '👥', 'title' => __('Hosts'), 'permission' => 'host'],
                                ['uri' => '/ag/professional/users', 'icon' => '✈️', 'title' => __('Professional Host'), 'permission' => 'professional-users'],
                            ],
                        ],
                        [
                            'uri' => '#',
                            'icon' => 'fa-building',
                            'title' => __('rooms'),
                            'permission' => null,
                            'children' => [
                                ['uri' => '/rooms', 'icon' => '🚪', 'title' => __('rooms'), 'permission' => 'rooms'],
                                ['uri' => '/live-rooms', 'icon' => '📺', 'title' => __('Live Rooms'), 'permission' => 'live-rooms'],
                            ],
                        ],
                        [
                            'uri' => '#',
                            'icon' => '📢',
                            'title' => __('Advertisements'),
                            'permission' => null,
                            'children' => [
                                ['uri' => '/official-message', 'icon' => '📋', 'title' => __('Official messages'), 'permission' => 'official-messages'],
                            ],
                        ],
                        [
                            'uri' => '#',
                            'icon' => '',
                            'title' => __('Employees and Permissions'),
                            'permission' => null,
                            'children' => [
                                ['uri' => '/roles', 'icon' => '', 'title' => __('roles'), 'permission' => 'roles'],
                                ['uri' => '/auth-users', 'icon' => '', 'title' => __('users'), 'permission' => 'auth-users'],
                            ],
                        ],
                    ];

                    function hasPermission($permission) {
                        if (Admin::user()->can('*')) return true;
                        if (is_null($permission)) return true;
                        return Admin::user()->can('browse-' . $permission);
                    }

                    function hasVisibleChildren($children) {
                        foreach ($children as $child) {
                            if (hasPermission($child['permission'] ?? null)) {
                                return true;
                            }
                        }
                        return false;
                    }
                @endphp

                @foreach($areaManagerLinks as $link)
                    @if(isset($link['children']))
                        @if(hasVisibleChildren($link['children']))
                            <li class="crs-tree crs-item">
                                <a href="#" class="crs-link crs-toggle">
                                    @if(str_contains($link['icon'] ?? '', 'fa-'))
                                        <i class="fa {{ $link['icon'] }} crs-icon" aria-hidden="true"></i>
                                    @else
                                        <span class="crs-icon emoji-icon">{{ $link['icon'] }}</span>
                                    @endif
                                    <span class="crs-title">{{ $link['title'] }}</span>
                                    <i class="fa fa-angle-left crs-arrow"></i>
                                </a>
                                <ul class="crs-submenu">
                                    @foreach($link['children'] as $child)
                                        @if(hasPermission($child['permission'] ?? null))
                                            <li class="crs-item">
                                                <a href="{{ areaManager_url($child['uri']) }}" class="crs-link crs-leaf">
                                                    @if(str_contains($child['icon'] ?? '', 'fa-'))
                                                        <i class="fa {{ $child['icon'] }} crs-icon" aria-hidden="true"></i>
                                                    @else
                                                        <span class="crs-icon emoji-icon">{{ $child['icon'] }}</span>
                                                    @endif
                                                    <span class="crs-title">{{ $child['title'] }}</span>
                                                </a>
                                            </li>
                                        @endif
                                    @endforeach
                                </ul>
                            </li>
                        @endif
                    @else
                        @if(hasPermission($link['permission'] ?? null))
                            <li class="crs-item">
                                <a href="{{ areaManager_url($link['uri']) }}" class="crs-link crs-leaf">
                                    @if(str_contains($link['icon'] ?? '', 'fa-'))
                                        <i class="fa {{ $link['icon'] }} crs-icon" aria-hidden="true"></i>
                                    @else
                                        <span class="crs-icon emoji-icon">{{ $link['icon'] }}</span>
                                    @endif
                                    <span class="crs-title">{{ $link['title'] }}</span>
                                </a>
                            </li>
                        @endif
                    @endif
                @endforeach
            @endif

            @php
                $adminTypes = ['bd', 'superadmin', 'sub_super_admin', 'area-manager', 'sub_area_manager'];
            @endphp

            @if (!in_array(Admin::user()->type, $adminTypes) && !session('preview_superadmin') &&!session('preview_area_manager'))
                @each('vendor.admin.partials.menu', $filteredMenu, 'item')
            @elseif(session('preview_superadmin'))
                @php
                    $superadminPreviewLinks = [
                        ['uri' => '/superadmin/statistics','icon' => '🏠','title' => __('Dashboard')],
                        ['uri' => '/superadmin/profile','icon' => '','title' => __('Super Admin Profile')],
                        ['uri' => '/users','icon' => '👥','title' => __('Users')],
                        ['uri' => '/usersBd','icon' => '💼','title' => __('BD')],
                        [
                            'uri' => '#',
                            'icon' => 'fa-building',
                            'title' => __('Agencies'),
                            'children' => [
                                ['uri' => '/agencies', 'icon' => '🏠', 'title' => __('Host Agencies')],
                                ['uri' => '/charge-agencies', 'icon' => '💳', 'title' => __('Shipping Agencies')],
                                ['uri' => '/ag/users', 'icon' => '👥', 'title' => __('Hosts')],
                                ['uri' => '/ag/professional/users', 'icon' => '✈️', 'title' => __('Professional Host')],
                            ],
                        ],
                        ['uri' => '/rooms','icon' => '🚪','title' => __('rooms')],
                        ['uri' => '/live-rooms','icon' => '📺','title' => __('Live Rooms')],
                        ['uri' => '/superadmin/home-carousel','icon' => '🎠','title' => __('HomeCarousel')],
                    ];
                @endphp

                @foreach($superadminPreviewLinks as $link)
                    @if(isset($link['children']))
                        <li class="crs-tree crs-item">
                            <a href="#" class="crs-link crs-toggle">
                                @if(str_contains($link['icon'] ?? '', 'fa-'))
                                    <i class="fa {{ $link['icon'] }} crs-icon" aria-hidden="true"></i>
                                @else
                                    <span class="crs-icon emoji-icon">{{ $link['icon'] }}</span>
                                @endif
                                <span class="crs-title">{{ $link['title'] }}</span>
                                <i class="fa fa-angle-left crs-arrow"></i>
                            </a>
                            <ul class="crs-submenu">
                                @foreach($link['children'] as $child)
                                    <li class="crs-item">
                                        <a href="{{ admin_url($child['uri']) }}" class="crs-link crs-leaf">
                                            @if(str_contains($child['icon'] ?? '', 'fa-'))
                                                <i class="fa {{ $child['icon'] }} crs-icon" aria-hidden="true"></i>
                                            @else
                                                <span class="crs-icon emoji-icon">{{ $child['icon'] }}</span>
                                            @endif
                                            <span class="crs-title">{{ $child['title'] }}</span>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </li>
                    @else
                        <li class="crs-item">
                            <a href="{{ admin_url($link['uri']) }}" class="crs-link crs-leaf">
                                @if(str_contains($link['icon'] ?? '', 'fa-'))
                                    <i class="fa {{ $link['icon'] }} crs-icon" aria-hidden="true"></i>
                                @else
                                    <span class="crs-icon emoji-icon">{{ $link['icon'] }}</span>
                                @endif
                                <span class="crs-title">{{ $link['title'] }}</span>
                            </a>
                        </li>
                    @endif
                @endforeach
            @elseif(session('preview_area_manager'))
                @php
                    $areaManagerPreviewLinks = [
                        ['uri' => '/', 'icon' => '', 'title' => __('Dashboard'), 'permission' => 'dashboard'],
                        [
                            'uri' => '#',
                            'icon' => 'fa-users',
                            'title' => __('Super Admin'),
                            'permission' => null,
                            'children' => [
                                ['uri' => '/superadmin-users', 'icon' => 'fa-users', 'title' => __('Super Admin'), 'permission' => 'superadmin'],
                            ],
                        ],
                        ['uri' => '/area-manager-charges-reports', 'icon' => 'fa-building', 'title' => __('charges')],
                        [
                            'uri' => '#',
                            'icon' => 'fa-briefcase',
                            'title' => __('BD'),
                            'permission' => null,
                            'children' => [
                                ['uri' => '/user-Bds', 'icon' => 'fa-briefcase', 'title' => __('BD'), 'permission' => 'Bds'],
                                ['uri' => '/professional-bd', 'icon' => 'fa-plane', 'title' => __('Professional BD'), 'permission' => 'professional-bd'],
                            ],
                        ],
                        ['uri' => '/users', 'icon' => '👥', 'title' => __('Users'), 'permission' => 'users'],
                        [
                            'uri' => '#',
                            'icon' => 'fa-building',
                            'title' => __('Agencies'),
                            'permission' => null,
                            'children' => [
                                ['uri' => '/agencies', 'icon' => '🏠', 'title' => __('Host Agencies'), 'permission' => 'agency'],
                                ['uri' => '/charge-agencies', 'icon' => '💳', 'title' => __('Shipping Agencies'), 'permission' => 'shipping-agency'],
                                ['uri' => '/ag/users', 'icon' => '👥', 'title' => __('Hosts'), 'permission' => 'host'],
                                ['uri' => '/ag/professional/users', 'icon' => '✈️', 'title' => __('Professional Host'), 'permission' => 'professional-users'],
                            ],
                        ],
                        [
                            'uri' => '#',
                            'icon' => 'fa-building',
                            'title' => __('rooms'),
                            'permission' => null,
                            'children' => [
                                ['uri' => '/rooms', 'icon' => '🚪', 'title' => __('rooms'), 'permission' => 'rooms'],
                                ['uri' => '/live-rooms', 'icon' => '📺', 'title' => __('Live Rooms'), 'permission' => 'live-rooms'],
                            ],
                        ],
                        [
                            'uri' => '#',
                            'icon' => '📢',
                            'title' => __('Advertisements'),
                            'permission' => null,
                            'children' => [
                                ['uri' => '/official_msgs', 'icon' => 'fa-list', 'title' => __('Official messages'), 'permission' => 'official-messages'],
                            ],
                        ],
                        [
                            'uri' => '#',
                            'icon' => '',
                            'title' => __('Employees and Permissions'),
                            'permission' => null,
                            'children' => [
                                ['uri' => '/auth/roles', 'icon' => '', 'title' => __('roles'), 'permission' => 'roles'],
                                ['uri' => '/auth/users', 'icon' => '', 'title' => __('users'), 'permission' => 'auth-users'],
                            ],
                        ],
                    ];
                @endphp

                @foreach($areaManagerPreviewLinks as $link)
                    @if(isset($link['children']))
                        <li class="crs-tree crs-item">
                            <a href="#" class="crs-link crs-toggle">
                                @if(str_contains($link['icon'] ?? '', 'fa-'))
                                    <i class="fa {{ $link['icon'] }} crs-icon" aria-hidden="true"></i>
                                @else
                                    <span class="crs-icon emoji-icon">{{ $link['icon'] }}</span>
                                @endif
                                <span class="crs-title">{{ $link['title'] }}</span>
                                <i class="fa fa-angle-left crs-arrow"></i>
                            </a>
                            <ul class="crs-submenu">
                                @foreach($link['children'] as $child)
                                    <li class="crs-item">
                                        <a href="{{ admin_url($child['uri']) }}" class="crs-link crs-leaf">
                                            <i class="fa {{ $child['icon'] }} crs-icon"></i>
                                            <span class="crs-title">{{ $child['title'] }}</span>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </li>
                    @else
                        <li class="crs-item">
                            <a href="{{ admin_url($link['uri']) }}" class="crs-link crs-leaf">
                                @if(str_contains($link['icon'] ?? '', 'fa-'))
                                    <i class="fa {{ $link['icon'] }} crs-icon" aria-hidden="true"></i>
                                @else
                                    <span class="crs-icon emoji-icon">{{ $link['icon'] }}</span>
                                @endif
                                <span class="crs-title">{{ $link['title'] }}</span>
                            </a>
                        </li>
                    @endif
                @endforeach
            @endif
        </ul>


    </section>

    <style>
        #tab-loading {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: var(--primary-color);
            color: var(--text-primary-color);
            z-index: 9999;
            padding: 30px 40px;
            border-radius: 10px;
            font-size: 20px;
            font-weight: bold;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.2);
        }

        .main-sidebar, .left-side {
            padding-top: 5% !important;
        }
    </style>

    <div id="tab-loading">
        Loading...
    </div>
    <!-- /.sidebar -->
</aside>
