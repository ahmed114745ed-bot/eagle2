<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-switch/3.3.4/css/bootstrap3/bootstrap-switch.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-switch/3.3.4/js/bootstrap-switch.min.js"></script>
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-colorpicker/2.5.3/css/bootstrap-colorpicker.min.css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-colorpicker/2.5.3/js/bootstrap-colorpicker.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" integrity="..." crossorigin="anonymous" />

@php
    $selectedTimeZone = App\Models\Setting::where('key', 'timezone')->first();
    $settings = App\Models\Setting::pluck('value', 'key')->toArray();

    // Real Time Settings
    $agora_app_id = App\Helpers\Common::getConfig('app_id');
    $zego_server_secret = App\Helpers\Common::getConfig('zego_server_secret');
    $zego_app_id = App\Helpers\Common::getConfig('zego_app_id');
    $tencent_server_secret = App\Helpers\Common::getConfig('tencent_server_secret');
    $tencent_app_id = App\Helpers\Common::getConfig('tencent_app_id');
    $app_sign = App\Helpers\Common::getConfig('app_sign');
    $soundLibrary = App\Helpers\Common::getConfig('sound_library');
    $videoLibrary = App\Helpers\Common::getConfig('video_library');
    $liveLibrary = App\Helpers\Common::getConfig('live_library');
    $zego_filter_enabled = App\Helpers\Common::getConfig('zego_filter_enabled');
    $is_auto_preview = App\Helpers\Common::getConfig('is_auto_preview');
    $agora_app_certificate = App\Helpers\Common::getConfig('agora_app_certificate');

    // UTD VOICE
    $zego_token = App\Helpers\Common::getConfig('zego_token');
    $zego_key = App\Helpers\Common::getConfig('zego_key');

    // UTD-STREAM
    $utd_stream_app_id = App\Helpers\Common::getConfig('utd_stream_app_id');
    $utd_stream_server_secret = App\Helpers\Common::getConfig('utd_stream_server_secret');
    $utd_stream_callback_secret = App\Helpers\Common::getConfig('utd_stream_callback_secret');
@endphp

@include('admin.settings.css.settings_css')

<body>
<div class="settings-sidebar">
    <div class="sb-brand">
        <div class="sb-brand-icon"><i class="fas fa-sliders-h"></i></div>
        <div class="sb-brand-text">{{ __('Settings') }}</div>
    </div>
    <div class="settings-menu">
        <button onclick="showSection('brandSettings')"><i class="fas fa-crown"></i> {{ __('Brand settings') }}</button>
        <button onclick="showSection('landPageSettings')"><i class="fas fa-globe"></i> {{ __('land settings') }}</button>
        <button onclick="showSection('workSettings')"><i class="fas fa-briefcase"></i> {{ __('Work') }}</button>
        <button onclick="showSection('mobileLinks')"><i class="fas fa-mobile-alt"></i> {{ __('Application') }}</button>
        <button onclick="showSection('themeSettings')"><i class="fas fa-paint-brush"></i> {{ __('Theme settings') }}</button>
        <button onclick="showSection('timeSettings')"><i class="fas fa-clock"></i> {{ __('Timing settings') }}</button>
        <button onclick="showSection('appSettings')"><i class="fas fa-cogs"></i> {{ __('App settings') }}</button>
        <button onclick="showSection('realTimeSetting')"><i class="fas fa-broadcast-tower"></i> {{ __('Sound & Video') }}</button>
        <button onclick="showSection('pusherSettings')"><i class="fas fa-satellite-dish"></i> {{ __('Real Time Setting') }}</button>
        <button onclick="showSection('paymentCredentialSettings')"><i class="fas fa-credit-card"></i> {{ __('Payment') }}</button>
        <button onclick="showSection('gamesSettings')"><i class="fas fa-gamepad"></i> {{ __('Games') }}</button>
        <button onclick="showSection('notificationSettings')" class="position-relative"><i class="fas fa-bell"></i> {{ __('Notifications') }}<span class="sb-soon">{{ __('soon') }}</span></button>
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
