@php
    use Modules\Vip\Entities\Vip;
        $selectedTimeZone = App\Models\Setting::where('key', 'timezone')->first();
        $settings = App\Models\Setting::pluck('value', 'key')->toArray();
@endphp
@include('admin.settings.css.settings_css')
<link rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-switch/3.3.4/css/bootstrap3/bootstrap-switch.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-switch/3.3.4/js/bootstrap-switch.min.js"></script>


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
            <div class="ribbon-banner">
                <span>{{ __('soon') }}</span>
            </div>
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


        <div id="notificationSettings" class="settings-section"></div>

        <div id="workSettings" class="settings-section">

            <div class="box-body">

                <!-- Inner Tabs -->
                <div class="inner-settings-menu">
                    @php $chargeTabType = request()->get('type', 'Experience'); @endphp

                    <button onclick="changeInnerTab('Experience')"
                            class="{{ $chargeTabType == 'Experience' ? 'active' : '' }}">
                        {{ __('Experience settings') }}
                    </button>

                    <button onclick="changeInnerTab('coin')"
                            class="{{ $chargeTabType == 'coin' ? 'active' : '' }}">
                        {{ __('coin exchange') }}
                    </button>
                </div>

                @php
                    $oldExpData = cache('exp_percentages');
                @endphp


                    <!-- EXPERIENCE TAB -->
                <div id="Experience_tab" class="inner-tab-content" style="display:none;">
                    <div class="form">
                        <label class="d-block">{{ __('Experience settings:') }}</label>

                        <div class="row mt-4">

                            <!-- Wealth -->
                            <div class="col-md-6 mb-3" style="margin-top:40px;">
                                <form action="{{ route('admin.ovip-config') }}" method="POST"
                                      enctype="multipart/form-data">
                                    @csrf

                                    <div class="card exp-card-cont p-3 shadow">
                                        <div class="card-header d-flex justify-content-between align-items-center">
                                            <h4 class="m-0">{{ __('wealth') }}</h4>
                                        </div>

                                        <div class="row">

                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label for="wealth_exp">{{ __('wealth') }}</label>
                                                    <input type="text" id="wealth_exp" name="exp_sender_percentage"
                                                           value="{{ $oldExpData['exp_sender_percentage'] ?? '' }}"
                                                           placeholder="{{ __('Enter Exp') }}"
                                                           class="form-control">
                                                    <span class="form-text text-muted">1 coin = X EXP</span>
                                                </div>
                                            </div>

                                            @php $sender = Vip::where('type',2)->count(); @endphp
                                            @if ($sender == 0)
                                                <div class="col-md-12">
                                                    <a href="/admin/vips">{{ __('Go to Settings') }}</a>
                                                </div>
                                            @endif

                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label for="wealth_gift_price">{{ __('gift price') }}</label>
                                                    <input type="text" id="wealth_gift_price" name="test_calco"
                                                           value="{{ $settings['wealth_gift_price'] ?? '' }}"
                                                           placeholder="{{ __('wealth_gift_price') }}"
                                                           class="form-control">
                                                    <span id="exp_result" class="fw-bold ms-2"></span>
                                                </div>
                                            </div>

                                            <script>
                                                document.addEventListener('DOMContentLoaded', function () {
                                                    const expInput = document.getElementById('wealth_exp');
                                                    const giftPriceInput = document.getElementById('wealth_gift_price');
                                                    const resultSpan = document.getElementById('exp_result');

                                                    function updateExpResult() {
                                                        const expRate = parseFloat(expInput.value);
                                                        const giftPrice = parseFloat(giftPriceInput.value);

                                                        if (!isNaN(expRate) && !isNaN(giftPrice)) {
                                                            resultSpan.textContent = `= ${expRate * giftPrice} EXP`;
                                                        } else {
                                                            resultSpan.textContent = '';
                                                        }
                                                    }

                                                    expInput.addEventListener('input', updateExpResult);
                                                    giftPriceInput.addEventListener('input', updateExpResult);
                                                    updateExpResult();
                                                });
                                            </script>

                                            <div class="col-12 d-flex gap-3 mt-3">
                                                <button class="btn btn-primary">{{ __('Save') }}</button>
                                            </div>

                                        </div>
                                    </div>
                                </form>
                            </div>


                            <!-- Attraction -->
                            <div class="col-md-6 mb-3" style="margin-top:40px;">
                                <form action="{{ route('admin.ovip-config') }}" method="POST"
                                      enctype="multipart/form-data">
                                    @csrf

                                    <div class="card p-3 shadow">
                                        <div class="card-header d-flex justify-content-between align-items-center">
                                            <h4 class="m-0">{{ __('attraction') }}</h4>
                                        </div>

                                        <div class="row">

                                            <div class="col-md-12">
                                                <label for="attraction_exp">{{ __('attraction') }}</label>
                                                <input type="text" id="attraction_exp" name="exp_received_percentage"
                                                       value="{{ $oldExpData['exp_received_percentage'] ?? '' }}"
                                                       placeholder="{{ __('Enter Exp') }}"
                                                       class="form-control">
                                                <span class="form-text text-muted">1 Diamond = X EXP</span>
                                            </div>

                                            <div class="col-md-12">
                                                <label>{{ __('gift price') }}</label>
                                                <input type="text" id="attraction_gift_price" name="test_calco"
                                                       value="{{ $settings['attraction_gift_price'] ?? '' }}"
                                                       placeholder="{{ __('attraction_gift_price') }}"
                                                       class="form-control">
                                                <span id="attraction_exp_result" class="fw-bold ms-2"></span>
                                            </div>

                                            @php $receiver = Vip::where('type',1)->count(); @endphp
                                            @if ($receiver == 0)
                                                <div class="col-md-12">
                                                    <a href="/admin/vips">{{ __('Go to Settings') }}</a>
                                                </div>
                                            @endif

                                            <div class="col-12 d-flex gap-3 mt-3">
                                                <button class="btn btn-primary">{{ __('Save') }}</button>
                                            </div>

                                        </div>
                                    </div>
                                </form>
                            </div>


                            <!-- Charge -->
                            <div class="col-md-6 mb-3" style="margin-top:40px;">
                                <form action="{{ route('admin.ovip-config') }}" method="POST">
                                    @csrf

                                    <div class="card p-3 shadow">
                                        <div class="card-header d-flex justify-content-between align-items-center">
                                            <h4 class="m-0">{{ __('charge') }}</h4>
                                        </div>

                                        <div class="row">

                                            <div class="col-md-12">
                                                <label for="charge_exp">{{ __('charge') }}</label>
                                                <input type="text" id="charge_exp" name="exp_charge_percentage"
                                                       value="{{ $oldExpData['exp_charge_percentage'] ?? '' }}"
                                                       placeholder="{{ __('Enter Exp') }}" class="form-control">
                                            </div>

                                            <div class="col-md-12">
                                                <label for="charge_gift_price">{{ __('coins') }}</label>
                                                <input type="text" id="charge_gift_price" name="test_calco"
                                                       value="{{ $settings['charge_gift_price'] ?? '' }}"
                                                       placeholder="{{ __('charge_gift_price') }}"
                                                       class="form-control">
                                                <span id="charge_exp_result" class="fw-bold ms-2"></span>
                                            </div>

                                            @php $charger = Vip::where('type',5)->count(); @endphp
                                            @if ($charger == 0)
                                                <div class="col-md-12">
                                                    <a href="/admin/vips">{{ __('Go to Settings') }}</a>
                                                </div>
                                            @endif

                                            <div class="col-12 d-flex gap-3 mt-3">
                                                <button class="btn btn-primary">{{ __('Save') }}</button>
                                            </div>

                                        </div>
                                    </div>

                                </form>
                            </div>


                            <!-- Rooms -->
                            <div class="col-md-6 mb-3" style="margin-top:40px;">
                                <form action="{{ route('admin.ovip-config') }}" method="POST">
                                    @csrf

                                    <div class="card p-3 shadow">
                                        <div class="card-header d-flex justify-content-between align-items-center">
                                            <h4 class="m-0">{{ __('Rooms') }}</h4>
                                        </div>

                                        <div class="row">

                                            <div class="col-md-12">
                                                <label for="rooms_exp">{{ __('Rooms') }}</label>
                                                <input type="text" id="rooms_exp" name="exp_room_percentage"
                                                       value="{{ $oldExpData['exp_room_percentage'] ?? '' }}"
                                                       placeholder="{{ __('Enter Exp') }}" class="form-control">
                                            </div>

                                            <div class="col-md-12">
                                                <label>{{ __('gift price') }}</label>
                                                <input type="text" id="rooms_gift_price" name="test_calco"
                                                       value="{{ $settings['rooms_gift_price'] ?? '' }}"
                                                       placeholder="{{ __('rooms_gift_price') }}"
                                                       class="form-control">
                                                <span id="rooms_exp_result" class="fw-bold ms-2"></span>
                                            </div>

                                            @php $rooms = Vip::where('type',4)->count(); @endphp
                                            @if ($rooms == 0)
                                                <div class="col-md-12">
                                                    <a href="/admin/vips">{{ __('Go to Settings') }}</a>
                                                </div>
                                            @endif

                                            <div class="col-12 d-flex gap-3 mt-3">
                                                <button class="btn btn-primary">{{ __('Save') }}</button>
                                            </div>

                                        </div>
                                    </div>

                                </form>
                            </div>


                            <!-- CP -->
                            <div class="col-md-6 mb-3" style="margin-top:40px;">
                                <form action="{{ route('admin.ovip-config') }}" method="POST">
                                    @csrf

                                    <div class="card p-3 shadow">
                                        <div class="card-header d-flex justify-content-between align-items-center">
                                            <h4 class="m-0">{{ __('cp') }}</h4>
                                        </div>

                                        <div class="row">

                                            <div class="col-md-12">
                                                <label for="cp_exp">{{ __('cp') }}</label>
                                                <input type="text" id="cp_exp" name="exp_cp_percentage"
                                                       value="{{ $oldExpData['exp_cp_percentage'] ?? '' }}"
                                                       placeholder="{{ __('Enter Exp') }}"
                                                       class="form-control">
                                            </div>

                                            <div class="col-md-12">
                                                <label>{{ __('gift price') }}</label>
                                                <input type="text" id="cp_gift_price" name="test_calco"
                                                       value="{{ $settings['cp_gift_price'] ?? '' }}"
                                                       placeholder="cp_gift_price"
                                                       class="form-control">
                                                <span id="cp_exp_result" class="fw-bold ms-2"></span>
                                            </div>

                                            @php $cp = Vip::where('type',3)->count(); @endphp
                                            @if ($cp == 0)
                                                <div class="col-md-12">
                                                    <a href="/admin/vips">{{ __('Go to Settings') }}</a>
                                                </div>
                                            @endif

                                            <div class="col-12 d-flex gap-3 mt-3">
                                                <button class="btn btn-primary">{{ __('Save') }}</button>
                                            </div>

                                        </div>
                                    </div>

                                </form>
                            </div>

                        </div>

                    </div>
                </div>


                <!-- COIN TAB -->
                <div id="coin_tab" class="inner-tab-content" style="display:none;">
                    <div class="form">

                        <div class="row mt-4">

                            <div class="col-md-6 mb-3" style="margin-top:40px;">
                                <form action="{{ route('admin.exchange-coins') }}" method="POST"
                                      enctype="multipart/form-data">
                                    @csrf

                                    <div class="card p-3 shadow">

                                        <div class="row">

                                            <div class="col-md-12">
                                                <label for="coin_exp">{{ __('diamond') }}</label>
                                                <input type="text" id="coin_exp" name="exchange_coin_percentage"
                                                       value="{{ $settings['exchange_coin_percentage'] ?? 1 }}"
                                                       placeholder="{{ __('Enter Exp') }}" class="form-control">
                                                <span class="form-text text-muted">1 diamond = X coin</span>
                                            </div>

                                            <div class="col-md-12 coin-calculator mt-3">
                                                <label>{{ __('diamond') }}</label>
                                                <input type="text" class="user_coin_input form-control"
                                                       placeholder="Enter value">
                                                <input type="hidden" class="exchange_rate"
                                                       value="{{ $settings['exchange_coin_percentage'] ?? 1 }}">
                                                <span class="exp_result fw-bold ms-2"></span>
                                            </div>

                                            <script>
                                                document.addEventListener('DOMContentLoaded', function () {
                                                    document.querySelectorAll('.coin-calculator').forEach(container => {
                                                        const userInput = container.querySelector('.user_coin_input');
                                                        const rate = container.querySelector('.exchange_rate');
                                                        const resultSpan = container.querySelector('.exp_result');

                                                        function calc() {
                                                            const val = parseFloat(userInput.value);
                                                            const rateX = parseFloat(rate.value);

                                                            if (!isNaN(val) && !isNaN(rateX)) {
                                                                resultSpan.textContent = `= ${(rateX / 100) * val} coin`;
                                                            } else {
                                                                resultSpan.textContent = '';
                                                            }
                                                        }

                                                        userInput.addEventListener('input', calc);
                                                        calc();
                                                    });
                                                });
                                            </script>

                                            <div class="col-12 d-flex gap-3 mt-3">
                                                <button class="btn btn-primary">{{ __('Save') }}</button>
                                            </div>

                                        </div>

                                    </div>

                                </form>
                            </div>

                        </div>

                    </div>
                </div>

            </div>
        </div>


        {{-- <div id="mobileLinks" class="settings-section">
            <div class="form">


                <div class="row mt-4">
                    <!-- android link -->
                    <div class="col-md-6 mb-3 ms-0 me-auto" style="margin-top: 40px;">
                        <form action="{{ route('admin.app.settings.update') }}" method="POST"
                            enctype="multipart/form-data">
                            @csrf
                            <div class="card exp-card-cont p-3 shadow" style="">
                                <div class="card-header exp-card d-flex justify-content-between align-items-center">
                                    <h4 class="m-0">{{ __('Android link') }}</h4>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group d-flex align-items-center">
                                        <div class="form-group">
                                                <label for="wealth_exp" class="form-label">{{ __('link') }}</label>
                                                <input type="text"  name="android_link" value="{{ $settings['android_link'] ?? ''}}"
                                                    class="form-control">

                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-12 d-flex gap-3 mt-3">
                                        <button type="submit"
                                            class="btn btn-primary">{{ __('Save') }}</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>



                    <!-- ios link -->
                    <div class="col-md-6 mb-3 ms-0 me-auto" style="margin-top: 40px;">
                        <form action="{{ route('admin.app.settings.update') }}" method="POST"
                            enctype="multipart/form-data">
                            @csrf
                           <div class="card exp-card-cont p-3 shadow" style="">
                                <div class="card-header exp-card d-flex justify-content-between align-items-center">
                                    <h4 class="m-0">{{ __('ios link') }}</h4>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group d-flex align-items-center">
                                        <div class="form-group">
                                                <label for="wealth_exp" class="form-label">{{ __('link') }}</label>
                                                <input type="text"  name="ios_link" value="{{ $settings['ios_link'] ?? ''}}"
                                                    class="form-control">

                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-12 d-flex gap-3 mt-3">
                                        <button type="submit"
                                            class="btn btn-primary">{{ __('Save') }}</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>



                    <!-- huawei  link -->
                    <div class="col-md-6 mb-3 ms-0 me-auto" style="margin-top: 40px;">
                        <form action="{{ route('admin.app.settings.update') }}" method="POST"
                            enctype="multipart/form-data">
                            @csrf
                            <div class="card exp-card-cont p-3 shadow" style="">
                                <div class="card-header exp-card d-flex justify-content-between align-items-center">
                                    <h4 class="m-0">{{ __('huawei link') }}</h4>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group d-flex align-items-center">
                                        <div class="form-group">
                                                <label for="wealth_exp" class="form-label">{{ __('link') }}</label>
                                                <input type="text"  name="huawei_link" value="{{ $settings['huawei_link'] ?? ''}}"
                                                    class="form-control">

                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-12 d-flex gap-3 mt-3">
                                        <button type="submit"
                                            class="btn btn-primary">{{ __('Save') }}</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div> --}}

        <div id="mobileLinks" class="settings-section">
            <div class="form">
                <div class="row mt-4">

                    <!-- Android Link -->
                    <div class="col-md-6 mb-3 ms-0 me-auto" style="margin-top: 40px;">
                        <form action="{{ route('admin.app.settings.update') }}" method="POST"
                              enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="current_tab" value="">
                            <div class="card exp-card-cont p-3 shadow">
                                <div class="card-header exp-card d-flex justify-content-between align-items-center">
                                    <h4 class="m-0">{{ __('Android link') }}</h4>
                                </div>

                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label class="form-label">{{ __('link') }}</label>
                                            <input type="text" name="android_link"
                                                   value="{{ $settings['android_link'] ?? '' }}"
                                                   class="form-control">
                                        </div>
                                    </div>

                                    <div class="col-12 d-flex gap-3 mt-3">
                                        <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- iOS Link -->
                    <div class="col-md-6 mb-3 ms-0 me-auto" style="margin-top: 40px;">
                        <form action="{{ route('admin.app.settings.update') }}" method="POST"
                              enctype="multipart/form-data">
                            @csrf
                            <div class="card exp-card-cont p-3 shadow">
                                <div class="card-header exp-card d-flex justify-content-between align-items-center">
                                    <h4 class="m-0">{{ __('ios link') }}</h4>
                                </div>

                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label class="form-label">{{ __('link') }}</label>
                                            <input type="text" name="ios_link"
                                                   value="{{ $settings['ios_link'] ?? '' }}"
                                                   class="form-control">
                                        </div>
                                    </div>

                                    <div class="col-12 d-flex gap-3 mt-3">
                                        <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Huawei Link -->
                    <div class="col-md-6 mb-3 ms-0 me-auto" style="margin-top: 40px;">
                        <form action="{{ route('admin.app.settings.update') }}" method="POST"
                              enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="current_tab" value="">
                            <div class="card exp-card-cont p-3 shadow">
                                <div class="card-header exp-card d-flex justify-content-between align-items-center">
                                    <h4 class="m-0">{{ __('huawei link') }}</h4>
                                </div>

                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label class="form-label">{{ __('link') }}</label>
                                            <input type="text" name="huawei_link"
                                                   value="{{ $settings['huawei_link'] ?? '' }}"
                                                   class="form-control">
                                        </div>
                                    </div>

                                    <div class="col-12 d-flex gap-3 mt-3">
                                        <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>

                </div>
            </div>
        </div>

        <div id="appSettings" class="settings-section">
            <h3>{{ __('App Settings') }}</h3>
            <form action="{{ route('admin.app-config.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="form row">
                    <input type="hidden" name="reset" id="reset" value=3>
                    {{-- Primary Color --}}
                    {{-- <div class="col-md-6">
                        <label>{{ __('Primary Color') }}</label>
                        <input type="color" name="app_primary_color" id="app_primary_color"
                               value="{{ data_get($settings, 'app_primary_color', '#32e5ac') }}"
                               class="form-control">
                    </div> --}}

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="app_primary_color">{{ __('Primary Color') }}</label>

                            <div class="input-group colorpicker-element">
                                    <span class="input-group-addon">
                                        <i style="background-color: {{ data_get($settings, 'app_primary_color', '#32e5ac') }};"></i>
                                    </span>

                                <input
                                    type="text"
                                    name="app_primary_color"
                                    id="app_primary_color"
                                    class="form-control"
                                    value="{{ data_get($settings, 'app_primary_color', '#32e5ac') }}"
                                    placeholder="اختر لون"
                                >
                            </div>
                        </div>
                    </div>


                    {{-- Background Type --}}
                    <div class="col-md-6">
                        <label>{{ __('Background Type') }}</label>
                        <select name="background_type" id="background_type" class="form-control"
                                onchange="toggleBackgroundInput()">
                            <option
                                value="color" {{ data_get($settings, 'background_type') === 'color' ? 'selected' : '' }}>
                                Color
                            </option>
                            <option
                                value="image" {{ data_get($settings, 'background_type') === 'image' ? 'selected' : '' }}>
                                Image
                            </option>
                            <option
                                value="gradient" {{ data_get($settings, 'background_type') === 'gradient' ? 'selected' : '' }}>
                                Gradient
                            </option>
                        </select>
                    </div>

                    {{-- Background Color --}}
                    {{-- <div class="col-md-6" id="background_color_group"
                         style="display: {{ data_get($settings, 'background_type') === 'color' ? 'block' : 'none' }};">
                        <label>{{ __('Background Color') }}</label>
                        <input type="color" name="background_color" id="background_color"
                               value="{{ data_get($settings, 'background_color', '#ffffff') }}"
                               class="form-control">
                    </div> --}}

                    <div class="col-md-6" id="background_color_group"
                         style="display: {{ data_get($settings, 'background_type') === 'color' ? 'block' : 'none' }};">

                        <div class="form-group">
                            <label for="background_color">{{ __('Background Color') }}</label>

                            <div class="input-group colorpicker-element">
                                    <span class="input-group-addon">
                                        <i style="background-color: {{ data_get($settings, 'background_color', '#ffffff') }};"></i>
                                    </span>

                                <input
                                    type="text"
                                    name="background_color"
                                    id="background_color"
                                    class="form-control"
                                    value="{{ data_get($settings, 'background_color', '#ffffff') }}"
                                    placeholder="اختر لون"
                                >
                            </div>
                        </div>

                    </div>


                    {{-- Background Image --}}
                    <div class="col-md-6" id="background_image_group"
                         style="display: {{ data_get($settings, 'background_type') === 'image' ? 'block' : 'none' }};">
                        <label>{{ __('Background Image') }}</label>
                        <input type="file" name="background_image" class="form-control">
                        @if(data_get($settings, 'background_type') === 'image' && !empty(data_get($settings, 'app_background')))
                            <div class="mt-2">
                                <img src="{{ getImagePath(data_get($settings, 'app_background')) }}" width="100"
                                     class="img-thumbnail">
                            </div>
                        @endif
                    </div>

                    {{-- Bottom Nav --}}
                    {{-- <div class="col-md-6">
                        <label>{{ __('Bottom Nav Color') }}</label>
                        <input type="color" name="bottom_color" id="bottom_color"
                               value="{{ data_get($settings, 'bottom_nav_bottom_color', '') }}"
                               class="form-control">
                    </div> --}}
                    {{-- <div class="col-md-6">
                        <label>{{ __('Bottom Nav Active Color') }}</label>
                        <input type="color" name="active_color" id="active_color"
                               value="{{ data_get($settings, 'bottom_nav_active_color', '') }}"
                               class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label>{{ __('Bottom Nav Inactive Color') }}</label>
                        <input type="color" name="inactive_color" id="inactive_color"
                               value="{{ data_get($settings, 'bottom_nav_inactive_color', '') }}"
                               class="form-control">
                    </div> --}}

                    {{-- Text Header Color --}}
                    {{-- <div class="col-md-6">
                        <label>{{ __('Text Header Color') }}</label>
                        <input type="color" name="text_header_color" id="text_header_color"
                               value="{{ data_get($settings, 'text_header_color', '#000000') }}"
                               class="form-control">
                    </div> --}}

                    {{-- Button Text Color --}}
                    {{-- <div class="col-md-6">
                        <label>{{ __('Button Text Color') }}</label>
                        <input type="color" name="button_text_color" id="button_text_color"
                               value="{{ data_get($settings, 'button_text_color', '#ffffff') }}"
                               class="form-control">
                    </div> --}}


                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="bottom_color">{{ __('Bottom Nav Color') }}</label>
                            <div class="input-group colorpicker-element">
                                    <span class="input-group-addon">
                                        <i style="background-color: {{ data_get($settings, 'bottom_nav_bottom_color', '') }};"></i>
                                    </span>
                                <input
                                    type="text"
                                    name="bottom_color"
                                    id="bottom_color"
                                    class="form-control"
                                    value="{{ data_get($settings, 'bottom_nav_bottom_color', '') }}"
                                    placeholder="اختر لون"
                                >
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="active_color">{{ __('Bottom Nav Active Color') }}</label>
                            <div class="input-group colorpicker-element">
                                    <span class="input-group-addon">
                                        <i style="background-color: {{ data_get($settings, 'bottom_nav_active_color', '') }};"></i>
                                    </span>
                                <input
                                    type="text"
                                    name="active_color"
                                    id="active_color"
                                    class="form-control"
                                    value="{{ data_get($settings, 'bottom_nav_active_color', '') }}"
                                    placeholder="اختر لون"
                                >
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="inactive_color">{{ __('Bottom Nav Inactive Color') }}</label>
                            <div class="input-group colorpicker-element">
                                    <span class="input-group-addon">
                                        <i style="background-color: {{ data_get($settings, 'bottom_nav_inactive_color', '') }};"></i>
                                    </span>
                                <input
                                    type="text"
                                    name="inactive_color"
                                    id="inactive_color"
                                    class="form-control"
                                    value="{{ data_get($settings, 'bottom_nav_inactive_color', '') }}"
                                    placeholder="اختر لون"
                                >
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="text_header_color">{{ __('Text Header Color') }}</label>
                            <div class="input-group colorpicker-element">
                                    <span class="input-group-addon">
                                        <i style="background-color: {{ data_get($settings, 'text_header_color', '#000000') }};"></i>
                                    </span>
                                <input
                                    type="text"
                                    name="text_header_color"
                                    id="text_header_color"
                                    class="form-control"
                                    value="{{ data_get($settings, 'text_header_color', '#000000') }}"
                                    placeholder="اختر لون"
                                >
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="button_text_color">{{ __('Button Text Color') }}</label>
                            <div class="input-group colorpicker-element">
                                    <span class="input-group-addon">
                                        <i style="background-color: {{ data_get($settings, 'button_text_color', '#ffffff') }};"></i>
                                    </span>
                                <input
                                    type="text"
                                    name="button_text_color"
                                    id="button_text_color"
                                    class="form-control"
                                    value="{{ data_get($settings, 'button_text_color', '#ffffff') }}"
                                    placeholder="اختر لون"
                                >
                            </div>
                        </div>
                    </div>

                    @if (in_array(env('APP_NAME'), ['Eagle', 'Lumio']))

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="is_new_theme_enabled">{{ __('New Theme Enabled') }}</label>
                                <input type="hidden" name="is_new_theme_enabled" value="0">
                                <input type="checkbox" name="is_new_theme_enabled" value="1"
                                       data-bootstrap-switch {{ data_get($settings, 'is_new_theme_enabled') ? 'checked' : '' }}>
                            </div>
                        </div>

                    @endif

                </div>

                <div class="col-12 d-flex gap-3 mt-3">
                    <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
                    <button type="button" id="resetAppColorsSettings"
                            class="btn btn-secondary">{{ __('Reset Colors') }}</button>
                </div>
            </form>
        </div>


        <div id="pusherSettings" class="settings-section">
            <h3>{{ __('Real Time Setting') }}</h3>

            <div class="row"
                 style="
                background-color:var(--box-background-color)!important;

                ">
                <div class="col-md-6 mb-3 ms-0 me-auto">

                    <!-- Pusher Form -->
                    <form action="{{ route('admin.update-agora-zego') }}" method="POST" class="mb-4">
                        @csrf
                        <div class="card p-3 shadow" style="height: 450px;">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h4 class="m-0">{{ __('pusher') }}</h4>
                                <div class="d-flex align-items-center">
                                    <input type="radio" id="pusherRadio" class="custom-radio pusherLib"
                                           name="library" value="2" {{ $library == '2' ? 'checked' : '' }}>
                                    <label for="pusherRadio" class="switch"></label>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="pusher_app_id">{{ __('pusher_app_id') }}:</label>
                                        <input type="text" id="pusher_app_id" name="pusher_app_id"
                                               placeholder="pusher_app_id" value="{{ $pusher_app_id }}"
                                               class="form-control" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="pusher_app_key">{{ __('pusher_app_key') }}:</label>
                                        <input type="text" id="pusher_app_key" name="pusher_app_key"
                                               placeholder="pusher_app_key" value="{{ $pusher_app_key }}"
                                               class="form-control" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="pusher_app_secret">{{ __('pusher_app_secret') }}:</label>
                                        <input type="text" id="pusher_app_secret" name="pusher_app_secret"
                                               placeholder="pusher_app_secret" value="{{ $pusher_app_secret }}"
                                               class="form-control" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="pusher_app_cluster">{{ __('pusher_app_cluster') }}:</label>
                                        <input type="text" id="pusher_app_cluster"
                                               name="pusher_app_cluster" placeholder="pusher_app_cluster"
                                               value="{{ $pusher_app_cluster }}" class="form-control" required>
                                    </div>
                                </div>
                            </div>
                            <button type="submit"
                                    class="btn btn-primary mt-5 btn-save">{{ __('save') }}</button>
                        </div>
                    </form>
                </div>
                <div class="col-md-6 mb-3 ms-0 me-auto">

                    <!-- Firebase Form -->
                    <form action="{{ route('admin.update-agora-zego') }}" method="POST" class="mb-4">
                        @csrf
                        <div class="card p-3 shadow" style="height: 450px;">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h4 class="m-0">{{ __('firebase') }}</h4>
                                <div class="ribbon-banner-card">
                                    <span>{{ __('soon') }}</span>
                                </div>
                                <div class="d-flex align-items-center">
                                    <input type="radio" id="firebaseRadio" class="custom-radio firebaseLib"
                                           name="library" value="1" {{ $library == '1' ? 'checked' : '' }}>
                                    <label for="firebaseRadio" class="switch"></label>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="firebase_api_key">{{ __('firebase_api_key') }}:</label>
                                        <input type="text" id="firebase_api_key" name="firebase_api_key"
                                               placeholder="{{ __('firebase_api_key') }}"
                                               value="{{ $firebase_api_key }}" class="form-control" required>
                                    </div>
                                    <div class="form-group">
                                        <label
                                            for="firebase_auth_domain">{{ __('firebase_auth_domain') }}:</label>
                                        <input type="text" id="firebase_auth_domain"
                                               name="firebase_auth_domain"
                                               placeholder="{{ __('firebase_auth_domain') }}"
                                               value="{{ $firebase_auth_domain }}" class="form-control" required>
                                    </div>
                                    <div class="form-group">
                                        <label
                                            for="firebase_database_url">{{ __('firebase_database_url') }}:</label>
                                        <input type="text" id="firebase_database_url"
                                               name="firebase_database_url"
                                               placeholder="{{ __('firebase_database_url') }}"
                                               value="{{ $firebase_database_url }}" class="form-control"
                                               required>
                                    </div>
                                </div>
                            </div>
                            <button type="submit"
                                    class="btn btn-primary mt-5 btn-save">{{ __('save') }}</button>
                        </div>
                    </form>
                </div>
                <div class="col-md-6 mb-3 ms-0 me-auto card-top">

                    <!-- Supabase Form -->
                    <form action="{{ route('admin.update-agora-zego') }}" method="POST">
                        @csrf
                        <div class="card p-3 shadow" style="height: 450px;">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h4 class="m-0">{{ __('supabase') }}</h4>
                                <div class="ribbon-banner-card">
                                    <span>{{ __('soon') }}</span>
                                </div>
                                <div class="d-flex align-items-center">
                                    <input type="radio" id="supabaseRadio" class="custom-radio supabaseLib"
                                           name="library" value="3" {{ $library == '3' ? 'checked' : '' }}>
                                    <label for="supabaseRadio" class="switch"></label>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="supabase_url">{{ __('supabase_url') }}:</label>
                                        <input type="text" id="supabase_url" name="supabase_url"
                                               placeholder="{{ __('supabase_url') }}"
                                               value="{{ $supabase_url }}" class="form-control" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="supabase_key">{{ __('supabase_key') }}:</label>
                                        <input type="text" id="supabase_key" name="supabase_key"
                                               placeholder="{{ __('supabase_key') }}"
                                               value="{{ $supabase_key }}" class="form-control" required>
                                    </div>
                                    <div class="form-group">
                                        <label
                                            for="supabase_service_role_key">{{ __('supabase_service_role_key') }}
                                            :</label>
                                        <input type="text" id="supabase_service_role_key"
                                               name="supabase_service_role_key"
                                               placeholder="{{ __('supabase_service_role_key') }}"
                                               value="{{ $supabase_service_role_key }}" class="form-control"
                                               required>
                                    </div>
                                </div>
                            </div>
                            <button type="submit"
                                    class="btn btn-primary mt-5 btn-save">{{ __('save') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>


        <div id="imageModal" class="modal" onclick="closeFullScreen()">
            <span class="close">&times;</span>
            <img class="modal-content" id="fullImage">
        </div>

        <script>
            $(document).on('change', '.libraryRealTime', function () {
                $(this).closest('form').submit();
            });

            function reseting(colorid, value) {
                $('#' + colorid).val(value);

                if (colorid === 'background_type') {
                    if (value === 'image') {
                        $('#background_image_group').show();
                        $('#background_image_preview').show();
                        $('#background_color_group').hide();
                        $('#gradient_group').hide();
                    }
                }
            }
        </script>
        <script>

            document.addEventListener('DOMContentLoaded', function () {
                const units = ['attraction', 'charge', 'rooms', 'cp'];

                units.forEach(unit => {
                    const expInput = document.getElementById(`${unit}_exp`);
                    const priceInput = document.getElementById(`${unit}_gift_price`);
                    const resultSpan = document.getElementById(`${unit}_exp_result`);

                    if (expInput && priceInput && resultSpan) {
                        function updateExpResult() {
                            const expRate = parseFloat(expInput.value);
                            const giftPrice = parseFloat(priceInput.value);

                            if (!isNaN(expRate) && !isNaN(giftPrice)) {
                                const totalExp = expRate * giftPrice;
                                resultSpan.textContent = `= ${totalExp} EXP`;
                            } else {
                                resultSpan.textContent = '';
                            }
                        }

                        expInput.addEventListener('input', updateExpResult);
                        priceInput.addEventListener('input', updateExpResult);
                        updateExpResult();
                    }
                });
            });

            function select_brand_image(name, obj) {
                $('#brand_image').val(name)
                $('.image_success').removeClass('border-success')
                $(obj).addClass('border-success')
                toastr.success('Brand image chosen successfully');
            }

            function choose_image() {
                var fileInput = document.getElementById('brand_background_image');
                var image = fileInput.files[0]; // Get the file object

                if (!image) {
                    toastr.error('Please select an image first.');
                    return;
                }

                var formData = new FormData();
                formData.append('image', image);
                formData.append('_token', "{{ csrf_token() }}");

                $.ajax({
                    url: "{{ route('admin.save_image') }}",
                    type: "POST",
                    data: formData,
                    contentType: false, // Important
                    processData: false, // Important
                    success: function (response) {
                        console.log('Uploading image is true');
                        toastr.success('Library preference saved!');
                    },
                    error: function (xhr) {
                        console.error('Failed to Upload image');
                        toastr.error('Failed to Upload image');
                    }
                });
            }
        </script>
        <script>
            function updateLibrary(selectedLibrary, inputName) {
                let data = {
                    _token: "{{ csrf_token() }}",
                };
                data[inputName] = selectedLibrary;

                $.ajax({
                    url: "{{ route('admin.update-agora-zego') }}",
                    type: "POST",
                    data: data,
                    success: function (response) {
                        console.log("Library updated via AJAX:", response);
                        toastr.success('Library preference saved!');
                    },
                    error: function (xhr) {
                        console.error("AJAX Error:", xhr.responseText);
                        toastr.error('Failed to update library');
                    }
                });
            }

            function updateSwitches() {
                $(".custom-radio").each(function () {
                    if ($(this).is(":checked")) {
                        $(this).next(".switch").addClass("active");
                    } else {
                        $(this).next(".switch").removeClass("active");
                    }
                });
            }

            function updatePaymentSwitches() {
                $(".custom-payment-radio").each(function () {
                    if ($(this).is(":checked")) {
                        $(this).next(".switch").addClass("active");
                    } else {
                        $(this).next(".switch").removeClass("active");
                    }
                });
            }

            // Handle change event on radio buttons
            $(document).on("change", ".custom-radio", function () {
                let selectedLibrary = $(this).val();
                let inputName = $(this).attr('name');
                console.log("Selected library:", selectedLibrary);
                console.log("library Name:", inputName);
                updateLibrary(selectedLibrary, inputName);
                updateSwitches();
            });

            $(document).on("change", ".custom-payment-radio", function () {
                updatePaymentSwitches();
            });

            // Handle click on switch to activate the corresponding radio button
            $(document).on("click", ".switch", function () {
                const radio = $(this).prev(".custom-radio");

                if (!radio.prop("checked")) {
                    // Uncheck all radios in the same group
                    $("input[name='library']").prop("checked", false);
                    // Remove active class from all switches
                    $(".switch").removeClass("active");

                    // Check the clicked one
                    radio.prop("checked", true).trigger("change");
                }
            });

            // Initialize switches based on current checked radio on load
            $(document).ready(function () {
                updateSwitches();
                updatePaymentSwitches();
            });
        </script>

        <!-- كود JavaScript -->
        <script>
            function previewImage(event) {
                let file = event.target.files[0];
                if (file) {
                    let reader = new FileReader();
                    reader.onload = function (e) {
                        let preview = document.getElementById('imagePreview');
                        preview.src = e.target.result; // Update preview with new image
                        preview.style.display = 'block'; // Show image
                    };
                    reader.readAsDataURL(file);
                }
            }

            function previewFavIcon(event) {
                let file = event.target.files[0];
                if (file) {
                    let reader = new FileReader();
                    reader.onload = function (e) {
                        let preview = document.getElementById('favIconPreview');
                        preview.src = e.target.result; // Update preview with new image
                        preview.style.display = 'block'; // Show image
                    };
                    reader.readAsDataURL(file);
                }
            }

            // document.addEventListener("DOMContentLoaded", function () {

            //         function getQueryParam(name) {
            //             const urlParams = new URLSearchParams(window.location.search);
            //             return urlParams.get(name);
            //         }

            //         // Which outer section is open?
            //         const activeTab = getQueryParam("firsttab") || "brandSettings";
            //         showSection(activeTab);

            //         // Auto-open inner tabs if workSettings is loaded
            //         if (activeTab === "workSettings") {
            //             const type = getQueryParam("type") || "Experience";
            //             showInnerContent(type);

            //             // Make button active
            //             document.querySelectorAll(".inner-settings-menu button").forEach(btn => {
            //                 btn.classList.remove("active");
            //             });

            //             const correctBtn = document.querySelector(`.inner-settings-menu button[onclick="changeInnerTab('${type}')"]`);
            //             if (correctBtn) correctBtn.classList.add("active");
            //         }
            //     });


            //     function showSection(sectionId) {
            //         document.querySelectorAll('.settings-section').forEach(section => {
            //             section.classList.remove('active');
            //         });

            //         document.getElementById(sectionId).classList.add('active');

            //         const url = new URL(window.location);
            //         url.searchParams.set("firsttab", sectionId);
            //         window.history.pushState({}, "", url);
            //     }


            document.addEventListener("DOMContentLoaded", function () {

                function getQueryParam(name) {
                    const urlParams = new URLSearchParams(window.location.search);
                    return urlParams.get(name);
                }

                // Which outer section is open?
                const activeTab = getQueryParam("tab") || "brandSettings";
                showSection(activeTab);

                // Auto-open inner tabs if workSettings is loaded
                if (activeTab === "workSettings") {
                    const type = getQueryParam("type") || "Experience";
                    showInnerContent(type);

                    // Make button active
                    document.querySelectorAll(".inner-settings-menu button").forEach(btn => {
                        btn.classList.remove("active");
                    });

                    const correctBtn = document.querySelector(`.inner-settings-menu button[onclick="changeInnerTab('${type}')"]`);
                    if (correctBtn) correctBtn.classList.add("active");
                }

                // Add click listeners to outer tabs
                document.querySelectorAll(".settings-menu button").forEach(btn => {
                    btn.addEventListener("click", function () {
                        const sectionId = btn.getAttribute("onclick").match(/'(.+?)'/)[1];
                        showSection(sectionId);
                    });
                });

                // Add click listeners to inner tabs (optional)
                document.querySelectorAll(".inner-settings-menu button").forEach(btn => {
                    btn.addEventListener("click", function () {
                        const type = btn.getAttribute("onclick").match(/'(.+?)'/)[1];
                        changeInnerTab(type);
                    });
                });
            });

            function showSection(sectionId) {
                document.querySelectorAll('.settings-section').forEach(section => {
                    section.classList.remove('active');
                });

                const section = document.getElementById(sectionId);
                if (section) section.classList.add('active');

                // Update hidden inputs
                document.querySelectorAll('input[name="current_tab"]').forEach(input => {
                    input.value = sectionId;
                });

                // Update URL
                const url = new URL(window.location);
                url.searchParams.set("tab", sectionId);
                window.history.pushState({}, "", url);
            }

            function changeInnerTab(type) {
                const url = new URL(window.location);
                url.searchParams.set("firsttab", "workSettings");
                url.searchParams.set("type", type);
                window.history.pushState({}, "", url);

                // Update button active class
                document.querySelectorAll(".inner-settings-menu button").forEach(btn =>
                    btn.classList.remove("active")
                );
                document.querySelector(`.inner-settings-menu button[onclick="changeInnerTab('${type}')"]`)
                    ?.classList.add("active");

                showInnerContent(type);
            }

            function showInnerContent(type) {
                document.querySelectorAll(".inner-tab-content").forEach(content => {
                    content.style.display = "none";
                });

                const section = document.getElementById(type + "_tab");
                if (section) section.style.display = "block";
            }

            function openFullScreen(imgElement) {
                var modal = document.getElementById("imageModal");
                var modalImg = document.getElementById("fullImage");

                modal.style.display = "block";
                modalImg.src = imgElement.src;
            }

            function closeFullScreen() {
                document.getElementById("imageModal").style.display = "none";
            }

            function toggleBackgroundInput() {
                const type = document.getElementById("background_type").value;
                document.getElementById("background_color_group").style.display = type === "color" ? "block" : "none";
                document.getElementById("background_image_group").style.display = type === "image" ? "block" : "none";
                document.getElementById("gradient_group").style.display = type === "gradient" ? "block" : "none";

            }

            function toggleBrandBackgroundInput() {
                const type = document.getElementById("brand_background_type").value;
                document.getElementById("brand_background_color_group").style.display = type === "color" ? "block" : "none";
                document.getElementById("brand_background_image_group").style.display = type === "image" ? "block" : "none";
            }

            async function updateBackgroundValue() {
                const type = document.getElementById("background_type").value;
                const hiddenInput = document.getElementById("app_background");

                if (type === "color") {
                    hiddenInput.value = document.getElementById("background_color").value;
                } else if (type === "image") {
                    const fileInput = document.getElementById("background_image");
                    if (fileInput.files.length > 0) {
                        try {
                            // Create a base64 representation of the image
                            const base64String = await getBase64(fileInput.files[0]);
                            hiddenInput.value = base64String;
                        } catch (error) {
                            console.error("Error converting image:", error);
                            hiddenInput.value = "";
                        }
                    } else {
                        hiddenInput.value = '';
                    }
                }
            }

            function getBase64(file) {
                return new Promise((resolve, reject) => {
                    const reader = new FileReader();
                    reader.readAsDataURL(file);
                    reader.onload = () => resolve(reader.result);
                    reader.onerror = error => reject(error);
                });
            }
        </script>

        <script>
            /*document.addEventListener('DOMContentLoaded', function() {
                                                                                                                                                                                                                // Updated selector to match your new class
                                                                                                                                                                                                                const radioButtons = document.querySelectorAll('.radio-input');
                                                                                                                                                                                                                const fieldsContainers = {
                                                                                                                                                                                                                    '0': document.getElementById('agora-fields'),
                                                                                                                                                                                                                    '1': document.getElementById('zego-fields'),
                                                                                                                                                                                                                    '2': document.getElementById('pusher-fields')
                                                                                                                                                                                                                };

                                                                                                                                                                                                                function toggleFields() {
                                                                                                                                                                                                                    const selectedValue = document.querySelector('input[name="library"]:checked').value;

                                                                                                                                                                                                                    // Hide all fields first
                                                                                                                                                                                                                    Object.values(fieldsContainers).forEach(container => {
                                                                                                                                                                                                                        container.style.display = 'none';
                                                                                                                                                                                                                    });

                                                                                                                                                                                                                    // Show the selected one
                                                                                                                                                                                                                    if (fieldsContainers[selectedValue]) {
                                                                                                                                                                                                                        fieldsContainers[selectedValue].style.display = 'flex';
                                                                                                                                                                                                                    }
                                                                                                                                                                                                                }

                                                                                                                                                                                                                // Add event listeners to radio buttons
                                                                                                                                                                                                                radioButtons.forEach(radio => {
                                                                                                                                                                                                                    radio.addEventListener('change', toggleFields);
                                                                                                                                                                                                                });

                                                                                                                                                                                                                // Initialize the fields visibility
                                                                                                                                                                                                                toggleFields();
                                                                                                                                                                                                            });*/


            document.addEventListener("DOMContentLoaded", function () {
                let resetButton = document.getElementById('resetColors');

                let resetApColorSettingpButton = document.getElementById('resetAppColorsSettings');

                if (resetApColorSettingpButton) {
                    resetApColorSettingpButton.addEventListener('click', function () {
                        // Reset color inputs with valid hex values
                        document.getElementById('app_primary_color').value = "#32e5ac";     // Teal-green
                        document.getElementById('background_color').value = "#FFFFFF";      // White
                        document.getElementById('background_type').value = "color";
                        document.getElementById('bottom_color').value = "#FFFFFF";
                        document.getElementById('reset').value = 1;          // White
                        document.getElementById('active_color').value = "#33FFAA";          // Teal-green
                        document.getElementById('inactive_color').value = "#D1CECE";        // Grey
                        document.getElementById('text_header_color').value = "#000000";     // Black
                        document.getElementById('button_text_color').value = "#FFFFFF";     // White

                        // Submit the form
                        document.querySelector('#appSettings form').submit();
                    });
                }

                if (resetButton) {
                    resetButton.addEventListener('click', function () {
                        let colorInputs = {
                            'primary_color': "#00FFCC",
                            'secondary_color': "#FFFFFF",
                            'text_primary_color': "#fdf8f8",
                            'text_secondary_color': "#000000",
                            'box_background_color': "#969696",
                            'table_background_color': "#c88213"
                        };

                        Object.keys(colorInputs).forEach(id => {
                            let input = document.getElementById(id);
                            if (input) {
                                input.value = colorInputs[id];
                            }
                        });

                        // Reset background type to color
                        document.getElementById('brand_background_type').value = "image";

                        // Show color input, hide image input
                        document.getElementById('brand_background_color_group').style.display = 'none';
                        document.getElementById('brand_background_image_group').style.display = 'block';

                        // Add a hidden input to explicitly set the background image to null
                        let hiddenInput = document.createElement('input');
                        hiddenInput.type = 'hidden';
                        hiddenInput.name = 'brand_background_image_reset';
                        hiddenInput.value = '1';
                        document.getElementById('themeSettingsForm').appendChild(hiddenInput);

                        // Remove any preview images
                        const imagePreviewContainer = document.querySelector(
                            '#brand_background_image_group .mt-2');
                        if (imagePreviewContainer) {
                            imagePreviewContainer.style.display = 'block';
                        }

                        // Submit the form to save changes and reload the page
                        document.getElementById('themeSettingsForm').submit();
                    });
                }
            });

            document.addEventListener("DOMContentLoaded", function () {
                document.querySelectorAll('input[type="color"]').forEach(input => {
                    input.addEventListener("input", function () {
                        this.style.background = this.value; // تحديث الخلفية
                        this.value = this.value; // تأكيد تحديث القيمة
                    });
                });
            });

            document.addEventListener('DOMContentLoaded', function () {
                document.querySelectorAll('.copy-button').forEach(function (button) {
                    button.addEventListener('click', function () {
                        const targetId = this.getAttribute('data-copy-target');
                        const input = document.getElementById(targetId);
                        if (input) {
                            input.select();
                            input.setSelectionRange(0, 99999); // For mobile
                            document.execCommand('copy');
                        }
                    });
                });
            });
        </script>

    </div>

</body>

<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-colorpicker/2.5.3/css/bootstrap-colorpicker.min.css"
      rel="stylesheet">

<script
    src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-colorpicker/2.5.3/js/bootstrap-colorpicker.min.js"></script>
<script>


    // $('.colorpicker-element').colorpicker();

    $('.colorpicker-element').colorpicker({

        align: 'left',     // Align dropdown to the right (for English dashboard)
        horizontal: true    // Show horizontal sliders
    });


    document.addEventListener("DOMContentLoaded", function () {
        const firstRow = document.querySelector('.content .row');
        if (firstRow) {
            const firstDiv = firstRow.querySelector('div');
            if (firstDiv && firstDiv.classList.contains('col-md-12')) {
                firstDiv.classList.add('col-sm-6');
            }
        }
    });
    (function () {
        const tabButtons = document.querySelectorAll('#landPageSettings .tab-btn');
        const panes = document.querySelectorAll('#landPageSettings .tab-pane');

        function activateTab(btn) {
            // deactivate all buttons
            tabButtons.forEach(b => {
                b.classList.remove('active');
                b.setAttribute('aria-selected', 'false');
            });

            // hide all panes
            panes.forEach(p => {
                p.classList.remove('show', 'active');
                p.setAttribute('aria-hidden', 'true');
            });

            // activate the clicked button
            btn.classList.add('active');
            btn.setAttribute('aria-selected', 'true');

            const target = btn.getAttribute('data-target');
            if (!target) return;

            const pane = document.querySelector(target);
            if (pane) {
                pane.classList.add('show', 'active');
                pane.setAttribute('aria-hidden', 'false');

                // optional: focus the first input element inside the tab
                const firstInput = pane.querySelector('input, select, textarea, button');
                if (firstInput) {
                    firstInput.focus({preventScroll: true});
                }
            }
        }

        // attach click listeners to tab buttons
        tabButtons.forEach(btn => {
            btn.addEventListener('click', e => {
                e.preventDefault();
                activateTab(btn);

                // smooth scroll to the tab content on mobile
                if (window.innerWidth < 768) {
                    const tabContent = document.querySelector('#landPageSettings .tab-content');
                    if (tabContent) {
                        tabContent.scrollIntoView({behavior: 'smooth'});
                    }
                }

                // update URL hash for history navigation
                const target = btn.getAttribute('data-target');
                if (target) {
                    history.replaceState(null, null, target);
                }
            });
        });

        // set the first tab active by default if none active
        const initiallyActive = document.querySelector('#landPageSettings .tab-btn.active') || tabButtons[0];
        if (initiallyActive) {
            activateTab(initiallyActive);
        }

        // allow switching tabs by URL hash (e.g., #stats)
        function checkHash() {
            if (location.hash) {
                const btn = document.querySelector('#landPageSettings .tab-btn[data-target="' + location.hash + '"]');
                if (btn) {
                    activateTab(btn);
                }
            }
        }

        window.addEventListener('hashchange', checkHash);
        checkHash();
    })();
</script>


<script>
    function toggleBackgroundInput() {
        let type = document.querySelector('[name="background_type"]').value;
        document.getElementById('background_color_group').style.display = (type === 'color') ? 'block' : 'none';
        document.getElementById('background_image_group').style.display = (type === 'image') ? 'block' : 'none';
        document.getElementById('gradient_group').style.display = (type === 'gradient') ? 'block' : 'none';
    }
</script>

<script>
    $(document).ready(function () {
        $('.select2-country').select2({
            placeholder: "{{ __('Select a country') }}",
            allowClear: true,
            width: '100%'
        });
    });
</script>
