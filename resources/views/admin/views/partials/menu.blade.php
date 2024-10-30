@php
    $anyChild = false;
            $roles = \Illuminate\Support\Arr::get($item, 'roles', []);
            $roles = count($roles) > 0 ? $roles : null;

@endphp

@if(isset($item['children']))
    @php


// if ($item['id'] == 111) {
//     foreach ( $item['children'] as $child) {
//             // if ($child['id'] == 111) {
//             //     dd($child);
//             // }
//             if (isset($child['children'])){
//                 foreach ($child['children'] as $child2){
//                     // dd($child2['id']);
//                     if ($child2['id'] == 86) {
//                         $permission=\Illuminate\Support\Arr::get($child2, 'permission');
//                         $name=\Illuminate\Support\Arr::get($child2, 'title');

//                         $rolesL = \Illuminate\Support\Arr::get($child2, 'roles', []);
//                         $rolesL = count($rolesL) > 0 ? $rolesL : null;

//                         if (!$rolesL && !$permission)  continue;
//                         dd($permission,"kkkkkkkkkk");
//                     }
//                     $permission=\Illuminate\Support\Arr::get($child2, 'permission');
//                     $name=\Illuminate\Support\Arr::get($child2, 'title');

//                     $rolesL = \Illuminate\Support\Arr::get($child2, 'roles', []);
//                     $rolesL = count($rolesL) > 0 ? $rolesL : null;

//                     if (!$rolesL && !$permission)  continue;
//                     $isRoleVisible = $rolesL && Admin::user()->visible($rolesL);

//                     // dd($rolesL);
//                     $anyChild =  ( $isRoleVisible) || ($permission && Admin::user()->can($permission));
//                     if ($anyChild) break;
//                 }
//             }

//                 $permission=\Illuminate\Support\Arr::get($child, 'permission');
//                 $name=\Illuminate\Support\Arr::get($child, 'title');

//                 $rolesL = \Illuminate\Support\Arr::get($child, 'roles', []);
//                 $rolesL = count($rolesL) > 0 ? $rolesL : null;

//                 if (!$rolesL && !$permission)  continue;
//                 $isRoleVisible = $rolesL && Admin::user()->visible($rolesL);

//                 $anyChild =  ( $isRoleVisible) || ($permission && Admin::user()->can($permission));
//                 if ($anyChild) break;


//         }
// }
        foreach ( $item['children'] as $child) {
            // if ($child['id'] == 111) {
            //     dd($child);
            // }
            if (isset($child['children'])){

                foreach ($child['children'] as $child2){

                        $permission=\Illuminate\Support\Arr::get($child2, 'permission');
                    $name=\Illuminate\Support\Arr::get($child2, 'title');

                    $rolesL = \Illuminate\Support\Arr::get($child2, 'roles', []);
                    $rolesL = count($rolesL) > 0 ? $rolesL : null;

                    if (!$rolesL && !$permission)  continue;
                    $isRoleVisible = $rolesL && Admin::user()->visible($rolesL);

                    $anyChild =  ( $isRoleVisible) || ($permission && Admin::user()->can($permission));
                    if ($anyChild) break;
                }
            }else {                

                $permission=\Illuminate\Support\Arr::get($child, 'permission');
                $name=\Illuminate\Support\Arr::get($child, 'title');

                $rolesL = \Illuminate\Support\Arr::get($child, 'roles', []);
                $rolesL = count($rolesL) > 0 ? $rolesL : null;

                if (!$rolesL && !$permission)  continue;
                $isRoleVisible = $rolesL && Admin::user()->visible($rolesL);

                $anyChild =  ( $isRoleVisible) || ($permission && Admin::user()->can($permission));
                if ($anyChild) break;
            }


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











