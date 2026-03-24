<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-switch/3.3.4/css/bootstrap3/bootstrap-switch.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-switch/3.3.4/js/bootstrap-switch.min.js"></script>
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-colorpicker/2.5.3/css/bootstrap-colorpicker.min.css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-colorpicker/2.5.3/js/bootstrap-colorpicker.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" integrity="..." crossorigin="anonymous" />

@php
    $selectedTimeZone = App\Models\Setting::where('key', 'timezone')->first();
    $settings = App\Models\Setting::pluck('value', 'key')->toArray();
@endphp

@include('admin.settings.css.settings_css')

<body>
<div class="settings-sidebar">
    <div class="settings-menu">
        <button onclick="showSection('brandSettings')">{{ __('Brand settings') }}</button>
        <button onclick="showSection('landPageSettings')">{{ __('land settings') }}</button>
        <button onclick="showSection('workSettings')" class="position-relative">{{ __('Work') }}</button>
        <button onclick="showSection('mobileLinks')" class="position-relative">{{ __('Application') }}</button>
        <button onclick="showSection('themeSettings')">{{ __('Theme settings') }}</button>
        <button onclick="showSection('timeSettings')">{{ __('Timing settings') }}</button>
        <button onclick="showSection('appSettings')" class="position-relative">
            {{ __('App settings') }}
            {{-- <div class="ribbon-banner">
                <span>{{ __('soon') }}</span>
            </div> --}}
        </button>
        <button onclick="showSection('realTimeSetting')">{{ __('Sound & Video') }}</button>
        <button onclick="showSection('pusherSettings')">{{ __('Real Time Setting') }}</button>
        <button onclick="showSection('paymentCredentialSettings')">{{ __('Payment') }}</button>
        <button onclick="showSection('gamesSettings')" class="position-relative">
            {{ __('Games') }}
        </button>
        <button onclick="showSection('notificationSettings')" class="position-relative">
            {{ __('Notifications') }}
            <div class="ribbon-banner">
                <span>{{ __('soon') }}</span>
            </div>
        </button>
    </div>
</div>

<div class="all-page" style="    width: 100%;">
    <div class="settings-content">
        @include('admin.settings.brand')

        @include('admin.settings.land_page')
        @include('admin.settings.theme')
        @include('admin.settings.time')
        @include('admin.settings.real_time')
        @include('admin.settings.game')
        @include('admin.settings.payment_credential')
        @include('admin.settings.work')
        @include('admin.settings.mobile_links')
        @include('admin.settings.app')
        @include('admin.settings.pusher')

        <div id="notificationSettings" class="settings-section"></div>

        <div id="imageModal" class="modal" onclick="closeFullScreen()">
            <span class="close">&times;</span>
            <img class="modal-content" id="fullImage">
        </div>
    </div>
</div>
</body>

@include('admin.settings.js.settings_js')
