@php
    $anyChild = false;
            $roles = \Illuminate\Support\Arr::get($item, 'roles', []);
            $roles = count($roles) > 0 ? $roles : null;

@endphp

@if(isset($item['children']))
    @php


        foreach ( $item['children'] as $child) {
            $permission=\Illuminate\Support\Arr::get($child, 'permission');
            $name=\Illuminate\Support\Arr::get($child, 'title');

            $rolesL = \Illuminate\Support\Arr::get($child, 'roles', []);
            $rolesL = count($rolesL) > 0 ? $rolesL : null;

            if (!$rolesL && !$permission)  continue;
            $isRoleVisible = $rolesL && Admin::user()->visible($rolesL);

            $anyChild =  ( $isRoleVisible) || ($permission && Admin::user()->can($permission));
            if ($anyChild) break;
        }
    @endphp
@endif


@php


            $isVisible = ((( $roles && Admin::user()->visible($roles) ) || (!empty(\Illuminate\Support\Arr::get($item, 'permission'))  &&(Admin::user()->can(\Illuminate\Support\Arr::get($item, 'permission') ))) || Admin::user()->can('*')) || @$anyChild ?? false);
@endphp

@if($isVisible)
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











