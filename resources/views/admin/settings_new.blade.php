<!-- <!DOCTYPE html>
<html lang="en">-->
<!-- <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    </head>  -->
<!--
<body>
    <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('POST')
        @php
            $selectedTimeZone = App\Models\Setting::where('key', 'timezone')->first();
        @endphp
        <label for="">TimeZone</label>


        <button type="submit">Update</button>
    </form>
</body>
</html> -->

@php
    $selectedTimeZone = App\Models\Setting::where('key', 'timezone')->first();
    $settings = App\Models\Setting::pluck('value', 'key')->toArray();

@endphp
<style>
    /* Add this CSS to your stylesheet */
    .radio-options-container {
        display: flex;
        gap: 20px;
        /* Space between options */
        align-items: center;
        margin: 15px 0;
    }

    .radio-option {
        display: flex;
        align-items: center;
        gap: 8px;
        /* Space between radio and label */
    }

    .radio-input {
        margin: 0;
        /* Remove default margins */
    }

    .radio-label {
        margin: 0;
        /* Remove default margins */
        cursor: pointer;
        user-select: none;
    }

    /* Custom radio button styling */
    .radio-input {
        -webkit-appearance: none;
        -moz-appearance: none;
        appearance: none;
        width: 18px;
        height: 18px;
        border: 2px solid #ff9800;
        border-radius: 50%;
        outline: none;
        cursor: pointer;
        position: relative;
    }

    .radio-input:checked {
        background-color: #ff9800;
    }

    .radio-input:checked::after {
        content: '';
        position: absolute;
        width: 8px;
        height: 8px;
        background: white;
        border-radius: 50%;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
    }

    body {
        font-family: Arial, sans-serif;
        margin: 0;
        padding: 0;
        background-color: var(--secondary-color);
        color: white;
        display: flex;
    }

    /* القائمة الجانبية */
    .settings-sidebar {
        width: 250px;
        background: #222;
        min-height: 400px;
        padding: 20px;
        box-shadow: 2px 0 10px rgba(0, 0, 0, 0.5);
    }

    .settings-sidebar h2 {
        text-align: center;
        color: #ff9800;
    }

    /* .settings-menu button {
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

    .settings-menu button:hover {
        background: #ff9800;
    } */

    /* محتوى الصفحة */
    .settings-content {
        flex-grow: 1;
        padding: 20px;
    }

    .settings-section {
        display: none;
    }

    .active {
        display: block;
    }

    .settings-section.active {
        display: block;
        /* Show active section */
    }

    /* تنسيق النماذج */
    form {
        background: var(--box-background-color);
        padding: 20px;
        border-radius: 5px;
        width: 100%;
        position: relative;
        margin: auto;

    }

    @media (max-width: 768px) {
        form {
            background: #222;
            padding: 20px;
            border-radius: 5px;
            width: 100%;

            position: relative;
            margin: auto;

        }
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

    .all-page {
        display: inline-flex;
    }

    .wrapper {
        width: 100%;

    }

    .settings-content {
        width: 869px;

    }

    button {
        width: 171px;
    }

    /* تصميم النافذة */
    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        padding-top: 50px;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: var(--secondary-color);
        ;
    }

    /* الصورة داخل النافذة */
    .modal-content {
        margin: auto;
        display: block;
        width: 80%;
        max-width: 700px;
    }

    /* زر الإغلاق */
    .close {
        position: absolute;
        top: 15px;
        right: 35px;
        color: white;
        font-size: 40px;
        font-weight: bold;
        cursor: pointer;
    }

    img {
        width: 201px;
        display: block;
        height: 99px;
        margin-bottom: 20px;
    }

    /* button {
        width: 200px;

    } */

    .settings-sidebar {

        background-color: var(--table-background-color);
        display: inline;
        justify-content: center;
        align-items: center;
        padding: 10px 0;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        color: var(--text-secondary-color);
        overflow-x: auto;
        /* يجعل الشريط قابلاً للتمرير عند الحاجة */
        white-space: nowrap;
        /* يمنع العناصر من النزول لسطر جديد */
        scrollbar-width: thin;
        /* تقليل عرض شريط التمرير */
    }

    .settings-menu {
        display: flex;
        gap: 4px;
        color: var(--text-secondary-color);
        overflow-x: auto;
        /* يجعل الشريط قابلاً للتمرير عند الحاجة */
        white-space: nowrap;
        /* يمنع العناصر من النزول لسطر جديد */
        scrollbar-width: thin;
        /* تقليل عرض شريط التمرير */

    }

    .settings-menu button {
        background-color: var(--secondary-color);
        border: none;
        padding: 10px 15px;
        font-size: 16px;
        cursor: pointer;
        transition: color 0.3s ease-in-out;
        color: var(--text-secondary-color) !important;

    }

    /*.settings-menu button:hover {*/
    /*    background: var(--primary-color);*/
    /*}*/


    .card {
        border-radius: 10px;
        /* Rounded corners */
        border: 1px solid #ddd;
        /* Light border */
        background: "{{ $settings['primary_color'] ?? '#000000' }}";
        /* White background */
        box-shadow: 2px 4px 6px rgba(0, 0, 0, 0.1);
        /* Subtle shadow */
        padding: 20px;
        /* Inner spacing */
        transition: transform 0.2s ease-in-out;
        margin-top: 40px;
        /* Smooth effect */
        position: relative;

    }

    .btn-save {
        position: absolute;
        bottom: 15px;
        left: 15px;
    }

    .card:hover {
        transform: scale(1.02);
        /* Slight zoom effect on hover */
    }

    .card-header {
        background: {{ $settings['primary_color'] ?? '#000000' }};
        /* Light gray background */
        padding: 12px 15px;
        border-bottom: 1px solid #ddd;
        /* Separator */
        border-radius: 8px 8px 0 0;
        /* Rounded top corners */
        text-align: center;
        font-weight: bold;
        font-size: 1.2rem;
        color: #333;

        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .card-header h4 {
        margin: 0;
    }

    .d-flex.align-items-center {
        gap: 10px;
        /* Add spacing between radio and switch */
    }


    .custom-radio {
        display: none;
    }

    .custom-payment-radio {
        display: none;
    }

    /* Switch container */
    .switch {
        display: inline-block;
        width: 50px;
        height: 25px;
        background-color: #ccc;
        border-radius: 25px;
        position: relative;
        cursor: pointer;
        transition: background 0.3s;
    }

    /* Circle (toggle) */
    .switch::after {
        content: "";
        width: 20px;
        height: 20px;
        background: white;
        border-radius: 50%;
        position: absolute;
        top: 50%;
        left: 5px;
        transform: translateY(-50%);
        transition: left 0.3s;
    }

    /* Active state */
    .switch.active {
        background: #4caf50;
    }

    .switch.active::after {
        left: 25px;
    }

    .border-success {
        border: #4caf50, solid, 5px;
    }

    .position-relative {
        position: relative;
        overflow: visible;
    }

    .ribbon-banner {
        position: absolute;
        top: 6px;
        right: -10px;
        background-color: #ff0000;
        padding: 2px 7px;
        transform: rotate(90deg);
        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
    }
    .ribbon-banner-card {
        position: absolute;
        top: 6px;
        right: -9px;
        background-color: #ff0000;
        padding: 2px 7px;
        transform: rotate(90deg);
        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
    }
    .ribbon-banner-card span {
        color: white;
        font-size: 15px;
        font-weight: normal;
        text-shadow: 0 1px 1px rgba(0,0,0,0.2);

    }

    .rtl .ribbon-banner{
        right: auto;
        left: -11px; !important;
        padding: 2px 13px; !important;
    }

    .ribbon-banner span {
        color: white;
        font-size: 15px;
        font-weight: normal;
        text-shadow: 0 1px 1px rgba(0,0,0,0.2);
    }

    .text-center.my-3 img.img-fluid {
        max-height: 80px;
        display: unset !important;
        margin-top: 20px;
    }
    .card-top{
        margin-top: 33px;

    }
</style>

</head>

<body>

    <div class="settings-sidebar">
        <div class="settings-menu">
            <button onclick="showSection('brandSettings')">{{ __('Brand settings') }}</button>
            <button onclick="showSection('themeSettings')">{{ __('Theme settings') }}</button>
            <button onclick="showSection('timeSettings')">{{ __('Timing settings') }}</button>

            <button onclick="showSection('appSettings')" class="position-relative">
                {{ __('App settings') }}
                <div class="ribbon-banner">
                    <span>{{ __('soon') }}</span>
                </div>
            </button>

            <button onclick="showSection('realTimeSetting')">{{ __('Sound & Video') }}</button>
            <button onclick="showSection('pusherSettings')">{{ __('Real Time Setting') }}</button>
            <button onclick="showSection('paymentCredentialSettings')" class="position-relative">
                {{ __('Payment') }}
                <div class="ribbon-banner">
                    <span>{{ __('soon') }}</span>
                </div>
            </button>
        </div>
    </div>
    <div class="all-page" style="    width: 100%;">


        <div class="settings-content">
            <div id="brandSettings" class="settings-section active">

                <h3> {{ __('Brand settings') }}</h3>

                <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="form row">

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>{{ __('Application title en:') }} </label>
                                <input type="text" name="app_title_en" value="{{ $settings['app_title_en'] ?? '' }}"
                                    class="form-control">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>{{ __('Application title ar:') }} </label>
                                <input type="text" name="app_title_ar" value="{{ $settings['app_title_ar'] ?? '' }}"
                                    class="form-control">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>{{ __('Application logo:') }}</label>
                                <input type="file" name="app_logo" class="form-control"
                                    onchange="previewImage(event)">

                                <!-- Image Preview -->
                                <img id="imagePreview"
                                    src="{{ !empty($settings['app_logo']) ? getImagePath($settings['app_logo']) : '' }}"
                                    width="100" class="mt-2"
                                    style="{{ !empty($settings['app_logo']) ? '' : 'display:none;' }}"
                                    onclick="openFullScreen(this)">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>{{ __('Application Fav Icon:') }}</label>
                                <input type="file" name="app_fav_icon" class="form-control"
                                    onchange="previewFavIcon(event)">

                                <!-- Image Preview -->
                                <img id="favIconPreview"
                                    src="{{ !empty($settings['app_fav_icon']) ? getImagePath($settings['app_fav_icon']) : '' }}"
                                    width="100" class="mt-2"
                                    style="{{ !empty($settings['app_fav_icon']) ? '' : 'display:none;' }}"
                                    onclick="openFullScreen(this)">
                            </div>
                        </div>

                        <button type="submit">{{ __('save') }}</button>


                    </div>

                </form>
            </div>

            <div id="themeSettings" class="settings-section">
                <h3>{{ __('Theme settings') }}</h3>
                <form id="themeSettingsForm" action="{{ route('admin.settings.update') }}" method="POST"
                    enctype="multipart/form-data">
                    <div class="form row">
                        @csrf

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="secondary_color">{{ __('Primary Color:') }}</label>
                                <input type="color" id="secondary_color" name="secondary_color"
                                    value="{{ $settings['secondary_color'] ?? '#FFFFFF' }}"
                                    style="background: {{ $settings['secondary_color'] ?? '#FFFFFF' }};"
                                    title="اللون الثانوي المستخدم كخلفية لبعض الأقسام أو لتوضيح بعض العناصر.">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="primary_color">{{ __('Secondary Color:') }}</label>
                                <input type="color" id="primary_color" name="primary_color"
                                    value="{{ $settings['primary_color'] ?? '#000000' }}"
                                    style="background: {{ $settings['primary_color'] ?? '#000000' }};"
                                    title="لون الواجهة الرئيسي، يتم استخدامه في الأزرار والخلفيات الأساسية.">
                            </div>
                        </div>

                        {{--                        <div class="col-md-6"> --}}
                        {{--                            <div class="form-group"> --}}
                        {{--                                <label for="text_primary_color">{{ __('Text Primary Color:') }}</label> --}}
                        {{--                                <input type="color" id="text_primary_color" name="text_primary_color" --}}
                        {{--                                    value="{{ $settings['text_primary_color'] ?? '#000000' }}" --}}
                        {{--                                    style="background: {{ $settings['text_primary_color'] ?? '#000000' }};" --}}
                        {{--                                    title="لون النص الأساسي الذي يظهر في العناوين والمحتوى الرئيسي."> --}}
                        {{--                            </div> --}}
                        {{--                        </div> --}}

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="text_secondary_color">{{ __('Text Secondary Color:') }}</label>
                                <input type="color" id="text_secondary_color" name="text_secondary_color"
                                    value="{{ $settings['text_secondary_color'] ?? '#808080' }}"
                                    style="background: {{ $settings['text_secondary_color'] ?? '#808080' }};"
                                    title="لون النص الثانوي المستخدم في الشروحات أو النصوص المساعدة.">
                            </div>
                        </div>

                        {{--                        <div class="col-md-6"> --}}
                        {{--                            <div class="form-group"> --}}
                        {{--                                <label for="box_background_color">{{ __('Box Background Color:') }}</label> --}}
                        {{--                                <input type="color" id="box_background_color" name="box_background_color" --}}
                        {{--                                    value="{{ $settings['box_background_color'] ?? '#F8F9FA' }}" --}}
                        {{--                                    style="background: {{ $settings['box_background_color'] ?? '#F8F9FA' }};" --}}
                        {{--                                    title="لون خلفية الصناديق أو الكروت داخل التطبيق."> --}}
                        {{--                            </div> --}}
                        {{--                        </div> --}}
                    </div>

                    {{--                    <div class="form row"> --}}
                    {{--                        <div class="col-md-6"> --}}
                    {{--                            <div class="form-group"> --}}
                    {{--                                <label for="table_background_color">{{ __('Table Background Color:') }}</label> --}}
                    {{--                                <input type="color" id="table_background_color" name="table_background_color" --}}
                    {{--                                       value="{{ $settings['table_background_color'] ?? '#FFFFFF' }}" --}}
                    {{--                                       style="background: {{ $settings['table_background_color'] ?? '#FFFFFF' }};" --}}
                    {{--                                       title="لون خلفية الجداول في التقارير أو البيانات."> --}}
                    {{--                            </div> --}}
                    {{--                        </div> --}}
                    {{--                    </div> --}}
                    <div class="form row">
                        <!-- New Background Type Selection -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="brand_background_type">{{ __('Brand Background Type') }}</label>
                                <select id="brand_background_type" name="brand_background_type" class="form-control"
                                    onchange="toggleBrandBackgroundInput()">
                                    <option value="color"
                                        {{ ($settings['brand_background_type'] ?? 'color') === 'color' ? 'selected' : '' }}>
                                        {{ __('Color') }}
                                    </option>
                                    <option value="image"
                                        {{ ($settings['brand_background_type'] ?? '') === 'image' ? 'selected' : '' }}>
                                        {{ __('Image') }}
                                    </option>
                                </select>
                            </div>
                        </div>

                        <!-- Background Color Input -->
                        <div class="col-md-6">
                            <div class="form-group" id="brand_background_color_group"
                                style="display: {{ ($settings['brand_background_type'] ?? 'color') === 'color' ? 'block' : 'none' }};">
                                <label for="box_background_color">{{ __('Box Background Color:') }}</label>
                                <input type="color" id="box_background_color" name="box_background_color"
                                    value="{{ $settings['box_background_color'] ?? '#F8F9FA' }}"
                                    style="background: {{ $settings['box_background_color'] ?? '#F8F9FA' }};"
                                    title="لون خلفية الصناديق أو الكروت داخل التطبيق.">
                            </div>
                        </div>
                        <input type="hidden" name="brand_image" id="brand_image">

                        <!-- Background Image Input -->
                        <div class="col-md-6">
                            <div class="form-group" id="brand_background_image_group"
                                style="display: {{ ($settings['brand_background_type'] ?? '') === 'image' ? 'block' : 'none' }};">
                                <label for="brand_background_image">{{ __('Brand Background Image') }}</label>
                                <input onchange="choose_image()" type="file" id="brand_background_image"
                                    name="brand_background_image" class="form-control">
                                <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                                    @foreach ($brand_images as $img)
                                        <img class="image_success @if (!empty($settings['brand_background_image']) && $img->name == $settings['brand_background_image']) border-success @endif"
                                            onclick="select_brand_image('{{ $img->name }}', this)"
                                            src="{{ !empty($img->name) ? getImagePath($img->name) : '' }}"
                                            alt="" style="width: 50px; height: 50px; cursor: pointer;">
                                    @endforeach
                                </div>
                                {{-- @if (!empty($settings['brand_background_image']) && ($settings['brand_background_type'] ?? '') === 'image')
                                    <div class="mt-2">
                                        <img id="imagePreview"
                                            src="{{ !empty($settings['brand_background_image']) ? getImagePath($settings['brand_background_image']) : '' }}"
                                            width="100" class="mt-2"
                                            style="{{ !empty($settings['app_logo']) ? '' : 'display:none;' }}"
                                            onclick="openFullScreen(this)">

                                    </div>
                                @endif --}}
                            </div>
                        </div>

                    </div>

                    <div class="col-12 d-flex gap-3 mt-3">
                        <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
                        <button type="button" id="resetColors"
                            class="btn btn-secondary">{{ __('Reset Colors') }}</button>
                    </div>

                </form>
            </div>

            <div id="timeSettings" class="settings-section">
                <h3>{{ __('Timing settings') }}</h3>
                <form action="{{ route('admin.settings.update') }}" method="POST">
                    @csrf
                    <div class="form">

                        <label>{{ __('Time zone:') }}</label>
                        <select name="timezone">
                            @foreach ($timezones as $timezone)
                                <option value="{{ $timezone->name }}"
                                    {{ $timezone->name == ($settings['timezone'] ?? '') ? 'selected' : '' }}>
                                    {{ $timezone->name }} ({{ $timezone->offset }})
                                </option>
                            @endforeach
                        </select>

                        <button type="submit">{{ __('save') }}</button>
                    </div>
                </form>
            </div>
            <div id="realTimeSetting" class="settings-section">
                <form action="{{ route('admin.update-agora-zego') }}" method="POST">
                    @csrf
                    <div class="form">
                        <label class="d-block">{{ __('Sound & Video System Setting:') }}</label>

                        <div class="row mt-4">
                            <!-- Agora Fields -->
                            <div class="col-md-6 mb-3 ms-0 me-auto">
                                <div class="card p-3 shadow" style="height: 300px;">
                                    <div class="card-header d-flex justify-content-between align-items-center  ">
                                        <h4 class="m-0">{{ __('admin.Agora') }}</h4>
                                        <div class="d-flex align-items-center">
                                            <input type="radio" id="agoraRadio"
                                                class="custom-radio libraryRealTime" name="library" value="0"
                                                {{ $library == '0' ? 'checked' : '' }}>
                                            <label for="agoraRadio" class="switch"></label>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="agora_app_id">{{ __('admin.app_id') }}:</label>
                                                <input type="text" id="agora_app_id" name="app_id"
                                                    placeholder="app_id" value="{{ $agora_app_id }}"
                                                    class="form-control">
                                            </div>
                                        </div>
                                    </div>
                                    <button type="submit"
                                        class="btn btn-primary mt-3 btn-save">{{ __('save') }}</button>
                                </div>
                            </div>

                            <!-- Zego Fields -->
                            <div class="col-md-6 mb-3 ms-0 me-auto">
                                <div class="card p-3 shadow" style="height: 300px;">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h4 class="m-0">{{ __('admin.Zego') }}</h4>
                                        <div class="d-flex align-items-center">
                                            <input type="radio" id="zegoRadio" class="custom-radio libraryRealTime"
                                                name="library" value="1" {{ $library == '1' ? 'checked' : '' }}>
                                            <label for="zegoRadio" class="switch"></label>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label
                                                    for="zego_server_secret">{{ __('admin.server_secret') }}:</label>
                                                <input type="text" id="zego_server_secret"
                                                    name="zego_server_secret" placeholder="server_secret"
                                                    value="{{ $zego_server_secret }}" class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="zego_app_id">{{ __('admin.app_id') }}:</label>
                                                <input type="text" id="zego_app_id" name="zego_app_id"
                                                    placeholder="app_id" value="{{ $zego_app_id }}"
                                                    class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="app_sign">{{ __('admin.app_sign') }}:</label>
                                                <input type="text" id="app_sign" name="app_sign"
                                                    placeholder="app_sign" value="{{ $app_sign }}"
                                                    class="form-control">
                                            </div>
                                        </div>
                                    </div>
                                    <button type="submit"
                                        class="btn btn-primary mt-3 btn-save">{{ __('save') }}</button>
                                </div>
                            </div>

                            <!-- Tencent Fields -->
                            <div class="col-md-6 mb-3 ms-0 me-auto">
                                <div class="card p-3 shadow" style="height: 300px;">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h4 class="m-0">{{ __('admin.Tencent') }}</h4>
                                        <div class="ribbon-banner-card">
                                            <span>{{ __('soon') }}</span>
                                        </div>
                                        <div class="d-flex align-items-center">
                                            <input type="radio" id="tencentRadio" class="custom-radio libraryRealTime"
                                                   name="library" value="1" {{ $library == '1' ? 'checked' : '' }}>
                                            <label for="tencentRadio" class="switch"></label>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label
                                                    for="tencent_server_secret">{{ __('admin.server_secret') }}:</label>
                                                <input type="text" id="tencent_server_secret"
                                                       name="tencent_server_secret" placeholder="server_secret"
                                                       value="{{ $tencent_server_secret }}" class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="tencent_app_id">{{ __('admin.app_id') }}:</label>
                                                <input type="text" id="tencent_app_id" name="tencent_app_id"
                                                       placeholder="app_id" value="{{ $tencent_app_id }}"
                                                       class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="app_sign">{{ __('admin.app_sign') }}:</label>
                                                <input type="text" id="app_sign" name="app_sign"
                                                       placeholder="app_sign" value="{{ $app_sign }}"
                                                       class="form-control">
                                            </div>
                                        </div>
                                    </div>
                                    <button type="submit"
                                            class="btn btn-primary mt-3 btn-save">{{ __('save') }}</button>
                                </div>
                            </div>
                        </div>
                        {{-- <div class="row">
                            <!-- Pusher Fields -->
                            <div class="col-md-6 mb-3 ms-0 me-auto" >
                                <div class="card p-3 shadow" style="height: 200px;">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h4 class="m-0">{{ __('pusher') }}</h4>
                                        <div class="d-flex align-items-center">
                                            <input type="radio" id="pusherRadio" class="custom-radio" name="library" value="2"
                                                {{ $library == '2' ? 'checked' : '' }}>
                                            <label for="pusherRadio" class="switch"></label>
                                        </div>
                                    </div>
                                    <button type="submit" class="btn btn-primary mt-3 btn-save">{{ __('save') }}</button>
                                </div>
                            </div>
                        </div> --}}
                    </div>
                </form>

                <form action="{{ route('admin.update-agora-zego') }}" method="POST">
                    @csrf
                    <div class="form">
                        <label class="d-block">{{ __('Sound System Setting:') }}</label>

                        <div class="row mt-4">
                            <!-- Agora Fields -->
                            <div class="col-md-4 mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h4 class="m-0">{{ __('admin.Agora') }}</h4>
                                    <div class="d-flex align-items-center">
                                        <input type="radio" id="agoraRadio"
                                               class="custom-radio libraryRealTime" name="library" value="0"
                                            {{ $library == '0' ? 'checked' : '' }}>
                                        <label for="agoraRadio" class="switch"></label>
                                    </div>
                                </div>
                            </div>

                            <!-- Zego Fields -->
                            <div class="col-md-4 mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h4 class="m-0">{{ __('admin.Zego') }}</h4>
                                    <div class="d-flex align-items-center">
                                        <input type="radio" id="zegoRadio" class="custom-radio libraryRealTime"
                                               name="library" value="1" {{ $library == '1' ? 'checked' : '' }}>
                                        <label for="zegoRadio" class="switch"></label>
                                    </div>
                                </div>
                            </div>

                            <!-- Tencent Fields -->
                            <div class="col-md-4 mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h4 class="m-0">{{ __('admin.Tencent') }}</h4>
                                    <div class="d-flex align-items-center">
                                        <input type="radio" id="tencentRadio" class="custom-radio libraryRealTime"
                                               name="library" value="2" {{ $library == '2' ? 'checked' : '' }}>
                                        <label for="tencentRadio" class="switch"></label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>

                <form action="{{ route('admin.update-agora-zego') }}" method="POST">
                    @csrf
                    <div class="form">
                        <label class="d-block">{{ __('Video System Setting:') }}</label>

                        <div class="row mt-4">
                            <!-- Agora Fields -->
                            <div class="col-md-4 mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h4 class="m-0">{{ __('admin.Agora') }}</h4>
                                    <div class="d-flex align-items-center">
                                        <input type="radio" id="agoraRadio"
                                               class="custom-radio libraryRealTime" name="library" value="0"
                                            {{ $library == '0' ? 'checked' : '' }}>
                                        <label for="agoraRadio" class="switch"></label>
                                    </div>
                                </div>
                            </div>

                            <!-- Zego Fields -->
                            <div class="col-md-4 mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h4 class="m-0">{{ __('admin.Zego') }}</h4>
                                    <div class="d-flex align-items-center">
                                        <input type="radio" id="zegoRadio" class="custom-radio libraryRealTime"
                                               name="library" value="1" {{ $library == '1' ? 'checked' : '' }}>
                                        <label for="zegoRadio" class="switch"></label>
                                    </div>
                                </div>
                            </div>

                            <!-- Tencent Fields -->
                            <div class="col-md-4 mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h4 class="m-0">{{ __('admin.Tencent') }}</h4>
                                    <div class="d-flex align-items-center">
                                        <input type="radio" id="tencentRadio" class="custom-radio libraryRealTime"
                                               name="library" value="2" {{ $library == '2' ? 'checked' : '' }}>
                                        <label for="tencentRadio" class="switch"></label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>

            </div>

            <div id="paymentCredentialSettings" class="settings-section">

                    <div class="form">
                        <label class="d-block">{{ __('Payment Credential Settings:') }}</label>

                        <div class="row mt-4">
                            <!-- Fawry Fields -->
                            <div class="col-md-6 mb-3 ms-0 me-auto">
                                <form action="{{ route('admin.settings.update') }}" method="POST">
                                    @csrf
                                <div class="card p-3 shadow" style="height: 495px;">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h4 class="m-0">{{ __('admin.fawry') }}</h4>
                                        <div class="d-flex align-items-center">
                                            <input type="hidden" name="is_fawry_active" value="0">
                                            <input type="checkbox" id="fawryRadio"
                                                class="custom-payment-radio libraryRealTime" name="is_fawry_active"
                                                value="1"
                                                {{ @$settings['is_fawry_active'] == '1' ? 'checked' : '' }}>
                                            <label for="fawryRadio" class="switch"></label>
                                        </div>
                                    </div>
                                    <div class="text-center my-3">
                                        @php
                                            $fawryImageFound = false;
                                        @endphp

                                        @foreach($paymentCoins as $coin)
                                            @if($coin->title == 'fawry')
                                            <img src="{{ asset('images/fawry.jpeg') }}"
                                            alt="Fawry Payment"
                                            style="border-radius: 50%; width: 100px; height: 100px; object-fit: cover; display: block; margin: 3px auto;">

                                                @php
                                                    $fawryImageFound = true;
                                                @endphp
                                            @endif
                                        @endforeach

                                        @if(!$fawryImageFound)
                                            <img src="{{ asset('images/dollar.jpg') }}"
                                                 alt="Fawry Payment"
                                                 class="img-fluid">
                                        @endif
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label
                                                    for="fawry_secret">{{ __('admin.server_secret') }}:</label>
                                                <input type="text" id="fawry_secret"
                                                       name="fawry_secret" placeholder="secret"
                                                       value="{{ $settings['fawry_secret'] ?? ''}}" class="form-control" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="fawry_merchant_code">{{ __('admin.merchant_code') }}:</label>
                                                <input type="text" id="fawry_merchant_code" name="fawry_merchant_code"
                                                       placeholder="merchant_code" value="{{ $settings['fawry_merchant_code'] ?? ''}}"
                                                       class="form-control" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="fawry_utd_url">{{ __('admin.utd_url') }}:</label>
                                                <input type="text" id="fawry_utd_url" name="fawry_utd_url"
                                                       placeholder="utd_url" value="{{ $settings['fawry_utd_url'] ?? ''}}"
                                                       class="form-control" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="fawry_return_url">{{ __('admin.return_url') }}:</label>
                                                <input type="text" id="fawry_return_url" name="fawry_return_url"
                                                       placeholder="return_url" value="{{ $settings['fawry_return_url'] ?? ''}}"
                                                       class="form-control" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="fawry_url">{{ __('admin.fawry_url') }}:</label>
                                                <input type="text" id="fawry_url" name="fawry_url"
                                                       placeholder="fawry_url" value="{{ $settings['fawry_url'] ?? ''}}"
                                                       class="form-control" required>
                                            </div>
                                        </div>
                                    </div>
                                    <button type="submit"
                                        class="btn btn-primary mt-3 btn-save">{{ __('save') }}</button>
                                </div>
                                </form>
                            </div>

                            <!-- skyPay Fields -->
                            <div class="col-md-6 mb-3 ms-0 me-auto">
                                <form action="{{ route('admin.settings.update') }}" method="POST">
                                    @csrf
                                <div class="card p-3 shadow" style="height: 495px;">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h4 class="m-0">{{ __('admin.skyPay') }}</h4>
                                        <div class="d-flex align-items-center">
                                            <input type="hidden" name="is_skyPay_active" value="0">
                                            <input type="checkbox" id="skyPayRadio"
                                                class="custom-payment-radio libraryRealTime" name="is_skyPay_active"
                                                value="1" {{ @$settings['is_skyPay_active'] ? 'checked' : '' }}>
                                            <label for="skyPayRadio" class="switch"></label>
                                        </div>
                                    </div>
                                    <div class="text-center my-3">
                                        @php
                                            $skypayImageFound = false;
                                        @endphp

                                        @foreach($paymentCoins as $coin)
                                            @if($coin->title == 'sky pay')
                                                <img src="{{ asset('images/paysky.png') }}"
                                                     alt="Skypay Payment"
                                                     style="border-radius: 50%; width: 100px; height: 100px; object-fit: cover; display: block; margin: 3px auto;">
                                                @php
                                                    $skypayImageFound = true;
                                                @endphp
                                            @endif
                                        @endforeach

                                        @if(!$skypayImageFound)
                                        <img src="{{ asset('images/paysky.png') }}"
                                        alt="Skypay Payment"
                                        style="border-radius: 50%; width: 100px; height: 100px; object-fit: cover; display: block; margin: 3px auto;">
                                        @endif
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label
                                                    for="paysky_base_url">{{ __('admin.base_url') }}:</label>
                                                <input type="text" id="paysky_base_url"
                                                       name="paysky_base_url" placeholder="base_url"
                                                       value="{{ $settings['paysky_base_url'] ?? ''}}" class="form-control" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="paysky_merchant_id">{{ __('admin.merchant_id') }}:</label>
                                                <input type="text" id="paysky_merchant_id" name="paysky_merchant_id"
                                                       placeholder="merchant_id" value="{{ $settings['paysky_merchant_id'] ?? ''}}"
                                                       class="form-control" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="paysky_terminal_id">{{ __('admin.terminal_id') }}:</label>
                                                <input type="text" id="paysky_terminal_id" name="paysky_terminal_id"
                                                       placeholder="terminal_id" value="{{ $settings['paysky_terminal_id'] ?? ''}}"
                                                       class="form-control" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="paysky_api_key">{{ __('admin.api_key') }}:</label>
                                                <input type="text" id="paysky_api_key" name="paysky_api_key"
                                                       placeholder="api_key" value="{{ $settings['paysky_api_key'] ?? ''}}"
                                                       class="form-control" required>
                                            </div>
                                        </div>
                                    </div>
                                    <button type="submit"
                                        class="btn btn-primary mt-3 btn-save">{{ __('save') }}</button>
                                </div>
                                </form>
                            </div>

                            <!-- stripe Fields -->
                            <div class="col-md-6 mb-3 ms-0 me-auto">
                                <form action="{{ route('admin.settings.update') }}" method="POST">
                                    @csrf
                                <div class="card p-3 shadow" style="height: 495px;">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h4 class="m-0">{{ __('admin.stripe') }}</h4>
                                        <div class="d-flex align-items-center">
                                            <input type="hidden" name="is_stripe_active" value="0">
                                            <input type="checkbox" id="stripeRadio"
                                                class="custom-payment-radio libraryRealTime" name="is_stripe_active"
                                                value="1" {{ @$settings['is_stripe_active'] ? 'checked' : '' }}>
                                            <label for="stripeRadio" class="switch"></label>
                                        </div>
                                    </div>
                                    <div class="text-center my-3">
                                        @php
                                            $stripeImageFound = false;
                                        @endphp

                                        @foreach($paymentCoins as $coin)
                                            @if($coin->title == 'stripe')
                                                <img src="{{ asset('images/stripe.png') }}"
                                                     alt="Stripe Payment"
                                                     style="border-radius: 50%; width: 100px; height: 100px; object-fit: cover; display: block; margin: 3px auto;">
                                                @php
                                                    $stripeImageFound = true;
                                                @endphp
                                            @endif
                                        @endforeach

                                        @if(!$stripeImageFound)
                                        <img src="{{ asset('images/stripe.png') }}"
                                        alt="Stripe Payment"
                                        style="border-radius: 50%; width: 100px; height: 100px; object-fit: cover; display: block; margin: 3px auto;">
                                        @endif
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label
                                                    for="stripe_test_secret_key">{{ __('admin.test_secret_key') }}:</label>
                                                <input type="text" id="stripe_test_secret_key"
                                                       name="stripe_test_secret_key" placeholder="test_secret_key"
                                                       value="{{ $settings['stripe_test_secret_key'] ?? ''}}" class="form-control" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="stripe_success_url">{{ __('admin.success_url') }}:</label>
                                                <input type="text" id="stripe_success_url" name="stripe_success_url"
                                                       placeholder="success_url" value="{{ $settings['stripe_success_url'] ?? ''}}"
                                                       class="form-control" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="stripe_cancel_url">{{ __('admin.cancel_url') }}:</label>
                                                <input type="text" id="stripe_cancel_url" name="stripe_cancel_url"
                                                       placeholder="cancel_url" value="{{ $settings['stripe_cancel_url'] ?? ''}}"
                                                       class="form-control" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="stripe_currency">{{ __('admin.currency') }}:</label>
                                                <input type="text" id="stripe_currency" name="stripe_currency"
                                                       placeholder="currency" value="{{ $settings['stripe_currency'] ?? ''}}"
                                                       class="form-control" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="stripe_webhook_secret">{{ __('admin.webhook_secret') }}:</label>
                                                <input type="text" id="stripe_webhook_secret" name="stripe_webhook_secret"
                                                       placeholder="webhook_secret" value="{{ $settings['stripe_webhook_secret'] ?? ''}}"
                                                       class="form-control" required>
                                            </div>
                                        </div>
                                    </div>
                                    <button type="submit"
                                        class="btn btn-primary mt-3 btn-save">{{ __('save') }}</button>
                                </div>
                                </form>
                            </div>

                            <!-- opay Fields -->
                            <div class="col-md-6 mb-3 ms-0 me-auto">
                                <form action="{{ route('admin.settings.update') }}" method="POST">
                                    @csrf
                                <div class="card p-3 shadow" style="height: 495px;">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h4 class="m-0">{{ __('admin.opay') }}</h4>
                                        <div class="d-flex align-items-center">
                                            <input type="hidden" name="is_opay_active" value="0">
                                            <input type="checkbox" id="opayRadio"
                                                class="custom-payment-radio libraryRealTime" name="is_opay_active"
                                                value="1"
                                                {{ @$settings['is_opay_active'] == '1' ? 'checked' : '' }}>
                                            <label for="opayRadio" class="switch"></label>
                                        </div>
                                    </div>
                                    <div class="text-center my-3">
                                        @php
                                            $opayImageFound = false;
                                        @endphp

                                        @foreach($paymentCoins as $coin)
                                            @if($coin->title == 'opay')
                                                <img src="{{ asset('images/opay.png') }}"
                                                     alt="Opay Payment"
                                                     style="border-radius: 50%; width: 100px; height: 100px; object-fit: cover; display: block; margin: 3px auto;">
                                                @php
                                                    $opayImageFound = true;
                                                @endphp
                                            @endif
                                        @endforeach

                                        @if(!$opayImageFound)
                                        <img src="{{ asset('images/opay.png') }}"
                                        alt="Opay Payment"
                                        style="border-radius: 50%; width: 100px; height: 100px; object-fit: cover; display: block; margin: 3px auto;">
                                        @endif
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="opay_currency">{{ __('admin.currency') }}:</label>
                                                <input type="text" id="opay_currency" name="opay_currency"
                                                       placeholder="currency" value="{{ $settings['opay_currency'] ?? ''}}"
                                                       class="form-control" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="opay_secret_key">{{ __('admin.server_secret') }}:</label>
                                                <input type="text" id="opay_secret_key" name="opay_secret_key"
                                                    placeholder="server_secret"
                                                    value="{{ $settings['opay_secret_key'] ?? '' }}"
                                                    class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label
                                                    for="opay_public_key">{{ __('admin.public_key') }}:</label>
                                                <input type="text" id="opay_public_key"
                                                       name="opay_public_key" placeholder="public_key"
                                                       value="{{ $settings['opay_public_key'] ?? ''}}" class="form-control" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label
                                                    for="opay_merchant_id">{{ __('admin.merchant_id') }}:</label>
                                                <input type="text" id="opay_merchant_id"
                                                       name="opay_merchant_id" placeholder="merchant_id"
                                                       value="{{ $settings['opay_merchant_id'] ?? ''}}" class="form-control" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label
                                                    for="opay_country_code">{{ __('admin.country_code') }}:</label>
                                                <input type="text" id="opay_country_code"
                                                       name="country_code" placeholder="server_secret"
                                                       value="{{ $settings['country_code'] ?? ''}}" class="form-control" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label
                                                    for="opay_base_url">{{ __('admin.base_url') }}:</label>
                                                <input type="text" id="opay_base_url"
                                                       name="opay_base_url" placeholder="base_url"
                                                       value="{{ $settings['opay_base_url'] ?? ''}}" class="form-control" required>
                                            </div>
                                        </div>
                                    </div>
                                    <button type="submit"
                                        class="btn btn-primary mt-3 btn-save">{{ __('save') }}</button>
                                </div>
                                </form>
                            </div>
                        </div>
                        {{-- <div class="row">
                            <!-- Pusher Fields -->
                            <div class="col-md-6 mb-3 ms-0 me-auto" >
                                <div class="card p-3 shadow" style="height: 200px;">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h4 class="m-0">{{ __('pusher') }}</h4>
                                        <div class="d-flex align-items-center">
                                            <input type="radio" id="pusherRadio" class="custom-radio" name="library" value="2"
                                                {{ $library == '2' ? 'checked' : '' }}>
                                            <label for="pusherRadio" class="switch"></label>
                                        </div>
                                    </div>
                                    <button type="submit" class="btn btn-primary mt-3 btn-save">{{ __('save') }}</button>
                                </div>
                            </div>
                        </div> --}}
                    </div>
            </div>




            <div id="appSettings" class="settings-section">
                <h3>{{ __('App settings') }}</h3>
                <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="form row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <div style="display: inline-flex; align-items: center; gap: 6px;">
                                    <label for="primary_color">{{ __('Primary Color') }}</label>
                                    <span onclick="reseting('app_primary_color', '#32e5ac')">
                                        <i class="fa fa-repeat"></i>
                                    </span>
                                </div>
                                <input type="color" id="app_primary_color" name="app_primary_color"
                                    value="{{ $settings['app_primary_color'] ?? '#3498db' }}" class="form-control">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <div style="display: inline-flex; align-items: center; gap: 6px;">

                                    <label for="second_color">{{ __('Second Color') }}</label>
                                    <span onclick="reseting('app_second_color', '#003FA6')">
                                        <i class="fa fa-repeat"></i>
                                    </span>
                                </div>
                                <input type="color" id="app_second_color" name="app_second_color"
                                    value="{{ $settings['app_second_color'] ?? '#2ecc71' }}" class="form-control">
                            </div>
                        </div>


                        <div class="col-md-6">
                            <div class="form-group">
                                <div style="display: inline-flex; align-items: center; gap: 6px;">

                                    <label for="white_color">{{ __('White Color') }}</label>
                                    <span onclick="reseting('app_white_color', '#ffffff')">
                                        <i class="fa fa-repeat"></i>
                                    </span>
                                </div>
                                <input type="color" id="app_white_color" name="app_white_color"
                                    value="{{ $settings['app_white_color'] ?? '#ffffff' }}" class="form-control">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <div style="display: inline-flex; align-items: center; gap: 6px;">

                                    <label for="black_color">{{ __('Black Color') }}</label>
                                    <span onclick="reseting('app_black_color', '#000000')">
                                        <i class="fa fa-repeat"></i>
                                    </span>
                                </div>
                                <input type="color" id="app_black_color" name="app_black_color"
                                    value="{{ $settings['app_black_color'] ?? '#000000' }}" class="form-control">
                            </div>
                        </div>


                        <div class="col-md-6">
                            <div class="form-group">
                                <div style="display: inline-flex; align-items: center; gap: 6px;">

                                    <label for="grey_color">{{ __('Grey Color') }}</label>
                                    <span onclick="reseting('app_grey_color','#a5a7a4')">
                                        <i class="fa fa-repeat"></i>
                                    </span>
                                </div>
                                <input type="color" id="app_grey_color" name="app_grey_color"
                                    value="{{ $settings['app_grey_color'] ?? '#808080' }}" class="form-control">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <div style="display: inline-flex; align-items: center; gap: 6px;">

                                    <label for="yellow_color">{{ __('Yellow Color') }}</label>
                                    <span onclick="reseting('app_yellow_color', '#FFAD38')">
                                        <i class="fa fa-repeat"></i>
                                    </span>
                                </div>
                                <input type="color" id="app_yellow_color" name="app_yellow_color"
                                    value="{{ $settings['app_yellow_color'] ?? '#ffff00' }}" class="form-control">
                            </div>
                        </div>




                        {{-- <div class="col-md-6">
                            <div class="form-group">
                                <label for="background_type">{{ __('Background Type') }}</label>
                                <select id="background_type" name="background_type" class="form-control"
                                    onchange="toggleBackgroundInput()">
                                    <option value="color"
                                        {{ @$settings['background_type'] === 'color' ? 'selected' : '' }}>
                                        {{ __('Color') }}</option>
                                    <option value="image"
                                        {{ @$settings['background_type'] === 'image' ? 'selected' : '' }}>
                                        {{ __('Image') }}</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group" id="background_color_group"
                                style="display: {{ ($settings['background_type'] ?? 'color') === 'color' ? 'block' : 'none' }};">
                                <label for="background_color">{{ __('Background Color') }}</label>
                                <input type="color" id="background_color" name="background_color"
                                    class="form-control" value="{{ $settings['background_color'] ?? '#ffffff' }}">
                            </div>
                        </div> --}}

                        {{-- <div class="col-md-6">
                            <div class="form-group" id="background_image_group"
                                style="display: {{ ($settings['background_type'] ?? '') === 'image' ? 'block' : 'none' }};">
                                <label for="background_image">{{ __('Background Image') }}</label>
                                <input type="file" id="background_image" name="app_background_image"
                                    class="form-control">
                                @if (!empty($settings['app_background']) && ($settings['background_type'] ?? '') === 'image')
                                    <div class="mt-2">
                                        <img src="{{ asset($settings['app_background']) }}" width="100"
                                            class="img-thumbnail">
                                    </div>
                                @endif
                            </div>
                        </div> --}}
                    </div>

                    <div class="col-12 d-flex gap-3 mt-3">
                        <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
                        <button type="button" id="resetAppColors"
                            class="btn btn-secondary">{{ __('Reset Colors') }}</button>
                    </div>

                </form>
            </div>
            <div id="pusherSettings" class="settings-section">
                <h3>{{ __('Real Time Setting') }}</h3>
             
                <div class="row" style="
                background-color:var(--box-background-color)!important;

                ">
                <div class="col-md-6 mb-3 ms-0 me-auto">

    <!-- Pusher Form -->
    <form action="{{ route('admin.update-agora-zego') }}" method="POST" class="mb-4">
        @csrf
        <div class="card p-3 shadow" style="height: 400px;">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="m-0">{{ __('pusher') }}</h4>
                <div class="d-flex align-items-center">
                    <input type="radio" id="pusherRadio" class="custom-radio pusherLib" name="library" value="2" {{ $library == '2' ? 'checked' : '' }}>
                    <label for="pusherRadio" class="switch"></label>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="pusher_app_id">{{ __('pusher_app_id') }}:</label>
                        <input type="text" id="pusher_app_id" name="pusher_app_id" placeholder="pusher_app_id" value="{{ $pusher_app_id }}" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="pusher_app_key">{{ __('pusher_app_key') }}:</label>
                        <input type="text" id="pusher_app_key" name="pusher_app_key" placeholder="pusher_app_key" value="{{ $pusher_app_key }}" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="pusher_app_secret">{{ __('pusher_app_secret') }}:</label>
                        <input type="text" id="pusher_app_secret" name="pusher_app_secret" placeholder="pusher_app_secret" value="{{ $pusher_app_secret }}" class="form-control" required>
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-primary mt-5 btn-save">{{ __('save') }}</button>
        </div>
    </form>
</div>
<div class="col-md-6 mb-3 ms-0 me-auto">

    <!-- Firebase Form -->
    <form action="{{ route('admin.update-agora-zego') }}" method="POST" class="mb-4">
        @csrf
        <div class="card p-3 shadow" style="height: 400px;">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="m-0">{{ __('firebase') }}</h4>
                <div class="ribbon-banner-card">
                    <span>{{ __('soon') }}</span>
                </div>
                <div class="d-flex align-items-center">
                    <input type="radio" id="firebaseRadio" class="custom-radio firebaseLib" name="library" value="1" {{ $library == '1' ? 'checked' : '' }}>
                    <label for="firebaseRadio" class="switch"></label>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="firebase_api_key">{{ __('firebase_api_key') }}:</label>
                        <input type="text" id="firebase_api_key" name="firebase_api_key" placeholder="{{ __('firebase_api_key') }}" value="{{ $firebase_api_key }}" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="firebase_auth_domain">{{ __('firebase_auth_domain') }}:</label>
                        <input type="text" id="firebase_auth_domain" name="firebase_auth_domain" placeholder="{{ __('firebase_auth_domain') }}" value="{{ $firebase_auth_domain }}" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="firebase_database_url">{{ __('firebase_database_url') }}:</label>
                        <input type="text" id="firebase_database_url" name="firebase_database_url" placeholder="{{ __('firebase_database_url') }}" value="{{ $firebase_database_url }}" class="form-control" required>
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-primary mt-5 btn-save">{{ __('save') }}</button>
        </div>
    </form>
</div>
<div class="col-md-6 mb-3 ms-0 me-auto card-top">

    <!-- Supabase Form -->
    <form action="{{ route('admin.update-agora-zego') }}" method="POST">
        @csrf
        <div class="card p-3 shadow" style="height: 400px;">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="m-0">{{ __('supabase') }}</h4>
                <div class="ribbon-banner-card">
                    <span>{{ __('soon') }}</span>
                </div>
                <div class="d-flex align-items-center">
                    <input type="radio" id="supabaseRadio" class="custom-radio supabaseLib" name="library" value="3" {{ $library == '3' ? 'checked' : '' }}>
                    <label for="supabaseRadio" class="switch"></label>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="supabase_url">{{ __('supabase_url') }}:</label>
                        <input type="text" id="supabase_url" name="supabase_url" placeholder="{{ __('supabase_url') }}" value="{{ $supabase_url }}" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="supabase_key">{{ __('supabase_key') }}:</label>
                        <input type="text" id="supabase_key" name="supabase_key" placeholder="{{ __('supabase_key') }}" value="{{ $supabase_key }}" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="supabase_service_role_key">{{ __('supabase_service_role_key') }}:</label>
                        <input type="text" id="supabase_service_role_key" name="supabase_service_role_key" placeholder="{{ __('supabase_service_role_key') }}" value="{{ $supabase_service_role_key }}" class="form-control" required>
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-primary mt-5 btn-save">{{ __('save') }}</button>
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
                function reseting(colorid, value) {
                    $('#' + colorid).val(value)
                }
            </script>
            <script>
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
                        success: function(response) {
                            console.log('Uploading image is true');
                            toastr.success('Library preference saved!');
                        },
                        error: function(xhr) {
                            console.error('Failed to Upload image');
                            toastr.error('Failed to Upload image');
                        }
                    });
                }
            </script>
            <script>
                function updateLibrary(selectedLibrary) {
                    $.ajax({
                        url: "{{ route('admin.update-agora-zego') }}",
                        type: "POST",
                        data: {
                            _token: "{{ csrf_token() }}",
                            library: selectedLibrary
                        },
                        success: function(response) {
                            console.log("Library updated via AJAX:", response);
                            toastr.success('Library preference saved!');
                        },
                        error: function(xhr) {
                            console.error("AJAX Error:", xhr.responseText);
                            toastr.error('Failed to update library');
                        }
                    });
                }

                function updateSwitches() {
                    $(".custom-radio").each(function() {
                        if ($(this).is(":checked")) {
                            $(this).next(".switch").addClass("active");
                        } else {
                            $(this).next(".switch").removeClass("active");
                        }
                    });
                }

                function updatePaymentSwitches() {
                    $(".custom-payment-radio").each(function() {
                        if ($(this).is(":checked")) {
                            $(this).next(".switch").addClass("active");
                        } else {
                            $(this).next(".switch").removeClass("active");
                        }
                    });
                }

                // Handle change event on radio buttons
                $(document).on("change", ".custom-radio", function() {
                    let selectedLibrary = $(this).val();
                    console.log("Selected library:", selectedLibrary);
                    updateLibrary(selectedLibrary);
                    updateSwitches();
                });

                $(document).on("change", ".custom-payment-radio", function() {
                    updatePaymentSwitches();
                });

                // Handle click on switch to activate the corresponding radio button
                $(document).on("click", ".switch", function() {
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
                $(document).ready(function() {
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
                        reader.onload = function(e) {
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
                        reader.onload = function(e) {
                            let preview = document.getElementById('favIconPreview');
                            preview.src = e.target.result; // Update preview with new image
                            preview.style.display = 'block'; // Show image
                        };
                        reader.readAsDataURL(file);
                    }
                }

                document.addEventListener("DOMContentLoaded", function() {
                    // Function to get query parameter by name
                    function getQueryParam(name) {
                        const urlParams = new URLSearchParams(window.location.search);
                        return urlParams.get(name);
                    }

                    // Get the 'firsttab' parameter from URL or default to 'brandSettings'
                    const activeTab = getQueryParam("firsttab") || "brandSettings";

                    // Show the selected tab
                    showSection(activeTab);
                });

                function showSection(sectionId) {
                    // Remove active class from all sections
                    document.querySelectorAll('.settings-section').forEach(section => {
                        section.classList.remove('active');
                    });

                    // Add active class to the selected section
                    document.getElementById(sectionId).classList.add('active');

                    // Reset button styles
                    document.querySelectorAll('.settings-menu button').forEach(button => {
                        button.style.backgroundColor = '';
                        button.style.color = '';
                    });

                    // Highlight the active button
                    const activeButton = document.querySelector(`.settings-menu button[onclick="showSection('${sectionId}')"]`);
                    if (activeButton) {
                        activeButton.style.backgroundColor = 'var(--primary-color)';
                        activeButton.style.color = 'var(--text-secondary-color)';
                    }

                    // Update the URL with the selected tab without reloading
                    const url = new URL(window.location);
                    url.searchParams.set("firsttab", sectionId);
                    window.history.pushState({}, "", url);
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
                            hiddenInput.value = "";
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


                document.addEventListener("DOMContentLoaded", function() {
                    let resetButton = document.getElementById('resetColors');
                    let resetAppButton = document.getElementById('resetAppColors');

                    if (resetAppButton) {
                        resetAppButton.addEventListener('click', function() {
                            // Reset color inputs
                            document.getElementById('app_primary_color').value = "#32e5ac";
                            document.getElementById('app_second_color').value = "#003FA6";

                            // Reset background (assuming you want color background)
                            document.getElementById('app_white_color').value = "#ffffff";
                            document.getElementById('app_black_color').value = "#000000";
                            document.getElementById('app_grey_color').value = "#a5a7a4"; // Grey color
                            document.getElementById('app_yellow_color').value = "#FFAD38"; // Yellow color

                            // Submit the form
                            document.querySelector('#appSettings form').submit();
                        });
                    }

                    if (resetButton) {
                        resetButton.addEventListener('click', function() {
                            let colorInputs = {
                                'primary_color': "#FF9428",
                                'secondary_color': "#1A1A1A",
                                'text_primary_color': "#fdf8f8",
                                'text_secondary_color': "#c1b9b9",
                                'box_background_color': "#222222",
                                'table_background_color': "#c88213"
                            };

                            Object.keys(colorInputs).forEach(id => {
                                let input = document.getElementById(id);
                                if (input) {
                                    input.value = colorInputs[id];
                                }
                            });

                            // Reset background type to color
                            document.getElementById('brand_background_type').value = "color";

                            // Show color input, hide image input
                            document.getElementById('brand_background_color_group').style.display = 'block';
                            document.getElementById('brand_background_image_group').style.display = 'none';

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
                                imagePreviewContainer.style.display = 'none';
                            }

                            // Submit the form to save changes and reload the page
                            document.getElementById('themeSettingsForm').submit();
                        });
                    }
                });

                document.addEventListener("DOMContentLoaded", function() {
                    document.querySelectorAll('input[type="color"]').forEach(input => {
                        input.addEventListener("input", function() {
                            this.style.background = this.value; // تحديث الخلفية
                            this.value = this.value; // تأكيد تحديث القيمة
                        });
                    });
                });
            </script>

        </div>
</body>
