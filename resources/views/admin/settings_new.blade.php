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
        background: #ff9800;
        padding: 10px;
        border: none;
        cursor: pointer;
        color: black;
        font-weight: bold;
    }

    button:hover {
        background: #e68900;
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
        width: 198px;
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

    .settings-menu button:hover {
        background: var(--primary-color);
    }


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
    gap: 10px; /* Add spacing between radio and switch */
}


    .custom-radio {
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
</style>

</head>
<body>

    <div class="settings-sidebar">
        <div class="settings-menu">
            <button onclick="showSection('brandSettings')">{{ __('Brand settings') }}</button>
            <button onclick="showSection('themeSettings')">{{ __('Theme settings') }}</button>
            <button onclick="showSection('timeSettings')">{{ __('Timing settings') }}</button>
            <button onclick="showSection('appSettings')">{{ __('App settings') }}</button>
            <button onclick="showSection('realTimeSetting')">{{ __('Real Time system Setting') }}</button>
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
                                <label>{{ __('Application title:') }} </label>
                                <input type="text" name="app_title" value="{{ $settings['app_title'] ?? '' }}"
                                    class="form-control">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>{{ __('Application logo:') }}</label>
                                <input type="file" name="app_logo" class="form-control" onchange="previewImage(event)">

                                <!-- Image Preview -->
                                <img id="imagePreview"
                                     src="{{ !empty($settings['app_logo']) ? getImagePath($settings['app_logo']) : '' }}"
                                     width="100" class="mt-2"
                                     style="{{ !empty($settings['app_logo']) ? '' : 'display:none;' }}"
                                     onclick="openFullScreen(this)">
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>{{ __('Application Fav Icon:') }}</label>
                                <input type="file" name="app_fav_icon" class="form-control" onchange="previewFavIcon(event)">

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
                <form id="themeSettingsForm" action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data">
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

{{--                        <div class="col-md-6">--}}
{{--                            <div class="form-group">--}}
{{--                                <label for="text_primary_color">{{ __('Text Primary Color:') }}</label>--}}
{{--                                <input type="color" id="text_primary_color" name="text_primary_color"--}}
{{--                                    value="{{ $settings['text_primary_color'] ?? '#000000' }}"--}}
{{--                                    style="background: {{ $settings['text_primary_color'] ?? '#000000' }};"--}}
{{--                                    title="لون النص الأساسي الذي يظهر في العناوين والمحتوى الرئيسي.">--}}
{{--                            </div>--}}
{{--                        </div>--}}

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="text_secondary_color">{{ __('Text Secondary Color:') }}</label>
                                <input type="color" id="text_secondary_color" name="text_secondary_color"
                                    value="{{ $settings['text_secondary_color'] ?? '#808080' }}"
                                    style="background: {{ $settings['text_secondary_color'] ?? '#808080' }};"
                                    title="لون النص الثانوي المستخدم في الشروحات أو النصوص المساعدة.">
                            </div>
                        </div>

{{--                        <div class="col-md-6">--}}
{{--                            <div class="form-group">--}}
{{--                                <label for="box_background_color">{{ __('Box Background Color:') }}</label>--}}
{{--                                <input type="color" id="box_background_color" name="box_background_color"--}}
{{--                                    value="{{ $settings['box_background_color'] ?? '#F8F9FA' }}"--}}
{{--                                    style="background: {{ $settings['box_background_color'] ?? '#F8F9FA' }};"--}}
{{--                                    title="لون خلفية الصناديق أو الكروت داخل التطبيق.">--}}
{{--                            </div>--}}
{{--                        </div>--}}
                    </div>

{{--                    <div class="form row">--}}
{{--                        <div class="col-md-6">--}}
{{--                            <div class="form-group">--}}
{{--                                <label for="table_background_color">{{ __('Table Background Color:') }}</label>--}}
{{--                                <input type="color" id="table_background_color" name="table_background_color"--}}
{{--                                       value="{{ $settings['table_background_color'] ?? '#FFFFFF' }}"--}}
{{--                                       style="background: {{ $settings['table_background_color'] ?? '#FFFFFF' }};"--}}
{{--                                       title="لون خلفية الجداول في التقارير أو البيانات.">--}}
{{--                            </div>--}}
{{--                        </div>--}}
{{--                    </div>--}}
                    <div class="form row">
                        <!-- New Background Type Selection -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="brand_background_type">{{ __('Brand Background Type') }}</label>
                                <select id="brand_background_type" name="brand_background_type" class="form-control"
                                        onchange="toggleBrandBackgroundInput()">
                                    <option value="color" {{ ($settings['brand_background_type'] ?? 'color') === 'color' ? 'selected' : '' }}>
                                        {{ __('Color') }}
                                    </option>
                                    <option value="image" {{ ($settings['brand_background_type'] ?? '') === 'image' ? 'selected' : '' }}>
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

                        <!-- Background Image Input -->
                        <div class="col-md-6">
                            <div class="form-group" id="brand_background_image_group"
                                 style="display: {{ ($settings['brand_background_type'] ?? '') === 'image' ? 'block' : 'none' }};">
                                <label for="brand_background_image">{{ __('Brand Background Image') }}</label>
                                <input type="file" id="brand_background_image" name="brand_background_image" class="form-control">
                                @if(!empty($settings['brand_background_image']) && ($settings['brand_background_type'] ?? '') === 'image')
                                    <div class="mt-2">
                                        <img id="imagePreview"
                                             src="{{ !empty($settings['brand_background_image']) ? getImagePath($settings['brand_background_image']) : '' }}"
                                             width="100" class="mt-2"
                                             style="{{ !empty($settings['app_logo']) ? '' : 'display:none;' }}"
                                             onclick="openFullScreen(this)">

                                        {{--                                        <img src="{{ asset($settings['brand_background_image']) }}" width="100" class="img-thumbnail">--}}
                                    </div>
                                @endif
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
                        <label class="d-block">{{ __('Real Time system Setting:') }}</label>

                        <div class="row mt-4">
                            <!-- Agora Fields -->
                            <div class="col-md-6 mb-3 ms-0 me-auto" >
                                <div class="card p-3 shadow" style="height: 300px;">
                                    <div class="card-header d-flex justify-content-between align-items-center  ">
                                        <h4 class="m-0">{{ __('admin.Agora') }}</h4>
                                        <div class="d-flex align-items-center">
                                            <input type="radio" id="agoraRadio" class="custom-radio libraryRealTime" name="library" value="0"
                                                {{ $library == '0' ? 'checked' : '' }}>
                                            <label for="agoraRadio" class="switch"></label>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="agora_app_id">{{ __('admin.app_id') }}:</label>
                                                <input type="text" id="agora_app_id" name="app_id" placeholder="app_id"
                                                    value="{{ $agora_app_id }}" class="form-control">
                                            </div>
                                        </div>
                                    </div>
                                    <button type="submit" class="btn btn-primary mt-3 btn-save">{{ __('save') }}</button>
                                </div>
                            </div>

                            <!-- Zego Fields -->
                            <div class="col-md-6 mb-3 ms-0 me-auto" >
                                <div class="card p-3 shadow" style="height: 300px;">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h4 class="m-0">{{ __('admin.Zego') }}</h4>
                                        <div class="d-flex align-items-center">
                                            <input type="radio" id="zegoRadio" class="custom-radio libraryRealTime" name="library" value="1"
                                                {{ $library == '1' ? 'checked' : '' }}>
                                            <label for="zegoRadio" class="switch"></label>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="zego_server_secret">{{ __('admin.server_secret') }}:</label>
                                                <input type="text" id="zego_server_secret" name="zego_server_secret"
                                                    placeholder="server_secret" value="{{ $zego_server_secret }}" class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="zego_app_id">{{ __('admin.app_id') }}:</label>
                                                <input type="text" id="zego_app_id" name="zego_app_id" placeholder="app_id"
                                                    value="{{ $zego_app_id }}" class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="app_sign">{{ __('admin.app_sign') }}:</label>
                                                <input type="text" id="app_sign" name="app_sign" placeholder="app_sign"
                                                    value="{{ $app_sign }}" class="form-control">
                                            </div>
                                        </div>
                                    </div>
                                    <button type="submit" class="btn btn-primary mt-3 btn-save">{{ __('save') }}</button>
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
            </div>




            <div id="appSettings" class="settings-section">
                <h3>{{ __('Timing settings') }}</h3>
                <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="form row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="primary_color">{{ __('Primary Color') }}</label>
                                <input type="color" id="app_primary_color" name="app_primary_color"
                                    value="{{ $settings['app_primary_color'] ?? '#3498db' }}" class="form-control">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="second_color">{{ __('Second Color') }}</label>
                                <input type="color" id="second_color" name="app_second_color"
                                    value="{{ $settings['app_second_color'] ?? '#2ecc71' }}" class="form-control">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="background_type">{{ __('Background Type') }}</label>
                                <select id="background_type" name="background_type" class="form-control"
                                    onchange="toggleBackgroundInput()">
                                    <option value="color" {{ ($settings['background_type'] ?? 'color') === 'color' ? 'selected' : '' }}>{{ __('Color') }}</option>
                                    <option value="image" {{ ($settings['background_type'] ?? '') === 'image' ? 'selected' : '' }}>{{ __('Image') }}</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group" id="background_color_group"
                                style="display: {{ ($settings['background_type'] ?? 'color') === 'color' ? 'block' : 'none' }};">
                                <label for="background_color">{{ __('Background Color') }}</label>
                                <input type="color" id="background_color" name="background_color" class="form-control"
                                    value="{{ $settings['background_color'] ?? '#ffffff' }}">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group" id="background_image_group"
                                style="display: {{ ($settings['background_type'] ?? '') === 'image' ? 'block' : 'none' }};">
                                <label for="background_image">{{ __('Background Image') }}</label>
                                <input type="file" id="background_image" name="app_background_image" class="form-control">
                                @if(!empty($settings['app_background']) && ($settings['background_type'] ?? '') === 'image')
                                    <div class="mt-2">
                                        <img src="{{ asset($settings['app_background']) }}" width="100" class="img-thumbnail">
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="form row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>{{ __('Image 1') }}</label>
                                <input type="file" name="image1" class="form-control" onchange="previewImage(event)">

                                <img id="imagePreview"
                                     src="{{ !empty($settings['image1']) ? getImagePath($settings['image1']) : '' }}"
                                     width="100" class="mt-2"
                                     style="{{ !empty($settings['app_logo']) ? '' : 'display:none;' }}"
                                     onclick="openFullScreen(this)">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>{{ __('Image 2') }}</label>
                                <input type="file" name="image2" class="form-control" onchange="previewImage(event)">

                                <img id="imagePreview"
                                     src="{{ !empty($settings['image2']) ? getImagePath($settings['image2']) : '' }}"
                                     width="100" class="mt-2"
                                     style="{{ !empty($settings['app_logo']) ? '' : 'display:none;' }}"
                                     onclick="openFullScreen(this)">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>{{ __('Image 3') }}</label>
                                <input type="file" name="image3" class="form-control" onchange="previewImage(event)">

                                <img id="imagePreview"
                                     src="{{ !empty($settings['image3']) ? getImagePath($settings['image3']) : '' }}"
                                     width="100" class="mt-2"
                                     style="{{ !empty($settings['app_logo']) ? '' : 'display:none;' }}"
                                     onclick="openFullScreen(this)">
                            </div>
                        </div>
                    </div>

                    <div class="col-12 d-flex gap-3 mt-3">
                        <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
                        <button type="button" id="resetAppColors"
                                class="btn btn-secondary">{{ __('Reset Colors') }}</button>
                    </div>

                </form>
            </div>



            <div id="imageModal" class="modal" onclick="closeFullScreen()">
                <span class="close">&times;</span>
                <img class="modal-content" id="fullImage">
            </div>
            <script>
                $(document).ready(function () {
                    function updateLibrary(selectedLibrary) {
                        $.ajax({
                            url: "{{ route('admin.update-agora-zego') }}",
                            type: "POST",
                            data: {
                                _token: "{{ csrf_token() }}",
                                library: selectedLibrary
                            },
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
                            if ($(this).prop("checked")) {
                                $(this).next(".switch").addClass("active");
                            } else {
                                $(this).next(".switch").removeClass("active");
                            }
                        });
                    }

                    // عند تغيير الراديو، نحدث الواجهة
                    $(document).on("change", ".libraryRealTime", function () {
                        let selectedLibrary = $(this).val();
                        console.log("Selected library:", selectedLibrary);
                        updateLibrary(selectedLibrary);
                        updateSwitches();
                    });

                    $(".switch").click(function () {
                        let radio = $(this).prev(".custom-radio");

                        if (!radio.prop("checked")) {
                            $(".custom-radio").prop("checked", false);
                            $(".switch").removeClass("active");

                            radio.prop("checked", true).trigger("change");
                        }
                    });

                    updateSwitches();
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
                            document.getElementById('second_color').value = "#003FA6";

                            // Reset background (assuming you want color background)
                            document.getElementById('background_type').value = "color";
                            document.getElementById('background_color').value = "#32e5ac";
                            document.getElementById('app_background').value = "#32e5ac";

                            // Show the correct background input group
                            document.getElementById('background_color_group').style.display = 'block';
                            document.getElementById('background_image_group').style.display = 'none';

                            document.getElementById('brand_background_image_group').style.display = 'none';

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
                            const imagePreviewContainer = document.querySelector('#brand_background_image_group .mt-2');
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
