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
    gap: 20px; /* Space between options */
    align-items: center;
    margin: 15px 0;
}

.radio-option {
    display: flex;
    align-items: center;
    gap: 8px; /* Space between radio and label */
}

.radio-input {
    margin: 0; /* Remove default margins */
}

.radio-label {
    margin: 0; /* Remove default margins */
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
    background:  var(--primary-color);
    border-radius: 50%;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
}
    
    body {
        font-family: Arial, sans-serif;
        margin: 0;
        padding: 0;
        background-color:var(--secondary-color);
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
    display: block; /* Show active section */
}

    /* تنسيق النماذج */
    form {
        background: var(--box-background-color);
        padding: 20px;
        border-radius: 5px;
        width: 712px;
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

        button{
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
        background-color: var(--secondary-color);;
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
    color:var(--text-primary-color) ;
    overflow-x: auto;  /* يجعل الشريط قابلاً للتمرير عند الحاجة */
    white-space: nowrap; /* يمنع العناصر من النزول لسطر جديد */
    scrollbar-width: thin; /* تقليل عرض شريط التمرير */
}

.settings-menu {
    display: flex;
    gap: 4px;
    color:var(--text-primary-color) ;
    overflow-x: auto;  /* يجعل الشريط قابلاً للتمرير عند الحاجة */
    white-space: nowrap; /* يمنع العناصر من النزول لسطر جديد */
    scrollbar-width: thin; /* تقليل عرض شريط التمرير */

}

.settings-menu button {
    background-color: var(--box-background-color);
    border: none;
    padding: 10px 15px;
    font-size: 16px;
    cursor: pointer;
    transition: color 0.3s ease-in-out;
    color:var(--text-primary-color) !important;

}

.settings-menu button:hover {
    background: #ff9800;
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

            <h3> {{  __('Brand settings')}}</h3>

            <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="form row">

                <div class="col-md-6">
                    <div class="form-group">
                        <label>{{  __('Application title:')}} </label>
                        <input type="text" name="app_title" value="{{ $settings['app_title'] ?? '' }}" class="form-control">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label> {{  __('Application logo:')}}</label>
                        <input type="file" name="app_logo" class="form-control">
                        @if(!empty($settings['app_logo']))
                            <img src="{{ getImagePath( $settings['app_logo']) }}" width="100" class="mt-2" onclick="openFullScreen(this)">
                        @endif
                    </div>
                </div>
                 <div class="col-md-12">
                    <div class="form-group">
                        <label> {{  __('Application Fav Icon:')}}</label>
                        <input type="file" name="app_fav_icon" class="form-control">
                        @if(!empty($settings['app_fav_icon']))
                            <img src="{{ getImagePath( $settings['app_fav_icon']) }}" width="100" class="mt-2" onclick="openFullScreen(this)">
                        @endif
                    </div>                      
                </div>                      
             
                       <button type="submit">{{ __('save') }}</button>

                    
                </div>

            </form>
        </div>

        <div id="themeSettings" class="settings-section">
            <h3>{{  __('Theme settings')}}</h3>
            <form id="themeSettingsForm"  action="{{ route('admin.settings.update') }}" method="POST">
            <div class="form row">
                @csrf

        <div class="col-md-6">
        <div class="form-group">
            <label for="primary_color">{{ __('Primary Color:') }}</label>
            <input type="color" id="primary_color" name="primary_color"
                value="{{ $settings['primary_color'] ?? '#000000' }}"
                style="background: {{ $settings['primary_color'] ?? '#000000' }};"
                title="لون الواجهة الرئيسي، يتم استخدامه في الأزرار والخلفيات الأساسية.">
        </div>
    </div>

    <div class="col-md-6">
        <div class="form-group">
            <label for="secondary_color">{{ __('Secondary Color:') }}</label>
            <input type="color" id="secondary_color" name="secondary_color"
                value="{{ $settings['secondary_color'] ?? '#FFFFFF' }}"
                style="background: {{ $settings['secondary_color'] ?? '#FFFFFF' }};"
                title="اللون الثانوي المستخدم كخلفية لبعض الأقسام أو لتوضيح بعض العناصر.">
        </div>
    </div>

    <div class="col-md-6">
        <div class="form-group">
            <label for="text_primary_color">{{ __('Text Primary Color:') }}</label>
            <input type="color" id="text_primary_color" name="text_primary_color"
                value="{{ $settings['text_primary_color'] ?? '#000000' }}"
                style="background: {{ $settings['text_primary_color'] ?? '#000000' }};"
                title="لون النص الأساسي الذي يظهر في العناوين والمحتوى الرئيسي.">
        </div>
    </div>

    <div class="col-md-6">
        <div class="form-group">
            <label for="text_secondary_color">{{ __('Text Secondary Color:') }}</label>
            <input type="color" id="text_secondary_color" name="text_secondary_color"
                value="{{ $settings['text_secondary_color'] ?? '#808080' }}"
                style="background: {{ $settings['text_secondary_color'] ?? '#808080' }};"
                title="لون النص الثانوي المستخدم في الشروحات أو النصوص المساعدة.">
        </div>
    </div>

    <div class="col-md-6">
        <div class="form-group">
            <label for="box_background_color">{{ __('Box Background Color:') }}</label>
            <input type="color" id="box_background_color" name="box_background_color"
                value="{{ $settings['box_background_color'] ?? '#F8F9FA' }}"
                style="background: {{ $settings['box_background_color'] ?? '#F8F9FA' }};"
                title="لون خلفية الصناديق أو الكروت داخل التطبيق.">
        </div>
    </div>

    <div class="col-md-6">
        <div class="form-group">
            <label for="table_background_color">{{ __('Table Background Color:') }}</label>
            <input type="color" id="table_background_color" name="table_background_color"
                value="{{ $settings['table_background_color'] ?? '#FFFFFF' }}"
                style="background: {{ $settings['table_background_color'] ?? '#FFFFFF' }};"
                title="لون خلفية الجداول في التقارير أو البيانات.">
        </div>
    </div>

    <div class="col-12 d-flex gap-3 mt-3">
        <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
        <button type="button" id="resetColors" class="btn btn-secondary">{{ __('Reset Colors') }}</button>
    </div>
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

        <div id="realTimeSetting" class="settings-section">
            <form action="{{ route('admin.update-agora-zego') }}" method="POST">
                @csrf
                <div class="form">
                    <label class="d-block">{{ __('Real Time system Setting:') }}</label>
                    
                    <div class="radio-options-container">
                        <div class="radio-option">
                            <input type="radio" id="libraryAgora" name="library" value="0" class="radio-input" {{ $library == "0" ? "checked" : "" }}>
                            <label for="libraryAgora" class="radio-label">{{ __('admin.Agora') }}</label>
                        </div>
                    
                        <div class="radio-option">
                            <input type="radio" id="libraryZego" name="library" value="1" class="radio-input" {{ $library == "1" ? "checked" : "" }}>
                            <label for="libraryZego" class="radio-label">{{ __('admin.Zego') }}</label>
                        </div>
                    
                        <div class="radio-option">
                            <input type="radio" id="libraryPusher" name="library" value="2" class="radio-input" {{ $library == "2" ? "checked" : "" }}>
                            <label for="libraryPusher" class="radio-label">{{ __('pusher') }}</label>
                        </div>
                    </div>
        
                    <div class="library-fields-container mt-4">
                        <!-- Agora Fields -->
                        <div id="agora-fields" class="library-fields" style="display: {{ $library == 0 ? 'flex' : 'none' }};">
                            <div style="flex: 1; margin-right: 10px;">
                                <h1 class="control-label text-center">{{ __('admin.Agora') }}</h1>
                                <div class="form-group">
                                    <label for="agora_app_id" class="control-label">{{ __('admin.app_id') }}:</label>
                                    <input type="text" id="agora_app_id" name="app_id" placeholder="app_id" value="{{ $agora_app_id }}" class="form-control">
                                </div>
                            </div>
                        </div>
            
                        <!-- Zego Fields -->
                        <div id="zego-fields" class="library-fields" style="display: {{ $library == 1 ? 'flex' : 'none' }};">
                            <div style="flex: 1; margin-left: 10px;">
                                <h1 class="control-label text-center">{{ __('admin.Zego') }}</h1>
                                <div class="form-group">
                                    <label for="zego_server_secret" class="control-label">{{ __('admin.server_secret') }}:</label>
                                    <input type="text" id="zego_server_secret" name="zego_server_secret" placeholder="server_secret" value="{{ $zego_server_secret }}" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label for="zego_app_id" class="control-label">{{ __('admin.app_id') }}:</label>
                                    <input type="text" id="zego_app_id" name="zego_app_id" placeholder="app_id" value="{{ $zego_app_id }}" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label for="app_sign" class="control-label">{{ __('admin.app_sign') }}:</label>
                                    <input type="text" id="app_sign" name="app_sign" placeholder="app_sign" value="{{ $app_sign }}" class="form-control">
                                </div>
                            </div>
                        </div>
            
                        <!-- Pusher Fields -->
                        <div id="pusher-fields" class="library-fields" style="display: {{ $library == 2 ? 'flex' : 'none' }};">
                            <div style="flex: 1; margin-left: 10px;">
                                <h1 class="control-label text-center">{{ __('pusher') }}</h1>
                                <!-- Pusher fields can be added here -->
                            </div>
                        </div>
                    </div>
            
                    <button type="submit" class="btn btn-primary mt-3">{{ __('save') }}</button>
                </div>
            </form>
        </div>
        
        <div id="appSettings" class="settings-section">
            <h3>{{  __('Timing settings')}}</h3>
            <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="form row">
                @csrf

                <div class="col-md-6">
                       <div class="form-group">                  
                            <label for="primary_color">{{ __('Primary Color') }}</label>
                            <input type="color" id="app_primary_color" name="app_primary_color" value="#3498db" class="form-control">
                        </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label for="second_color">{{ __('Second Color') }}</label>
                        <input type="color" id="second_color" name="app_second_color" value="#2ecc71" class="form-control">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="background_type">{{ __('Background Type') }}</label>
                        <select id="background_type" name="background_type" class="form-control" onchange="toggleBackgroundInput()">
                            <option value="color">{{ __('Color') }}</option>
                            <option value="image">{{ __('Image') }}</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group" id="background_color_group">
                        <label for="background_color">{{ __('Background Color') }}</label>
                        <input type="color" id="background_color" class="form-control" onchange="updateBackgroundValue()">
                    </div>
                </div>
                <div class="col-md-6">

                <div class="form-group" id="background_image_group" style="display: none;">
                    <label for="background_image">{{ __('Background Image') }}</label>
                    <input type="file" id="background_image" class="form-control" onchange="updateBackgroundValue()">
                </div>
                </div>

                @php
                    $image1 = Cache::get('image1');
                    $image2 = Cache::get('image2');
                    $image3 = Cache::get('image3');
                @endphp

                <div class=" col-md-12 row">
                    <!-- Image 1 -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="image1">{{ __('Image 1') }}</label>
                            <input type="file" id="image1" name="image1" class="form-control" onchange="updatePreview('image1')">
                            @if($image1)
                                <div class="mt-2">
                                    <!-- <label>{{ __('Old Image') }}</label><br> -->
                                    <img src="{{ getImagePath(  $image1) }}" alt="Old Image 1" width="100">
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Image 2 -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <!-- <label for="image2">{{ __('Image 2') }}</label> -->
                            <input type="file" id="image2" name="image2" class="form-control" onchange="updatePreview('image2')">
                            @if($image2)
                                <div class="mt-2">
                                    <label>{{ __('Old Image') }}</label><br>
                                    <img src="{{ getImagePath(  $image2) }}" alt="Old Image 2" width="100">
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Image 3 -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <!-- <label for="image3">{{ __('Image 3') }}</label> -->
                            <input type="file" id="image3" name="image3" class="form-control" onchange="updatePreview('image3')">
                            @if($image3)
                                <div class="mt-2">
                                    <label>{{ __('Old Image') }}</label><br>
                                    <img src="{{ getImagePath(  $image3) }}" alt="Old Image 3" width="100">
                                </div>
                            @endif
                        </div>
                    </div>
                </div>


                <input type="hidden" id="app_background" name="app_background">

                <button type="submit" class="btn btn-primary mt-3">{{ __('Save Settings') }}</button>

            </div>
            </form>
        </div>


        
        <div id="imageModal" class="modal" onclick="closeFullScreen()">
            <span class="close">&times;</span>
            <img class="modal-content" id="fullImage">
        </div>
        <!-- كود JavaScript -->
        <script>
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
                    activeButton.style.color = 'var(--text-primary-color)';
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
        var type = document.getElementById("background_type").value;
        document.getElementById("background_color_group").style.display = (type === "color") ? "block" : "none";
        document.getElementById("background_image_group").style.display = (type === "image") ? "block" : "none";

        updateBackgroundValue();
    }

    function updateBackgroundValue() {
        var type = document.getElementById("background_type").value;
        var hiddenInput = document.getElementById("app_background");

        if (type === "color") {
            hiddenInput.value = document.getElementById("background_color").value;
        } else if (type === "image") {
            var fileInput = document.getElementById("background_image");
            if (fileInput.files.length > 0) {
                hiddenInput.value = fileInput.files[0].name; // حفظ اسم الملف فقط
            } else {
                hiddenInput.value = "";
            }
        }
    }
    </script>

<script>
document.addEventListener('DOMContentLoaded', function() {
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
});

  document.addEventListener("DOMContentLoaded", function () {
        let resetButton = document.getElementById('resetColors');

        if (resetButton) {
            resetButton.addEventListener('click', function () {
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

                // إرسال النموذج لحفظ التغييرات وإعادة تحميل الصفحة
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
</script>
    <script>
        function updatePreview(inputId) {
            const input = document.getElementById(inputId);
            const reader = new FileReader();
            reader.onload = function(e) {
                const img = input.parentElement.querySelector('img');
                if (img) img.src = e.target.result;
            }
            if (input.files && input.files[0]) {
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
    </div>
</body>

