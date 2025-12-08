

@php
use Modules\Vip\Entities\Vip;

    $selectedTimeZone = App\Models\Setting::where('key', 'timezone')->first();
    $settings = App\Models\Setting::pluck('value', 'key')->toArray();

@endphp
<style>

    .inner-settings-menu {
    display: flex;
    gap: 10px;
    margin-bottom: 20px;
}

.inner-settings-menu button {
    padding: 10px 20px;
    background: var(--secondary-color);
    color: black;
    border: none;
    border-radius: 6px;
    cursor: pointer;
}

.inner-settings-menu button.active {
    background: var(--primary-color);
    color: black;
}

.settings-section {
    display: none;
}

.settings-section.active {
    display: block;
}


/* General colorpicker dropdown styling */
.colorpicker {
    min-width: 220px;
    padding: 10px;
    border-radius: 8px;
    box-shadow: 0 0 15px rgba(0,0,0,0.3);
    font-family: Arial, sans-serif;
}

/* Saturation square */
.colorpicker-saturation {
    border-radius: 5px !important;
}

/* Hue slider */
.colorpicker-hue {
    border-radius: 5px !important;
}

/* Alpha slider */
.colorpicker-alpha {
    border-radius: 5px !important;
}

/* Color preview box */
.colorpicker-color div {
    border-radius: 5px;
    border: 1px solid #ccc;
}

/* Force dropdown alignment */
.colorpicker.colorpicker-right {
    left: auto !important;
    right: 0 !important;
}

.colorpicker.colorpicker-left {
    left: 0 !important;
    right: auto !important;
}


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
        display: block;
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
        border: 5px solid #4caf50;
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
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
    }

    .ribbon-banner-card {
        position: absolute;
        top: 6px;
        right: -9px;
        background-color: #ff0000;
        padding: 2px 7px;
        transform: rotate(90deg);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
    }

    .ribbon-banner-card span {
        color: white;
        font-size: 15px;
        font-weight: normal;
        text-shadow: 0 1px 1px rgba(0, 0, 0, 0.2);

    }

    .rtl .ribbon-banner {
        right: auto !important;
        left: -11px !important;
        padding: 2px 13px !important;
    }

    .ribbon-banner span {
        color: white;
        font-size: 15px;
        font-weight: normal;
        text-shadow: 0 1px 1px rgba(0, 0, 0, 0.2);
    }

    .text-center.my-3 img.img-fluid {
        max-height: 80px;
        display: unset !important;
        margin-top: 20px;
    }

    .card-top {
        margin-top: 33px;

    }

    .no-background-form {
        background: transparent !important;
        border: none !important;
        box-shadow: none !important;
        padding: 0 !important;
        margin: 0 !important;
    }
    .exp-card{
        margin-bottom: 32px;

    }
   .exp-card-cont{
    height: 400px;

     }
    .copy-container {
        position: relative;
    }

    .copy-button {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        border: none;
        background: none !important;
        cursor: pointer;
        padding: 0;
        font-size: 16px;
    }

    .ltr .copy-button{
        left: 95px;
    }
    .rtl .copy-button{
        right: 95px;
    }

    @media (max-width: 768px) {


    .settings-menu {
        display: flex;
        flex-wrap: nowrap;
    }

    .settings-menu button {
        display: inline-block;
        min-width: 150px;
        margin-right: 0.5rem;
        margin-bottom: 0; /* إزالة المسافة الرأسية */
        white-space: normal;
    }
}

    @media (max-width: 576px) {
    }
    @media (max-width: 768px) {
    }
    @media (max-width: 992px) {
    }
    @media (max-width: 1200px) {
    }
    @media (max-width: 1400px) {
        .form-control {
            width: 170px !important;
        }
        .rtl .copy-button {
            right: 75px;
        }
    }
</style>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-switch/3.3.4/css/bootstrap3/bootstrap-switch.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-switch/3.3.4/js/bootstrap-switch.min.js"></script>


<body>


    <div class="settings-sidebar">
        <div class="settings-menu">
            <button onclick="showSection('brandSettings')">{{ __('Brand settings') }}</button>
            <button onclick="showSection('landPageSettings')">{{ __('land settings') }}</button>

            <button onclick="showSection('workSettings')" class="position-relative">
                {{ __('Work') }}
            </button>
             <button onclick="showSection('mobileLinks')" class="position-relative">
                {{ __('Application') }}
            </button>
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
        <div id="brandSettings" class="settings-section active">

            <h3> {{ __('Brand settings') }}</h3>

            <form action="{{ route('admin.app.settings.update') }}" method="POST" enctype="multipart/form-data">
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
        <div id="landPageSettings" class="settings-section">
            <h3 class="mb-4">{{ __('Landing Page Settings') }}</h3>

            <div class="settings-container">
                <!-- Sidebar Tabs -->
                <div class="tabs-sidebar" role="tablist" aria-orientation="vertical">
                    <button class="tab-btn active" data-target="#general" type="button" role="tab" aria-controls="general" aria-selected="true">
                        {{ __('General Settings') }}
                    </button>
                    <button class="tab-btn" data-target="#stats" type="button" role="tab" aria-controls="stats" aria-selected="false">
                        {{ __('Statistics Settings') }}
                    </button>
                    <button class="tab-btn" data-target="#social" type="button" role="tab" aria-controls="social" aria-selected="false">
                        {{ __('Social Media Settings') }}
                    </button>
                </div>

                <!-- Tab Content -->
                <div class="tab-content">
                    <form action="{{ route('admin.app.settings.update') }}" method="POST" enctype="multipart/form-data" id="landingSettingsForm">
                        @csrf

                        <!-- General Settings -->
                        <div class="tab-pane show active" id="general" role="tabpanel" aria-labelledby="general-tab">
                            <h5>{{ __('General Settings') }}</h5>
                            <hr>
                            <div class="row">
                                <div class="col-md-6">
                                    <label>{{ __('About Us Link') }}</label>
                                    <input type="url" name="about_us_link" value="{{ $settings['about_us_link'] ?? '' }}" class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <label>{{ __('Gallery App Link') }}</label>
                                    <input type="url" name="gallery_app_link" value="{{ $settings['gallery_app_link'] ?? '' }}" class="form-control">
                                </div>
                            </div>
                        </div>

                        <!-- Statistics Settings -->
                        <div class="tab-pane" id="stats" role="tabpanel" aria-labelledby="stats-tab">
                            <h5>{{ __('Statistics Settings') }}</h5>
                            <hr>
                            <div class="row">
                                <div class="col-md-4">
                                    <label>{{ __('Number of Users') }}</label>
                                    <input type="number" name="landing_users_count" value="{{ $settings['landing_users_count'] ?? '' }}" class="form-control">
                                </div>
                                <div class="col-md-4">
                                    <label>{{ __('Number of Countries') }}</label>
                                    <input type="number" name="landing_countries_count" value="{{ $settings['landing_countries_count'] ?? '' }}" class="form-control">
                                </div>
                                <div class="col-md-4">
                                    <label>{{ __('Number of Live Streams') }}</label>
                                    <input type="number" name="landing_live_count" value="{{ $settings['landing_live_count'] ?? '' }}" class="form-control">
                                </div>
                            </div>
                        </div>

                        <!-- Social Media Settings -->
                        <div class="tab-pane" id="social" role="tabpanel" aria-labelledby="social-tab">
                            <h5>{{ __('Social Media Settings') }}</h5>
                            <hr>
                            <div class="row">
                                <div class="col-md-4">
                                    <label>{{ __('Facebook Link') }}</label>
                                    <input type="url" name="facebook_link" value="{{ $settings['facebook_link'] ?? '' }}" class="form-control">
                                </div>
                                <div class="col-md-4">
                                    <label>{{ __('Twitter Link') }}</label>
                                    <input type="url" name="twitter_link" value="{{ $settings['twitter_link'] ?? '' }}" class="form-control">
                                </div>
                                <div class="col-md-4">
                                    <label>{{ __('WhatsApp Link') }}</label>
                                    <input type="url" name="whatsapp_link" value="{{ $settings['whatsapp_link'] ?? '' }}" class="form-control">
                                </div>
                            </div>
                        </div>

                        <!-- Save Button -->
                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>




            <div id="themeSettings" class="settings-section">
                <h3>{{ __('Theme settings') }}</h3>
                <form id="themeSettingsForm" action="{{ route('admin.app.settings.update') }}" method="POST"
                    enctype="multipart/form-data">
                    <div class="form row">
                        @csrf


                        {{-- <div class="col-md-6">
                            <div class="form-group">
                                <label for="secondary_color">{{ __('Primary Color:') }}</label>
                                <input type="color" id="secondary_color" name="secondary_color"
                                    value="{{ $settings['secondary_color'] ?? '#FFFFFF' }}"
                                    style="background: {{ $settings['secondary_color'] ?? '#FFFFFF' }};"
                                    title="اللون الثانوي المستخدم كخلفية لبعض الأقسام أو لتوضيح بعض العناصر.">
                            </div>
                        </div> --}}

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="secondary_color">{{ __('Primary Color:') }}</label>

                                <div class="input-group colorpicker-element">
                                    <span class="input-group-addon">
                                        <i style="background-color: {{ $settings['secondary_color'] ?? '#FFFFFF' }};"></i>
                                    </span>

                                    <input
                                        type="text"
                                        id="secondary_color"
                                        name="secondary_color"
                                        class="form-control"
                                        value="{{ $settings['secondary_color'] ?? '#FFFFFF' }}"
                                        placeholder="ادخل لون"
                                    >
                                </div>
                            </div>
                        </div>


                        {{-- <div class="col-md-6">
                            <div class="form-group">
                                <label for="primary_color">{{ __('Secondary Color:') }}</label>
                                <input type="color" id="primary_color" name="primary_color"
                                    value="{{ $settings['primary_color'] ?? '#000000' }}"
                                    style="background: {{ $settings['primary_color'] ?? '#000000' }};"
                                    title="لون الواجهة الرئيسي، يتم استخدامه في الأزرار والخلفيات الأساسية.">
                            </div>
                        </div> --}}

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="primary_color">{{ __('Secondary Color:') }}</label>

                                <div class="input-group colorpicker-element">
                                    <span class="input-group-addon">
                                        <i style="background-color: {{ $settings['primary_color'] ?? '#000000' }};"></i>
                                    </span>

                                    <input
                                        type="text"
                                        id="primary_color"
                                        name="primary_color"
                                        class="form-control"
                                        value="{{ $settings['primary_color'] ?? '#000000' }}"
                                        placeholder="ادخل لون"
                                    >
                                </div>
                            </div>
                        </div>




                        {{-- <div class="col-md-6">
                            <div class="form-group">
                                <label for="text_secondary_color">{{ __('Text Secondary Color:') }}</label>
                                <input type="color" id="text_secondary_color" name="text_secondary_color"
                                    value="{{ $settings['text_secondary_color'] ?? '#808080' }}"
                                    style="background: {{ $settings['text_secondary_color'] ?? '#808080' }};"
                                    title="لون النص الثانوي المستخدم في الشروحات أو النصوص المساعدة.">
                            </div>
                        </div> --}}

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="text_secondary_color">{{ __('Text Secondary Color:') }}</label>

                                <div class="input-group colorpicker-element">
                                    <span class="input-group-addon">
                                        <i style="background-color: {{ $settings['text_secondary_color'] ?? '#808080' }};"></i>
                                    </span>

                                    <input
                                        type="text"
                                        id="text_secondary_color"
                                        name="text_secondary_color"
                                        class="form-control"
                                        value="{{ $settings['text_secondary_color'] ?? '#808080' }}"
                                        placeholder="ادخل لون"
                                    >
                                </div>
                            </div>
                        </div>



                    </div>

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
                        {{-- <div class="col-md-6">
                            <div class="form-group" id="brand_background_color_group"
                                style="display: {{ ($settings['brand_background_type'] ?? 'color') === 'color' ? 'block' : 'none' }};">
                                <label for="box_background_color">{{ __('Box Background Color:') }}</label>
                                <input type="color" id="box_background_color" name="box_background_color"
                                    value="{{ $settings['box_background_color'] ?? '#F8F9FA' }}"
                                    style="background: {{ $settings['box_background_color'] ?? '#F8F9FA' }};"
                                    title="لون خلفية الصناديق أو الكروت داخل التطبيق.">
                            </div>
                        </div> --}}

                        <div class="col-md-6">
                            <div class="form-group" id="brand_background_color_group"
                                style="display: {{ ($settings['brand_background_type'] ?? 'color') === 'color' ? 'block' : 'none' }};">

                                <label for="box_background_color">{{ __('Box Background Color:') }}</label>

                                <div class="input-group colorpicker-element">
                                    <span class="input-group-addon">
                                        <i style="background-color: {{ $settings['box_background_color'] ?? '#F8F9FA' }};"></i>
                                    </span>

                                    <input
                                        type="text"
                                        id="box_background_color"
                                        name="box_background_color"
                                        class="form-control"
                                        value="{{ $settings['box_background_color'] ?? '#F8F9FA' }}"
                                        placeholder="ادخل لون"
                                    >
                                </div>
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
                    <form action="{{ route('admin.app.settings.update') }}" method="POST">
                        @csrf
                                <div class="form">

                            <label>{{ __('Time zone:') }}</label>
                            <select name="timezone" class="form-control">
                                @foreach ($timezones as $timezone)
                                    <option value="{{ $timezone->name }}"
                                        {{ $timezone->name == ($settings['timezone'] ?? '') ? 'selected' : '' }}>
                                        {{ $timezone->name }} ({{ $timezone->offset }})
                                    </option>
                                @endforeach
                            </select>

                            <label>{{ __('Default Country:') }}</label>
                            <select name="default_country" class="form-control select2-country">
                                @foreach ($countries as $country)
                                    <option value="{{ $country->id }}"
                                        {{ $country->id == ($settings['default_country'] ?? '') ? 'selected' : '' }}>
                                        {{ app()->getLocale() === 'ar' ? $country->name : $country->e_name }}
                                    </option>
                                @endforeach
                            </select>

                            <label class="mt-3">{{ __('Start of week:') }}</label>
                            <select name="week_start" class="form-control">
                                @foreach ([
                                    'sunday' => __('Sunday'),
                                    'monday' => __('Monday'),
                                    'tuesday' => __('Tuesday'),
                                    'wednesday' => __('Wednesday'),
                                    'thursday' => __('Thursday'),
                                    'friday' => __('Friday'),
                                    'saturday' => __('Saturday'),
                                ] as $key => $day)
                                    <option value="{{ $key }}" {{ ($settings['week_start'] ?? 'monday') == $key ? 'selected' : '' }}>
                                        {{ $day }}
                                    </option>
                                @endforeach
                            </select>

                            {{-- اختيار نهاية الأسبوع --}}
                            <label class="mt-3">{{ __('End of week:') }}</label>
                            <select name="week_end" class="form-control">
                                @foreach ([
                                    'saturday' => __('Saturday'),
                                    'sunday' => __('Sunday'),
                                    'monday' => __('Monday'),
                                    'tuesday' => __('Tuesday'),
                                    'wednesday' => __('Wednesday'),
                                    'thursday' => __('Thursday'),
                                    'friday' => __('Friday'),
                                ] as $key => $day)
                                    <option value="{{ $key }}" {{ ($settings['week_end'] ?? 'sunday') == $key ? 'selected' : '' }}>
                                        {{ $day }}
                                    </option>
                                @endforeach
                            </select>

                            <button type="submit" class="btn btn-primary mt-3">{{ __('Save') }}</button>
                        </div>
                    </form>
                </div>

            <div id="realTimeSetting" class="settings-section">
                <form>
                    @csrf
                    <div class="form">
                        <label class="d-block">{{ __('Sound & Video System Setting:') }}</label>

                        <div class="row mt-4">
                           <form class="no-background-form" action="{{ route('admin.update-agora-zego') }}"
                                method="POST">
                                @csrf

                            </form>
                             <form class="no-background-form" action="{{ route('admin.update-agora-zego') }}"
                                method="POST">
                                @csrf
                                <!-- Tencent Fields -->
                                <div class="col-md-6 mb-3 ms-0 me-auto">
                                    <div class="card p-3 shadow" style="height: 300px;">
                                        <div class="card-header d-flex justify-content-between align-items-center">
                                            <h4 class="m-0">{{ __('admin.Tencent') }}</h4>
                                            <div class="ribbon-banner-card">
                                                <span>{{ __('soon') }}</span>
                                            </div>
                                            {{-- <div class="d-flex align-items-center">
                                                <input type="radio" id="tencentRadio" class="custom-radio libraryRealTime"
                                                       name="library" value="2" {{ $library == '2' ? 'checked' : '' }}>
                                                <label for="tencentRadio" class="switch"></label>
                                            </div> --}}
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label
                                                        for="tencent_server_secret">{{ __('admin.server_secret') }}:</label>
                                                    <input type="text" id="tencent_server_secret"
                                                        name="tencent_server_secret" placeholder="server_secret"
                                                        value="{{ $tencent_server_secret }}" class="form-control"
                                                        required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="tencent_app_id">{{ __('admin.app_id') }}:</label>
                                                    <input type="text" id="tencent_app_id" name="tencent_app_id"
                                                        placeholder="app_id" value="{{ $tencent_app_id }}"
                                                        class="form-control" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="app_sign">{{ __('admin.app_sign') }}:</label>
                                                    <input type="text" id="app_sign" name="app_sign"
                                                        placeholder="app_sign" value="{{ $app_sign }}"
                                                        class="form-control" required>
                                                </div>
                                            </div>
                                        </div>
                                        <button type="submit"
                                            class="btn btn-primary mt-3 btn-save">{{ __('save') }}</button>
                                    </div>
                                </div>
                            </form>
                            <form class="no-background-form" action="{{ route('admin.update-agora-zego') }}"
                                method="POST">
                                @csrf
                                <!-- Zego Fields -->
                                <div class="col-md-6 mb-3 ms-0 me-auto">
                                    <div class="card p-3 shadow" style="height: 300px;">
                                        <div class="card-header d-flex justify-content-between align-items-center">
                                            <h4 class="m-0">{{ __('admin.Zego') }}</h4>
                                            {{-- <div class="d-flex align-items-center">
                                                <input type="radio" id="zegoRadio" class="custom-radio libraryRealTime"
                                                       name="library" value="1" {{ $library == '1' ? 'checked' : '' }}>
                                                <label for="zegoRadio" class="switch"></label>
                                            </div> --}}


                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                     <input type="hidden" name="provider" value="zego">
                                                    <label
                                                        for="zego_server_secret">{{ __('admin.server_secret') }}:</label>
                                                    <input type="text" id="zego_server_secret"
                                                        name="zego_server_secret" placeholder="server_secret"
                                                        value="{{ $zego_server_secret }}" class="form-control"
                                                        required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="zego_app_id">{{ __('admin.app_id') }}:</label>
                                                    <input type="text" id="zego_app_id" name="zego_app_id"
                                                        placeholder="app_id" value="{{ $zego_app_id }}"
                                                        class="form-control" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="app_sign">{{ __('admin.app_sign') }}:</label>
                                                    <input type="text" id="app_sign" name="app_sign"
                                                        placeholder="app_sign" value="{{ $app_sign }}"
                                                        class="form-control" required>
                                                </div>
                                            </div>
                                            <input type="hidden" name="zego_filter_enabled" value="0">

                                            <input type="checkbox"
                                                name="zego_filter_enabled"
                                                value="1"
                                                data-bootstrap-switch
                                                {{ $zego_filter_enabled ? 'checked' : '' }}>




                                                <script>
                                                    function initZegoSwitch() {
                                                        $('input[data-bootstrap-switch]').each(function () {
                                                            $(this).bootstrapSwitch('state', $(this).prop('checked'), true);
                                                        });
                                                    }

                                                    $(document).ready(initZegoSwitch);
                                                    $(document).on('pjax:success', initZegoSwitch);
                                                </script>
                                        </div>
                                        <button type="submit"
                                            class="btn btn-primary mt-3 btn-save">{{ __('save') }}</button>
                                    </div>
                                </div>
                            </form>

                             <form class="no-background-form" action="{{ route('admin.update-agora-zego') }}"
                                method="POST">
                                @csrf
                                <!-- Agora Fields -->
                                <div class="col-md-6 mb-3 ms-0 me-auto">
                                    <div class="card p-3 shadow" style="height: 300px;">
                                        <div class="card-header d-flex justify-content-between align-items-center  ">
                                            <h4 class="m-0">{{ __('admin.Agora') }}</h4>

                                        </div>
                                        <br>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="agora_app_id">{{ __('admin.app_id') }}:</label>
                                                    <input type="text" id="agora_app_id" name="app_id"
                                                        placeholder="app_id" value="{{ $agora_app_id }}"
                                                        class="form-control" required>
                                                </div>
                                            </div>
                                             <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="agora_app_certificate">{{ __('certificate') }}:</label>
                                                    <input type="text" id="agora_app_certificate" name="agora_app_certificate"
                                                        placeholder="agora_app_certificate" value="{{ $agora_app_certificate }}"
                                                        class="form-control" required>
                                                </div>
                                            </div>
                                        </div>
                                        <button type="submit"
                                            class="btn btn-primary mt-3 btn-save">{{ __('save') }}</button>
                                    </div>
                                </div>
                            </form>


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
                                        <input type="radio" id="agoraSoundRadio"
                                            class="custom-radio libraryRealTime" name="sound_library" value="0"
                                            {{ $soundLibrary == '0' ? 'checked' : '' }}>
                                        <label for="agoraSoundRadio" class="switch"></label>
                                    </div>
                                </div>
                            </div>

                            <!-- Zego Fields -->
                            <div class="col-md-4 mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h4 class="m-0">{{ __('admin.Zego') }}</h4>
                                    <div class="d-flex align-items-center">
                                        <input type="radio" id="zegoSoundRadio"
                                            class="custom-radio libraryRealTime" name="sound_library" value="1"
                                            {{ $soundLibrary == '1' ? 'checked' : '' }}>
                                        <label for="zegoSoundRadio" class="switch"></label>
                                    </div>
                                </div>
                            </div>

                            <!-- Tencent Fields -->
                            <div class="col-md-4 mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h4 class="m-0">{{ __('admin.Tencent') }}</h4>
                                    <div class="d-flex align-items-center">
                                        <input type="radio" id="tencentSoundRadio"
                                            class="custom-radio libraryRealTime" name="sound_library" value="2"
                                            {{ $soundLibrary == '2' ? 'checked' : '' }}>
                                        <label for="tencentSoundRadio" class="switch"></label>
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
                                        <input type="radio" id="agoraVideoRadio"
                                            class="custom-radio libraryRealTime" name="video_library" value="0"
                                            {{ $videoLibrary == '0' ? 'checked' : '' }}>
                                        <label for="agoraVideoRadio" class="switch"></label>
                                    </div>
                                </div>
                            </div>

                            <!-- Zego Fields -->
                            <div class="col-md-4 mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h4 class="m-0">{{ __('admin.Zego') }}</h4>
                                    <div class="d-flex align-items-center">
                                        <input type="radio" id="zegoVideoRadio"
                                            class="custom-radio libraryRealTime" name="video_library" value="1"
                                            {{ $videoLibrary == '1' ? 'checked' : '' }}>
                                        <label for="zegoVideoRadio" class="switch"></label>
                                    </div>
                                </div>
                            </div>

                            <!-- Tencent Fields -->
                            <div class="col-md-4 mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h4 class="m-0">{{ __('admin.Tencent') }}</h4>
                                    <div class="d-flex align-items-center">
                                        <input type="radio" id="tencentVideoRadio"
                                            class="custom-radio libraryRealTime" name="video_library" value="2"
                                            {{ $videoLibrary == '2' ? 'checked' : '' }}>
                                        <label for="tencentVideoRadio" class="switch"></label>
                                    </div>
                                </div>
                            </div>



                        </div>
                    </div>
                </form>

                <form action="{{ route('admin.update-agora-zego') }}" method="POST">
                    @csrf
                    <div class="form">
                        <label class="d-block">{{ __('Live System Setting:') }}</label>

                        <div class="row mt-4">
                            <!-- RTC Fields -->
                            <div class="col-md-4 mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h4 class="m-0">{{ __('admin.RTC') }}</h4>
                                    <div class="d-flex align-items-center">
                                        <input type="radio" id="rtcLiveRadio"
                                               class="custom-radio libraryRealTime" name="live_library" value="0"
                                            {{ $liveLibrary == '0' ? 'checked' : '' }}>
                                        <label for="rtcLiveRadio" class="switch"></label>
                                    </div>
                                </div>
                            </div>

                            <!-- CDN Fields -->
                            <div class="col-md-4 mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h4 class="m-0">{{ __('admin.CDN') }}</h4>
                                    <div class="d-flex align-items-center">
                                        <input type="radio" id="cdnLiveRadio"
                                               class="custom-radio libraryRealTime" name="live_library" value="1"
                                            {{ $liveLibrary == '1' ? 'checked' : '' }}>
                                        <label for="cdnLiveRadio" class="switch"></label>
                                    </div>
                                </div>
                            </div>

                            <!-- L3 Fields -->
                            <div class="col-md-4 mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h4 class="m-0">{{ __('admin.L3') }}</h4>
                                    <div class="d-flex align-items-center">
                                        <input type="radio" id="l3LiveRadio"
                                               class="custom-radio libraryRealTime" name="live_library" value="2"
                                            {{ $liveLibrary == '2' ? 'checked' : '' }}>
                                        <label for="l3LiveRadio" class="switch"></label>
                                    </div>
                                </div>
                            </div>



                        </div>
                    </div>
                </form>

                <form id="autoPreviewForm" action="{{ route('admin.update-agora-zego') }}" method="POST">
                        @csrf
                        <div class="form">
                            <label class="d-block">{{ __('admin.is_preview') }}</label>

                            <div class="row mt-4">
                                <div class="col-md-4 mb-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h4 class="m-0">{{ __('admin.is_preview') }}</h4>
                                        <div class="d-flex align-items-center">
                                            <input type="hidden" name="is_auto_preview" value="0">

                                            <input type="checkbox"
                                                name="is_auto_preview"
                                                value="1"
                                                data-bootstrap-switch
                                                {{ $is_auto_preview ? 'checked' : '' }}>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>

                    <script>
                        function initIsPreviewSwitch() {
                            const $switch = $('input[name="is_auto_preview"][data-bootstrap-switch]');

                            $switch.each(function () {
                                $(this).bootstrapSwitch('state', $(this).prop('checked'), true);
                            });

                            $switch.on('switchChange.bootstrapSwitch', function (event, state) {
                                const form = $('#autoPreviewForm');
                                const formData = form.serializeArray();

                                const newValue = state ? 1 : 0;
                                formData.push({ name: 'is_auto_preview', value: newValue });

                                $.ajax({
                                    url: form.attr('action'),
                                    method: form.attr('method'),
                                    data: formData,
                                    success: function () {
                                        console.log('is_auto_preview updated to', newValue);
                                    },
                                    error: function (xhr) {
                                        console.error('Error updating is_auto_preview:', xhr.responseText);
                                    }
                                });
                            });
                        }

                        $(document).ready(initIsPreviewSwitch);
                        $(document).on('pjax:success', initIsPreviewSwitch);
                    </script>


            </div>

            <div id="gamesSettings" class="settings-section">

                <div class="form">
                    <label class="d-block">{{ __('Games Settings:') }}</label>

                    <div class="row mt-4">
                        <!-- Lucky Flex -->
                        <div class="col-md-6 mb-3 ms-0 me-auto">
                            <form action="{{ route('admin.update-agora-zego') }}" method="POST">
                                @csrf
                                <div class="card p-3 shadow" style="height: 495px;">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h4 class="m-0">{{ __('lucky phonix') }}</h4>
                                        <div class="d-flex align-items-center">
                                            <input type="radio" id="luckyFlexRadio"
                                                class="custom-radio libraryRealTime" name="games_library"
                                                value="0" {{ $gamesLibrary == '0' ? 'checked' : '' }}>
                                            <label for="luckyFlexRadio" class="switch"></label>
                                        </div>
                                    </div>
                                    <div class="row">
                                        {{--                                        <div class="col-md-6"> --}}
                                        {{--                                            <div class="form-group"> --}}
                                        {{--                                                <label --}}
                                        {{--                                                    for="fawry_secret">{{ __('admin.server_secret') }}:</label> --}}
                                        {{--                                                <input type="text" id="fawry_secret" --}}
                                        {{--                                                       name="fawry_secret" placeholder="secret" --}}
                                        {{--                                                       value="{{ $settings['fawry_secret'] ?? ''}}" class="form-control" required> --}}
                                        {{--                                            </div> --}}
                                        {{--                                        </div> --}}
                                    </div>
                                    <button type="submit"
                                        class="btn btn-primary mt-3 btn-save">{{ __('save') }}</button>
                                </div>
                            </form>
                        </div>

                        <!-- Guess The Word -->
                        <div class="col-md-6 mb-3 ms-0 me-auto">
                            <form action="{{ route('admin.update-agora-zego') }}" method="POST">
                                @csrf
                                <div class="card p-3 shadow" style="height: 495px;">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h4 class="m-0">{{ __('Zynga') }}</h4>
                                        <div class="ribbon-banner-card">
                                            <span>{{ __('soon') }}</span>
                                        </div>
                                        <div class="d-flex align-items-center">
                                            <input type="radio" id="guessTheWordRadio"
                                                class="custom-radio libraryRealTime" name="games_library"
                                                value="1" {{ $gamesLibrary == '1' ? 'checked' : '' }}>
                                            <label for="guessTheWordRadio" class="switch"></label>
                                        </div>
                                    </div>
                                    <div class="row">
                                        {{--                                        <div class="col-md-6"> --}}
                                        {{--                                            <div class="form-group"> --}}
                                        {{--                                                <label --}}
                                        {{--                                                    for="fawry_secret">{{ __('admin.server_secret') }}:</label> --}}
                                        {{--                                                <input type="text" id="fawry_secret" --}}
                                        {{--                                                       name="fawry_secret" placeholder="secret" --}}
                                        {{--                                                       value="{{ $settings['fawry_secret'] ?? ''}}" class="form-control" required> --}}
                                        {{--                                            </div> --}}
                                        {{--                                        </div> --}}
                                    </div>
                                    <button type="submit"
                                        class="btn btn-primary mt-3 btn-save">{{ __('save') }}</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>


            <div id="notificationSettings" class="settings-section">
            </div>



            <div id="paymentCredentialSettings" class="settings-section">

                <div class="form">
                    <label class="d-block">{{ __('Payment Credential Settings:') }}</label>

                    <div class="row mt-4">
                        <!-- dynamic Fields -->
                        @foreach ($paymentCoins as $coin)
                            <div class="col-md-6 mb-3 ms-0 me-auto" style="margin-top: 40px;">
                                <form action="{{ route('admin.app.settings.update') }}" method="POST"
                                    enctype="multipart/form-data">
                                    @csrf
                                    <div class="card payment-card p-3 shadow">
                                        <div class="card-header d-flex justify-content-between align-items-center">
                                            <h4 class="m-0">{{ __('admin.' . $coin->title) }}</h4>
                                            <div class="d-flex align-items-center">
                                               <input type="hidden" name="is_{{ $coin->type }}_active"
                                                    value="0">
                                                    <input type="hidden" name="payment_getaway_id"
                                                    value={{ $coin->id }}>
                                                <input type="checkbox" id="{{ $coin->type }}Radio"
                                                    class="custom-payment-radio libraryRealTime"
                                                    name="is_{{ $coin->type }}_active" value="1"
                                                    {{ $coin->status == 1 && @$settings['is_' . $coin->type . '_active'] == '1' ? 'checked' : '' }}
                                                    {{-- {{ $coin->status == 0 ? 'disabled' : '' }} --}}
                                                    >
                                                <label for="{{ $coin->type }}Radio" class="switch"></label>
                                            </div>
                                        </div>
                                        <div class="text-center my-3">
                                            <img src="{{ $coin->photo ? getImagePath($coin->photo) : asset('images/dollar.jpg') }}"
                                                alt="{{ $coin->title }}"
                                                style="border-radius: 50%; width: 100px; height: 100px; object-fit: contain; display: block; margin: 3px auto; background: #fff;">
                                        </div>
                                        <div class="row">
                                            @if ($coin->type == 'fawry')
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label
                                                            for="fawry_secret">{{ __('admin.server_secret') }}:</label>
                                                        <input type="text" id="fawry_secret" name="fawry_secret"
                                                            placeholder="secret"
                                                            value="{{ $settings['fawry_secret'] ?? '' }}"
                                                            class="form-control" required>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label
                                                            for="fawry_merchant_code">{{ __('admin.merchant_code') }}:</label>
                                                        <input type="text" id="fawry_merchant_code"
                                                            name="fawry_merchant_code" placeholder="merchant_code"
                                                            value="{{ $settings['fawry_merchant_code'] ?? '' }}"
                                                            class="form-control" required>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label
                                                            for="fawry_return_url">{{ __('admin.return_url') }}:</label>
                                                        <input type="text" id="fawry_return_url"
                                                            name="fawry_return_url" placeholder="return_url"
                                                            value="{{ $settings['fawry_return_url'] ?? '' }}"
                                                            class="form-control" required>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="fawry_url">{{ __('admin.fawry_url') }}:</label>
                                                        <input type="text" id="fawry_url" name="fawry_url"
                                                            placeholder="fawry_url"
                                                            value="{{ $settings['fawry_url'] ?? '' }}"
                                                            class="form-control" required>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="fawry_webhook_url">{{ __('admin.webhook_url') }}:</label>
                                                        <div class="copy-container">
                                                            <input type="text" id="fawry_webhook_url" name="fawry_webhook_url"
                                                                   placeholder="fawry_webhook_url"
                                                                   value="{{ url('/api/fawry-callback') }}"
                                                                   class="form-control" readonly>
                                                            <button type="button" class="copy-button" data-copy-target="fawry_webhook_url" title="Copy">📋</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                                @if ($coin->type == 'utd_fawry')
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label
                                                                for="utd_fawry_secret">{{ __('admin.server_secret') }}:</label>
                                                            <input type="text" id="utd_fawry_secret" name="utd_fawry_secret"
                                                                   placeholder="secret"
                                                                   value="{{ $settings['utd_fawry_secret'] ?? '' }}"
                                                                   class="form-control" required>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label
                                                                for="utd_fawry_merchant_code">{{ __('admin.merchant_code') }}:</label>
                                                            <input type="text" id="utd_fawry_merchant_code"
                                                                   name="utd_fawry_merchant_code" placeholder="merchant_code"
                                                                   value="{{ $settings['utd_fawry_merchant_code'] ?? '' }}"
                                                                   class="form-control" required>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label for="utd_url">{{ __('admin.utd_url') }}:</label>
                                                            <input type="text" id="utd_url" name="utd_url"
                                                                   placeholder="utd_url"
                                                                   value="{{ $settings['utd_url'] ?? '' }}"
                                                                   class="form-control" required>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label for="utd_fawry_url">{{ __('admin.fawry_url') }}:</label>
                                                            <input type="text" id="utd_fawry_url" name="utd_fawry_url"
                                                                   placeholder="utd_fawry_url"
                                                                   value="{{ $settings['utd_fawry_url'] ?? '' }}"
                                                                   class="form-control" required>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label for="utd_fawry_return_url">{{ __('admin.return_url') }}:</label>
                                                            <div class="copy-container">
                                                                <input type="text" id="utd_fawry_return_url" name="utd_fawry_return_url"
                                                                       placeholder="utd_fawry_return_url"
                                                                       value="{{ url('/api/utd-fawry-callback') }}"
                                                                       class="form-control" readonly>
                                                                <button type="button" class="copy-button" data-copy-target="utd_fawry_return_url" title="Copy">📋</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif
                                            @if ($coin->type == 'strip')
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label
                                                            for="stripe_test_secret_key">{{ __('admin.test_secret_key') }}:</label>
                                                        <input type="text" id="stripe_test_secret_key"
                                                            name="stripe_test_secret_key"
                                                            placeholder="test_secret_key"
                                                            value="{{ $settings['stripe_test_secret_key'] ?? '' }}"
                                                            class="form-control" required>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label
                                                            for="stripe_success_url">{{ __('admin.success_url') }}:</label>
                                                        <input type="text" id="stripe_success_url"
                                                            name="stripe_success_url" placeholder="success_url"
                                                            value="{{ $settings['stripe_success_url'] ?? '' }}"
                                                            class="form-control" required>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label
                                                            for="stripe_cancel_url">{{ __('admin.cancel_url') }}:</label>
                                                        <input type="text" id="stripe_cancel_url"
                                                            name="stripe_cancel_url" placeholder="cancel_url"
                                                            value="{{ $settings['stripe_cancel_url'] ?? '' }}"
                                                            class="form-control" required>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label
                                                            for="stripe_currency">{{ __('admin.currency') }}:</label>
                                                        <input type="text" id="stripe_currency"
                                                            name="stripe_currency" placeholder="currency"
                                                            value="{{ $settings['stripe_currency'] ?? '' }}"
                                                            class="form-control" required>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label
                                                            for="stripe_webhook_secret">{{ __('admin.webhook_secret') }}:</label>
                                                        <input type="text" id="stripe_webhook_secret"
                                                            name="stripe_webhook_secret" placeholder="webhook_secret"
                                                            value="{{ $settings['stripe_webhook_secret'] ?? '' }}"
                                                            class="form-control" required>
                                                    </div>
                                                </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label for="stripe_webhook_url">{{ __('admin.webhook_url') }}:</label>
                                                            <div class="copy-container">
                                                                <input type="text" id="stripe_webhook_url" name="stripe_webhook_url"
                                                                       placeholder="stripe_webhook_url"
                                                                       value="{{ url('/api/stripe-callback') }}"
                                                                       class="form-control" readonly>
                                                                <button type="button" class="copy-button" data-copy-target="stripe_webhook_url" title="Copy">📋</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif
                                            @if ($coin->type == 'cash_free')
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label
                                                            for="cashfree_currency">{{ __('admin.currency') }}:</label>
                                                        <input type="text" id="cashfree_currency"
                                                            name="cashfree_currency" placeholder="currency"
                                                            value="{{ $settings['cashfree_currency'] ?? '' }}"
                                                            class="form-control" required>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label
                                                            for="opay_secret_key">{{ __('admin.app_id') }}:</label>
                                                        <input type="text" id="cashfree_app_id"
                                                            name="cashfree_app_id" placeholder="cashfree_app_id"
                                                            value="{{ $settings['cashfree_app_id'] ?? '' }}"
                                                            class="form-control">
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label
                                                            for="opay_country_code">{{ __('admin.secret_key') }}:</label>
                                                        <input type="text" id="cashfree_secret_key"
                                                            name="cashfree_secret_key"
                                                            placeholder="cashfree_secret_key"
                                                            value="{{ $settings['cashfree_secret_key'] ?? '' }}"
                                                            class="form-control" required>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label
                                                            for="opay_base_url">{{ __('admin.base_url') }}:</label>
                                                        <input type="text" id="cashfree_base_url"
                                                            name="cashfree_base_url" placeholder="base_url"
                                                            value="{{ $settings['cashfree_base_url'] ?? '' }}"
                                                            class="form-control" required>
                                                    </div>
                                                </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label for="opay_webhook_url">{{ __('admin.webhook_url') }}:</label>
                                                            <div class="copy-container">
                                                                <input type="text" id="opay_webhook_url" name="opay_webhook_url"
                                                                       placeholder="opay_webhook_url"
                                                                       value="{{ $settings['opay_webhook_url'] ?? '' }}"
                                                                       class="form-control" readonly>
                                                                <button type="button" class="copy-button" data-copy-target="opay_webhook_url" title="Copy">📋</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif
                                            @if ($coin->type == 'apple_pay')
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label
                                                            for="apple_team_id">{{ __('admin.apple_team_id') }}:</label>
                                                        <input type="text" id="apple_team_id" name="apple_team_id"
                                                            placeholder="apple_team_id"
                                                            value="{{ $settings['apple_team_id'] ?? '' }}"
                                                            class="form-control" required>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="apple_key_id">{{ __('admin.app_id') }}:</label>
                                                        <input type="text" id="apple_key_id" name="apple_key_id"
                                                            placeholder="apple_key_id"
                                                            value="{{ $settings['apple_key_id'] ?? '' }}"
                                                            class="form-control">
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label
                                                            for="apple_client_id">{{ __('admin.apple_client_id') }}:</label>
                                                        <input type="text" id="apple_client_id"
                                                            name="apple_client_id" placeholder="apple_client_id"
                                                            value="{{ $settings['apple_client_id'] ?? '' }}"
                                                            class="form-control" required>
                                                    </div>
                                                </div>


                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label
                                                            for="apple_redirect_uri">{{ __('admin.apple_service_file') }}:</label>
                                                        <input type="file" id="apple_service_file"
                                                            name="apple_service_file" placeholder="apple_service_file"
                                                            class="form-control">

                                                        <input type="text" id="apple_service_file" disabled
                                                            name="apple_service_file" placeholder="apple_service_file"
                                                            value="{{ $settings['apple_service_file'] ?? '' }}"
                                                            class="form-control">
                                                    </div>
                                                </div>

                                                @endif
                                            @if ($coin->type == 'google_pay')
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label
                                                            for="google_pay_payment_url">{{ __('admin.payment_url') }}:</label>
                                                        <input type="text" id="google_pay_payment_url"
                                                            name="google_pay_payment_url" placeholder="payment_url"
                                                            value="{{ $settings['google_pay_payment_url'] ?? '' }}"
                                                            class="form-control" required>
                                                    </div>
                                                </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label
                                                                for="google_pay_node_server_name">{{ __('admin.node_server_name') }}:</label>
                                                            <input type="text" id="google_pay_node_server_name"
                                                                   name="google_pay_node_server_name" placeholder="node_server_name"
                                                                   value="{{ $settings['google_pay_node_server_name'] ?? '' }}"
                                                                   class="form-control" required>
                                                        </div>
                                                    </div>
                                                @endif
                                            @if ($coin->type == 'sky_pay')
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label
                                                            for="paysky_base_url">{{ __('admin.base_url') }}:</label>
                                                        <input type="text" id="paysky_base_url"
                                                            name="paysky_base_url" placeholder="base_url"
                                                            value="{{ $settings['paysky_base_url'] ?? '' }}"
                                                            class="form-control" required>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label
                                                            for="paysky_merchant_id">{{ __('admin.merchant_id') }}:</label>
                                                        <input type="text" id="paysky_merchant_id"
                                                            name="paysky_merchant_id" placeholder="merchant_id"
                                                            value="{{ $settings['paysky_merchant_id'] ?? '' }}"
                                                            class="form-control" required>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label
                                                            for="paysky_terminal_id">{{ __('admin.terminal_id') }}:</label>
                                                        <input type="text" id="paysky_terminal_id"
                                                            name="paysky_terminal_id" placeholder="terminal_id"
                                                            value="{{ $settings['paysky_terminal_id'] ?? '' }}"
                                                            class="form-control" required>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label
                                                            for="paysky_api_key">{{ __('admin.api_key') }}:</label>
                                                        <input type="text" id="paysky_api_key"
                                                            name="paysky_api_key" placeholder="api_key"
                                                            value="{{ $settings['paysky_api_key'] ?? '' }}"
                                                            class="form-control" required>
                                                    </div>
                                                </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label for="paysky_webhook_url">{{ __('admin.webhook_url') }}:</label>
                                                            <div class="copy-container">
                                                                <input type="text" id="paysky_webhook_url" name="paysky_webhook_url"
                                                                       placeholder="paysky_webhook_url"
                                                                       value="{{ $settings['paysky_webhook_url'] ?? '' }}"
                                                                       class="form-control" readonly>
                                                                <button type="button" class="copy-button" data-copy-target="paysky_webhook_url" title="Copy">📋</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif
                                                @if ($coin->type == 'opay')
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label
                                                                for="opay_currency">{{ __('admin.currency') }}:</label>
                                                            <input type="text" id="opay_currency" name="opay_currency"
                                                                   placeholder="currency"
                                                                   value="{{ $settings['opay_currency'] ?? '' }}"
                                                                   class="form-control" required>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label
                                                                for="opay_secret_key">{{ __('admin.server_secret') }}:</label>
                                                            <input type="text" id="opay_secret_key"
                                                                   name="opay_secret_key" placeholder="server_secret"
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
                                                                   value="{{ $settings['opay_public_key'] ?? '' }}"
                                                                   class="form-control" required>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label
                                                                for="opay_merchant_id">{{ __('admin.merchant_id') }}:</label>
                                                            <input type="text" id="opay_merchant_id"
                                                                   name="opay_merchant_id" placeholder="merchant_id"
                                                                   value="{{ $settings['opay_merchant_id'] ?? '' }}"
                                                                   class="form-control" required>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label
                                                                for="opay_country_code">{{ __('admin.country_code') }}:</label>
                                                            <input type="text" id="opay_country_code"
                                                                   name="country_code" placeholder="server_secret"
                                                                   value="{{ $settings['country_code'] ?? '' }}"
                                                                   class="form-control" required>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label
                                                                for="opay_base_url">{{ __('admin.base_url') }}:</label>
                                                            <input type="text" id="opay_base_url" name="opay_base_url"
                                                                   placeholder="base_url"
                                                                   value="{{ $settings['opay_base_url'] ?? '' }}"
                                                                   class="form-control" required>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label for="opay_webhook_url">{{ __('admin.webhook_url') }}:</label>
                                                            <div class="copy-container">
                                                                <input type="text" id="opay_webhook_url" name="opay_webhook_url"
                                                                       placeholder="opay_webhook_url"
                                                                       value="{{ $settings['opay_webhook_url'] ?? '' }}"
                                                                       class="form-control" readonly>
                                                                <button type="button" class="copy-button" data-copy-target="opay_webhook_url" title="Copy">📋</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif
                                                @if ($coin->type == 'paytabs')
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label
                                                                for="profile_id">{{ __('admin.profile_id') }}:</label>
                                                            <input type="text" id="paytabs_profile_id"
                                                                   name="paytabs_profile_id"
                                                                   placeholder="paytabs_profile_id"
                                                                   value="{{ $settings['paytabs_profile_id'] ?? '' }}"
                                                                   class="form-control">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label
                                                                for="paytabs_server_key">{{ __('admin.server_key') }}:</label>
                                                            <input type="text" id="paytabs_server_key"
                                                                   name="paytabs_server_key"
                                                                   placeholder="paytabs_server_key"
                                                                   value="{{ $settings['paytabs_server_key'] ?? '' }}"
                                                                   class="form-control" required>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label
                                                                for="paytabs_base_url">{{ __('admin.base_url') }}:</label>
                                                            <input type="text" id="paytabs_base_url"
                                                                   name="paytabs_base_url" placeholder="paytabs_base_url"
                                                                   value="{{ $settings['paytabs_base_url'] ?? '' }}"
                                                                   class="form-control" required>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label
                                                                for="payment_address">{{ __('admin.payment_address') }}:</label>
                                                            <input type="text" id="paytabs_payment_address"
                                                                   name="paytabs_payment_address"
                                                                   placeholder="paytabs_payment_address"
                                                                   value="{{ $settings['paytabs_payment_address'] ?? '' }}"
                                                                   class="form-control" required>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label for="paytabs_webhook_url">{{ __('admin.webhook_url') }}:</label>
                                                            <div class="copy-container">
                                                                <input type="text" id="paytabs_webhook_url" name="paytabs_webhook_url"
                                                                       placeholder="paytabs_webhook_url"
                                                                       value="{{ $settings['paytabs_webhook_url'] ?? '' }}"
                                                                       class="form-control" readonly>
                                                                <button type="button" class="copy-button" data-copy-target="paytabs_webhook_url" title="Copy">📋</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif
                                                @if ($coin->type == 'paypal')
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label
                                                                for="paypal_base_url">{{ __('admin.base_url') }}:</label>
                                                            <input type="text" id="paypal_base_url"
                                                                   name="paypal_base_url" placeholder="paypal_base_url"
                                                                   value="{{ $settings['paypal_base_url'] ?? '' }}"
                                                                   class="form-control">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label
                                                                for="paypal_client_id">{{ __('admin.client_id') }}:</label>
                                                            <input type="text" id="paypal_client_id"
                                                                   name="paypal_client_id" placeholder="paypal_client_id"
                                                                   value="{{ $settings['paypal_client_id'] ?? '' }}"
                                                                   class="form-control">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label
                                                                for="paypal_client_secret">{{ __('admin.client_secret') }}:</label>
                                                            <input type="text" id="paypal_client_secret"
                                                                   name="paypal_client_secret"
                                                                   placeholder="paypal_client_secret"
                                                                   value="{{ $settings['paypal_client_secret'] ?? '' }}"
                                                                   class="form-control" required>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label
                                                                for="paypal_currency">{{ __('admin.currency') }}:</label>
                                                            <input type="text" id="paypal_currency"
                                                                   name="paypal_currency"
                                                                   placeholder="paypal_currency"
                                                                   value="{{ $settings['paypal_currency'] ?? '' }}"
                                                                   class="form-control" required>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label for="paypal_webhook_id">{{ __('admin.webhook_id') }}:</label>
                                                            <div class="copy-container">
                                                                <input type="text" id="paypal_webhook_id" name="paypal_webhook_id"
                                                                       placeholder="paypal_webhook_id"
                                                                       value="{{ $settings['paypal_webhook_id'] ?? '' }}"
                                                                       class="form-control">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label for="paypal_webhook_url">{{ __('admin.webhook_url') }}:</label>
                                                            <div class="copy-container">
                                                                <input type="text" id="paypal_webhook_url" name="paypal_webhook_url"
                                                                       placeholder="paypal_webhook_url"
                                                                       value="{{ url('/api/paypal-callback') }}"
                                                                       class="form-control" required>
                                                                <button type="button" class="copy-button" data-copy-target="paypal_webhook_url" title="Copy">📋</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif
                                                    @if ($coin->type == 'codapay')
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label
                                                                    for="codapay_base_url">{{ __('admin.base_url') }}:</label>
                                                                <input type="text" id="codapay_base_url"
                                                                       name="codapay_base_url" placeholder="codapay_base_url"
                                                                       value="{{ $settings['codapay_base_url'] ?? '' }}"
                                                                       class="form-control">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label
                                                                    for="codapay_api_key">{{ __('admin.api_key') }}:</label>
                                                                <input type="text" id="codapay_api_key"
                                                                       name="codapay_api_key" placeholder="codapay_api_key"
                                                                       value="{{ $settings['codapay_api_key'] ?? '' }}"
                                                                       class="form-control">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label
                                                                    for="codapay_project_id">{{ __('admin.project_id') }}:</label>
                                                                <input type="text" id="codapay_project_id"
                                                                       name="codapay_project_id"
                                                                       placeholder="codapay_project_id"
                                                                       value="{{ $settings['codapay_project_id'] ?? '' }}"
                                                                       class="form-control" required>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label for="codapay_webhook_url">{{ __('admin.webhook_url') }}:</label>
                                                                <div class="copy-container">
                                                                    <input type="text" id="codapay_webhook_url" name="codapay_webhook_url"
                                                                           placeholder="codapay_webhook_url"
                                                                           value="{{ url('/api/codapay-callback') }}"
                                                                           class="form-control" required>
                                                                    <button type="button" class="copy-button" data-copy-target="codapay_webhook_url" title="Copy">📋</button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                @endif

                                        </div>
                                        <button type="submit"
                                            class="btn btn-primary mt-3 btn-save">{{ __('save') }}</button>
                                    </div>
                                </form>
                            </div>
                        @endforeach


                    </div>
                </div>
            </div>
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
                                    <form action="{{ route('admin.ovip-config') }}" method="POST" enctype="multipart/form-data">
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
                                    <form action="{{ route('admin.ovip-config') }}" method="POST" enctype="multipart/form-data">
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
                                    <form action="{{ route('admin.exchange-coins') }}" method="POST" enctype="multipart/form-data">
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
                                                    <input type="text" class="user_coin_input form-control" placeholder="Enter value">
                                                    <input type="hidden" class="exchange_rate" value="{{ $settings['exchange_coin_percentage'] ?? 1 }}">
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
                            <form action="{{ route('admin.app.settings.update') }}" method="POST" enctype="multipart/form-data">
                                @csrf
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
                            <form action="{{ route('admin.app.settings.update') }}" method="POST" enctype="multipart/form-data">
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
                            <form action="{{ route('admin.app.settings.update') }}" method="POST" enctype="multipart/form-data">
                                @csrf
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
                            <select name="background_type" id="background_type" class="form-control" onchange="toggleBackgroundInput()">
                                <option value="color" {{ data_get($settings, 'background_type') === 'color' ? 'selected' : '' }}>Color</option>
                                <option value="image" {{ data_get($settings, 'background_type') === 'image' ? 'selected' : '' }}>Image</option>
                                <option value="gradient" {{ data_get($settings, 'background_type') === 'gradient' ? 'selected' : '' }}>Gradient</option>
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
                                    <img src="{{ getImagePath(data_get($settings, 'app_background')) }}" width="100" class="img-thumbnail">
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

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="is_new_theme_enabled">{{ __('New Theme Enabled') }}</label>
                                <input type="hidden" name="is_new_theme_enabled" value="0">
                                <input type="checkbox" name="is_new_theme_enabled" value="1" data-bootstrap-switch {{ data_get($settings, 'is_new_theme_enabled') ? 'checked' : '' }}>
                            </div>
                        </div>


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
                                                for="supabase_service_role_key">{{ __('supabase_service_role_key') }}:</label>
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
               $(document).on('change', '.libraryRealTime', function() {
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
                function updateLibrary(selectedLibrary, inputName) {
                    let data = {
                        _token: "{{ csrf_token() }}",
                    };
                    data[inputName] = selectedLibrary;

                    $.ajax({
                        url: "{{ route('admin.update-agora-zego') }}",
                        type: "POST",
                        data: data,
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
                    let inputName = $(this).attr('name');
                    console.log("Selected library:", selectedLibrary);
                    console.log("library Name:", inputName);
                    updateLibrary(selectedLibrary, inputName);
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
                    const activeTab = getQueryParam("firsttab") || "brandSettings";
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


                document.addEventListener("DOMContentLoaded", function() {
                    let resetButton = document.getElementById('resetColors');

                    let resetApColorSettingpButton = document.getElementById('resetAppColorsSettings');

                    if (resetApColorSettingpButton) {
                        resetApColorSettingpButton.addEventListener('click', function() {
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
                        resetButton.addEventListener('click', function() {
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

                document.addEventListener("DOMContentLoaded", function() {
                    document.querySelectorAll('input[type="color"]').forEach(input => {
                        input.addEventListener("input", function() {
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


<style>
/* Container */
#landPageSettings {
    max-width: 1020px;
    margin: 18px auto;
    border-radius: 10px;
    padding: 22px;
    box-shadow: 0 6px 20px rgba(0,0,0,0.06);
    font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial;
    color: #222;
    background: white;
}

/* layout */
.settings-container {
    display: flex;
    gap: 20px;
    align-items: flex-start;
}

/* sidebar */
.tabs-sidebar {
    min-width: 230px;
    display: flex;
    flex-direction: column;
    gap: 8px;
    border-left: 1px solid #eee; /* move border to left side */
    padding-left: 12px; /* spacing on left side */
    border-right: none;
    direction: rtl; /* aligns text and elements right-to-left */
}

.tab-btn {
    background-color: var(--secondary-color);
    border: none;
    padding: 10px 15px;
    border-radius: 10px;
    text-align: right; /* align text to the right */
    cursor: pointer;
    transition: all 0.3s ease;
}


.tab-btn:hover {
    transform: translateY(-1px);
}
.tab-btn.active {
    background: var(--primary-color); /* bootstrap primary */
    color: #fff;
    border-color: rgba(13,110,253,0.9);
}

/* content */
.tab-content {
    flex: 1;
    min-width: 0;
}
.tab-pane {
    display: none;
}
.tab-pane.show {
    display: block;
}

/* header and hr */
#landPageSettings h5 {
    font-weight: 700;
    margin-bottom: 8px;
}
#landPageSettings hr {
    margin-top: 8px;
    margin-bottom: 14px;
    border: none;
    height: 1px;
    /* background: linear-gradient(90deg,#eee,#f5f5f5); */
}

/* responsive: on small screens show tabs horizontally above content */
@media (max-width: 767px) {
    .settings-container {
        flex-direction: column;
    }
.tabs-sidebar {
       width: 100%;
        order: 0;
        border-right: none;
        border-bottom: 1px solid #eee;
        padding-right: 0;
        padding-bottom: 10px;
        flex-direction: row; /* horizontal layout */
        justify-content: flex-start;
        gap: 10px;
        overflow-x: auto;
    }
    .tab-btn {
        white-space: nowrap;
        padding: 8px 10px;
        font-size: 14px;
    }
    .tab-content {
        margin-top: 12px;
    }
}

/* small improvements for inputs */
.form-control {
    width: 100%;
    padding: 8px 10px;
    border: 1px solid #dcdcdc;
    border-radius: 6px;
    box-sizing: border-box;
}
.row { display:flex; flex-wrap:wrap; gap:12px; }
.col-md-6 { flex: 0 0 calc(50% - 12px); min-width: 240px; }
.col-md-4 { flex: 0 0 calc(33.333% - 12px); min-width: 160px; }

@media (max-width: 767px) {
    .col-md-6, .col-md-4 { flex: 1 1 100%; min-width: 0; }
    .settings-content {
    width: 100% !important;
}
}
</style>
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-colorpicker/2.5.3/css/bootstrap-colorpicker.min.css" rel="stylesheet">

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-colorpicker/2.5.3/js/bootstrap-colorpicker.min.js"></script>
<script>



    // $('.colorpicker-element').colorpicker();

     $('.colorpicker-element').colorpicker({

        align: 'left',     // Align dropdown to the right (for English dashboard)
        horizontal: true    // Show horizontal sliders
    });




document.addEventListener("DOMContentLoaded", function() {
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
                firstInput.focus({ preventScroll: true });
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
                    tabContent.scrollIntoView({ behavior: 'smooth' });
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
    $(document).ready(function() {
        $('.select2-country').select2({
            placeholder: "{{ __('Select a country') }}",
            allowClear: true,
            width: '100%'
        });
    });
</script>
