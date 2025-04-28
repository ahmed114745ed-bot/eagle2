@php
    $agencyMenu = null;
    foreach (Admin::menu() as $item) {
        if ($item['title'] == 'Agency System') {
            $agencyMenu = $item;
            break;
        }
    }
@endphp

<div class="settings-sidebar" style="margin-bottom: 24px;">
    <h2>{{ __('Agencies Menu') }}</h2>
    @if($agencyMenu)
        <ul class="sidebar-menu">
            <li class="header">{{ admin_trans($agencyMenu['title']) }}</li>
            @foreach($agencyMenu['children'] as $item)
                @include('admin::partials.menu', $item)
            @endforeach
        </ul>
    @endif
{{--    <div class="settings-menu">--}}
{{--        <button onclick="window.location.href='{{ admin_url('') }}'" style="background: var(--primary-color); color: var(--text-secondary-color);">--}}
{{--            {{ __('family') }}--}}
{{--        </button>--}}
{{--    </div>--}}
</div>
<style>
    body {
        font-family: Arial, sans-serif;
        margin: 0;
        padding: 0;
        background-color: #121212;
        color: white;
        display: flex;
    }

    .settings-sidebar {
        width: 250px;
        background: var(--secondary-color);
        min-height: 400px;
        padding: 20px;
        box-shadow: 2px 0 10px rgba(0, 0, 0, 0.5);
    }

    .settings-sidebar h2 {
        text-align: center;
    }

    .settings-menu button {
        display: block;
        width: 100%;
        text-align: right;
        padding: 15px;
        background: #333;
        color: white;
        border: none;
        margin-bottom: 5px;
        cursor: pointer;
        font-size: 16px;
    }

    form {
        background: #222;
        padding: 20px;
        border-radius: 5px;
    }

    label {
        display: block;
        margin: 10px 0 5px;
    }

    input,
    select {
        width: 100%;
        padding: 10px;
        margin-bottom: 15px;
        background: #333;
        border: 1px solid #444;
        color: white;
    }

    button {
        padding: 10px;
        border: none;
        cursor: pointer;
        font-weight: bold;
    }

    button {
        width: 200px;

    }
    .sidebar-menu>li>a {
        padding-left: 0; !important;
    }
</style>
<script>
    $(document).on('click', '.settings-sidebar .treeview > a', function (e) {
        e.preventDefault();
        var $parent = $(this).parent();
        $parent.toggleClass('active');
        // $parent.find('.treeview-menu').first().slideToggle();
    });
</script>
