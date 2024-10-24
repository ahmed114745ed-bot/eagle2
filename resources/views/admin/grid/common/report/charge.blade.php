<div class="box-body no-padding">
    <div class="nav-scroll-container">
        <ul class="nav nav-pills">
            <li class="{{ request()->name == 'dash' || request()->name == null ? 'active' : '' }}">
                <a href="?name=dash" class="charge_action">
                    <i class="fa fa-arrow-right text-red"></i> {{ __('dash_repo') }}
                </a>
            </li>
            <li class="{{ request()->name == 'app' ? 'active' : '' }}">
                <a href="?name=app" class="charge_action">
                    <i class="fa fa-arrow-right text-red"></i> {{ __('app_repo') }}
                </a>
            </li>
            <li class="{{ request()->name == 'stripe' ? 'active' : '' }}">
                <a href="?name=stripe" class="charge_action">
                    <i class="fa fa-arrow-right text-red"></i> {{ __('coins_repo') }}
                </a>
            </li>
            <li class="{{ request()->name == 'in-app-purchas' ? 'active' : '' }}">
                <a href="?name=in-app-purchas" class="charge_action">
                    <i class="fa fa-arrow-right text-red"></i> {{ __('in_app_purchas') }}
                </a>
            </li>
        </ul>
    </div>
</div>

<style>
    .nav-scroll-container {
        overflow-x: auto; /* Allow horizontal scrolling */
        white-space: nowrap; /* Prevent wrapping to the next line */
        -webkit-overflow-scrolling: touch; /* Enable smooth scrolling on iOS */
    }

    .nav-pills {
        display: inline-flex; /* Display nav items in a single line */
        padding: 10px 0; /* Adjust padding as needed */
    }

    .nav-pills li {
        display: inline-block; /* Ensure list items display inline */
    }
</style>
