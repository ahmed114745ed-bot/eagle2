
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
            $selectedTimeZone = App\Models\Setting::where('key','timezone')->first();
        @endphp
        <label for="">TimeZone</label>


        <button type="submit">Update</button>
    </form>
</body>
</html> -->

    @php
         $selectedTimeZone = App\Models\Setting::where( 'key','timezone')->first();
                $settings = App\Models\Setting::pluck('value', 'key')->toArray();

            @endphp
    <style>




    body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #121212;
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

        .settings-menu button:hover {
            background: #ff9800;
        }

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

        /* تنسيق النماذج */
        form {
            background: #222;
            padding: 20px;
            border-radius: 5px;
        }

        label {
            display: block;
            margin: 10px 0 5px;
        }

        input, select {
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
            display: inline-flex
        ;
        }
        .wrapper{
            width: 100%;

        }
        .settings-content{
            width: 869px;

        }
        .form{
            width: 400px;
            margin: auto;
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
            background-color: rgba(0, 0, 0, 0.9);
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
        img{
            width: 201px;
            display: block;
            height: 99px;
            margin-bottom: 20px;
        }

        button{
            width: 200px;

        }


    </style>
</head>
<body>
<div class="all-page">
    <div class="settings-sidebar">
        <h2>إعدادات</h2>
        <div class="settings-menu">
            <button onclick="showSection('brandSettings')">{{  __('Brand settings')}}</button>
            <button onclick="showSection('themeSettings')">{{  __('Theme settings')}}</button>
            <button onclick="showSection('timeSettings')"> {{  __('Timing settings')}}</button>
        </div>
    </div>

    <div class="settings-content">
        <div id="brandSettings" class="settings-section active">

            <h3> {{  __('Brand settings')}}</h3>

            <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="form">
                <label>{{  __('Application title:')}} </label>
                <input type="text" name="app_title" value="{{ $settings['app_title'] ?? '' }}" class="form-control">

                <label> {{  __('Application logo:')}}</label>
                <input type="file" name="app_logo" class="form-control">
                @if(!empty($settings['app_logo']))
                    <img src="{{ asset('uploads/settings/' . $settings['app_logo']) }}" width="100" class="mt-2" onclick="openFullScreen(this)">
                @endif


                <label> {{  __('Application Fav Icon:')}}</label>
                <input type="file" name="app_fav_icon" class="form-control">
                @if(!empty($settings['app_fav_icon']))
                    <img src="{{ asset('uploads/settings/' . $settings['app_fav_icon']) }}" width="100" class="mt-2" onclick="openFullScreen(this)">
                @endif

                <button type="submit">{{ __('save') }}</button>
                </div>

            </form>
        </div>

        <div id="themeSettings" class="settings-section">
            <h3>{{  __('Theme settings')}}</h3>
            <form action="{{ route('admin.settings.update') }}" method="POST">
            <div class="form">
                @csrf
                <label>{{ __('Primary Color:') }}</label>
                <input type="color" name="primary_color" value="{{ $settings['primary_color'] ?? '#000000' }}"
                    style="background: {{ $settings['primary_color'] ?? '#000000' }};">

                <label>{{ __('Secondary Color:') }}</label>
                <input type="color" name="secondary_color" value="{{ $settings['secondary_color'] ?? '#FFFFFF' }}"
                    style="background: {{ $settings['secondary_color'] ?? '#FFFFFF' }};">

                <label>{{ __('Text Primary Color:') }}</label>
                <input type="color" name="text_primary_color" value="{{ $settings['text_primary_color'] ?? '#000000' }}"
                    style="background: {{ $settings['text_primary_color'] ?? '#000000' }};">

                <label>{{ __('Text Secondary Color:') }}</label>
                <input type="color" name="text_secondary_color" value="{{ $settings['text_secondary_color'] ?? '#808080' }}"
                    style="background: {{ $settings['text_secondary_color'] ?? '#808080' }};">

                <label>{{ __('Box Background Color:') }}</label>
                <input type="color" name="box_background_color" value="{{ $settings['box_background_color'] ?? '#F8F9FA' }}"
                    style="background: {{ $settings['box_background_color'] ?? '#F8F9FA' }};">

                <label>{{ __('Table Background Color:') }}</label>
                <input type="color" name="table_background_color" value="{{ $settings['table_background_color'] ?? '#FFFFFF' }}"
                    style="background: {{ $settings['table_background_color'] ?? '#FFFFFF' }};">

                <button type="submit">{{ __('save') }}</button>
                </div>
            </form>
        </div>

        <div id="timeSettings" class="settings-section">
            <h3>{{  __('Timing settings')}}</h3>
            <form action="{{ route('admin.settings.update') }}" method="POST">
                @csrf
                <div class="form">

                <label>{{ __('Time zone:') }}</label>
                <select name="timezone">
                    @foreach ($timezones as $timezone)
                        <option value="{{ $timezone->name }}"
                                {{ $timezone->name == ($settings['timezone'] ?? '') ? 'selected' : '' }}>
                            {{ $timezone->name }}
                        </option>
                    @endforeach
                </select>

                <button type="submit">{{ __('save') }}</button>
                </div>
            </form>
        </div>
    </div>
    <div id="imageModal" class="modal" onclick="closeFullScreen()">
    <span class="close">&times;</span>
    <img class="modal-content" id="fullImage">
</div>
    <!-- كود JavaScript -->
    <script>
        function showSection(sectionId) {
        //     document.querySelectorAll('.settings-section').forEach(section => {
        //         section.classList.remove('active');
        //     });

        //     document.getElementById(sectionId).classList.add('active');
        // }

        document.querySelectorAll('.settings-menu button').forEach(button => {
            button.style.backgroundColor = ''; // إرجاع اللون الافتراضي
            button.style.color = ''; // إرجاع لون النص الافتراضي
        });

        // إخفاء جميع الأقسام
        document.querySelectorAll('.settings-section').forEach(section => {
            section.classList.remove('active');
        });

        // إظهار القسم المطلوب
        document.getElementById(sectionId).classList.add('active');

        // تحديد الزر المضغوط عليه
        const activeButton = document.querySelector(`.settings-menu button[onclick="showSection('${sectionId}')"]`);

        // تعيين لون الزر من متغير CSS
        activeButton.style.backgroundColor = 'var(--primary-color)';
        activeButton.style.color = 'var(--text-primary-color)';


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
    </script>
    </div>
</body>
