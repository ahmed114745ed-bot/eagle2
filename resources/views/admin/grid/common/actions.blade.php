<style>
.nav-pills .active {
    background-color: #fe9127 !important;
    color: white !important;
    border: none !important;
    outline: none !important;
    box-shadow: none !important;
}

.nav-pills .active::before {
    content: none !important;
}

.nav-pills > li.active > a {
    border-left: none !important;
}
</style>



<div class="box box-solid">
    <div class="box-header with-border">
        <h3 class="box-title">{{ __('admin.fields') }}</h3>
        <div class="box-tools">
            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
        </div>
    </div>
    <div class="box-body no-padding">
        <ul class="nav nav-pills nav-stacked">
            <li class="{{ request('name') == 'users' ? 'active' : '' }}" style="{{ request('name') == 'users' || empty(request('name')) ? 'background-color: orange;' : '' }}">
                <a href="?name=users" class="charge_action">
                    <i class="fa fa-arrow-right text-red"></i> {{ __('users report') }}
                </a>
            </li>
            <li class="{{ request('name') == 'agencies' ? 'active' : '' }}" style="{{ request('name') == 'agencies' ? 'background-color: orange;' : '' }}">
                <a href="?name=agencies" class="charge_action">
                    <i class="fa fa-arrow-right text-red"></i> {{ __('agencies report') }}
                </a>
            </li>
            <li class="{{ request('name') == 'agencies_manger' ? 'active' : '' }}" style="{{ request('name') == 'agencies_manger' ? 'background-color: orange;' : '' }}">
                <a href="?name=agencies_manger" class="charge_action">
                    <i class="fa fa-arrow-right text-red"></i> {{ __('admin.manger') }}
                </a>
            </li>
        </ul>
    </div>
</div>

