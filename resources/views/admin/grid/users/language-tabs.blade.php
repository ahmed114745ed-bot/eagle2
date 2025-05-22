@php
    $languages = [
        ['code' => 'en', 'name' => 'English'],
        ['code' => 'ar', 'name' => 'العربية'],
        ['code' => 'tr', 'name' => 'Türkçe'],
        ['code' => 'hi', 'name' => 'हिन्दी'],
    ];
    $roleType = isset($type) ? $type : 'weekly_star';
    $role = \Modules\Events\Entities\GeneralRole::where("type", $roleType)->first();
@endphp

<div class="box-body no-padding" style="margin: 10px">
    <ul class="nav nav-tabs" role="tablist">
        @foreach($languages as $i => $lang)
            <li class="{{ $i === 0 ? 'active' : '' }}">
                <a href="#tab-{{ $lang['code'] }}" data-toggle="tab">{{ $lang['name'] }}</a>
            </li>
        @endforeach
    </ul>

    <div class="tab-content" style="margin-top: 20px;">
        @foreach($languages as $i => $lang)
            <div class="tab-pane{{ $i === 0 ? ' active' : '' }}" id="tab-{{ $lang['code'] }}">
                @if($role != null)
                    <a href="{{ url('admin/general-rols/' . @$role->id . '/edit') }}">
                        {{ @$role->{'desc_' . $lang['code']} }}
                    </a>
                @endif
            </div>
        @endforeach
    </div>
</div>
