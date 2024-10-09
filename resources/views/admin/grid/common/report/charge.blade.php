<div class="box-body no-padding">
    <ul class="nav nav-pills nav-stacked">
        <li class="{{ request()->name == 'dash' || request()->name == null ? 'active' : '' }}"><a href="?name=dash"
                                                                                                  class="charge_action"><i class="fa fa-arrow-right text-red"></i>{{ __('dash_repo') }}</a></li>
        <li class="{{ request()->name == 'app' ? 'active' : '' }}"><a href="?name=app" class="charge_action"><i
                    class="fa fa-arrow-right text-red"></i>{{ __('app_repo') }}</a></li>
        <li class="{{ request()->name == 'stripe' ? 'active' : '' }}"><a href="?name=stripe"
                                                                         class="charge_action"><i class="fa fa-arrow-right text-red"></i>{{ __('coins_repo') }}</a></li>
        <li class="{{ request()->name == 'in-app-purchas' ? 'active' : '' }}"><a href="?name=in-app-purchas"
                                                                                 class="charge_action"><i class="fa fa-arrow-right text-red"></i>{{ __('in_app_purchas') }}</a></li>
    </ul>
</div>


