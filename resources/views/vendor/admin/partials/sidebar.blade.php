<aside id="main-sidebar" class="main-sidebar">

    <!-- sidebar: style can be found in sidebar.less -->
    <section class="sidebar">

        <!-- Sidebar user panel (optional) -->
        <div class="user-panel">
            <div class="pull-left image">
                <img src="{{ Admin::user()->image }}" class="img-circle" alt="User Image">
            </div>
            <div class="pull-left info">
                <p>{{ Admin::user()->name }}</p>
                <!-- Status -->
                <a href="#"><i class="fa fa-circle text-success"></i> {{ trans('admin.online') }}</a>
            </div>
            <div class="pull-role">
            {{ Auth::user()->roles[0]->name ?? '' }}
            <!-- Status -->
            </div>
        </div>
   
        @if(config('admin.enable_menu_search'))
        <!-- search form (Optional) -->
        <form class="sidebar-form" style="overflow: initial;" onsubmit="return false;">
            <div class="input-group">
                <input type="text" autocomplete="off" class="form-control autocomplete" placeholder="Search...">
              <span class="input-group-btn">
                <button type="submit" name="search" id="search-btn" class="btn btn-flat"><i class="fa fa-search"></i>
                </button>
              </span>
                <ul class="dropdown-menu" role="menu" style="min-width: 210px;max-height: 300px;overflow: auto;">

              
                @foreach(Admin::menuLinks() as $link)
                    <li>
                        <a href="{{ admin_url($link['uri']) }}"><i class="fa {{ $link['icon'] }}"></i>{{ admin_trans($link['title']) }}</a>
                    </li>
                    @endforeach
                </ul>
            </div>
        </form>
        <!-- /.search form -->
        @endif


        @php
            use Illuminate\Support\Str;

            $menu = Admin::menu();

            $filteredMenu = collect($menu)->filter(function ($item) {
                if ((Str::startsWith($item['uri'] ?? '', 'bd') || ($item['uri'] ?? '') === '*') && !Admin::user()->inRoles(['bd'])) {
                    return false; 
                }
                return true; 
            })->values()->all();   
            @endphp

            <ul class="sidebar-menu">
                <li class="header">{{ trans('admin.menu') }}</li>

                @each('admin::partials.menu', $filteredMenu, 'item')
            </ul>


        <!-- Sidebar Menu -->
        <!-- <ul class="sidebar-menu">
            <li class="header">{{ trans('admin.menu') }}</li>

            @each('admin::partials.menu', Admin::menu(), 'item')

        </ul> -->
        <!-- /.sidebar-menu -->
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
            </style>


            <div id="tab-loading" >
            Loading...
            </div>
    <!-- /.sidebar -->
</aside>
