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
    
    <div class="settings-content">
        <!-- Brand Settings Section -->
        <div id="brandSettings" class="settings-section active">
            <h3>{{ __('Brand settings') }}</h3>
            <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="form row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>{{ __('Application title:') }}</label>
                            <input type="text" name="app_title" value="{{ $settings['app_title'] ?? '' }}" class="form-control">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>{{ __('Application logo:') }}</label>
                            <input type="file" name="app_logo" class="form-control" onchange="previewImage(event)">
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
                            <img id="favIconPreview" 
                                 src="{{ !empty($settings['app_fav_icon']) ? getImagePath($settings['app_fav_icon']) : '' }}" 
                                 width="100" class="mt-2" 
                                 style="{{ !empty($settings['app_fav_icon']) ? '' : 'display:none;' }}" 
                                 onclick="openFullScreen(this)">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">{{ __('save') }}</button>
                </div>
            </form>
        </div>

        <!-- Theme Settings Section -->
        <div id="themeSettings" class="settings-section">
            <h3>{{ __('Theme settings') }}</h3>
            <form id="themeSettingsForm" action="{{ route('admin.settings.update') }}" method="POST">
                @csrf
                <div class="form row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="primary_color">{{ __('Primary Color:') }}</label>
                            <input type="color" id="primary_color" name="primary_color" 
                                   value="{{ $settings['primary_color'] ?? '#000000' }}" 
                                   style="background: {{ $settings['primary_color'] ?? '#000000' }};" 
                                   class="form-control">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="secondary_color">{{ __('Secondary Color:') }}</label>
                            <input type="color" id="secondary_color" name="secondary_color" 
                                   value="{{ $settings['secondary_color'] ?? '#FFFFFF' }}" 
                                   style="background: {{ $settings['secondary_color'] ?? '#FFFFFF' }};" 
                                   class="form-control">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="text_primary_color">{{ __('Text Primary Color:') }}</label>
                            <input type="color" id="text_primary_color" name="text_primary_color" 
                                   value="{{ $settings['text_primary_color'] ?? '#000000' }}" 
                                   style="background: {{ $settings['text_primary_color'] ?? '#000000' }};" 
                                   class="form-control">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="text_secondary_color">{{ __('Text Secondary Color:') }}</label>
                            <input type="color" id="text_secondary_color" name="text_secondary_color" 
                                   value="{{ $settings['text_secondary_color'] ?? '#808080' }}" 
                                   style="background: {{ $settings['text_secondary_color'] ?? '#808080' }};" 
                                   class="form-control">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="box_background_color">{{ __('Box Background Color:') }}</label>
                            <input type="color" id="box_background_color" name="box_background_color" 
                                   value="{{ $settings['box_background_color'] ?? '#F8F9FA' }}" 
                                   style="background: {{ $settings['box_background_color'] ?? '#F8F9FA' }};" 
                                   class="form-control">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="table_background_color">{{ __('Table Background Color:') }}</label>
                            <input type="color" id="table_background_color" name="table_background_color" 
                                   value="{{ $settings['table_background_color'] ?? '#FFFFFF' }}" 
                                   style="background: {{ $settings['table_background_color'] ?? '#FFFFFF' }};" 
                                   class="form-control">
                        </div>
                    </div>
                    <div class="col-12 d-flex gap-3 mt-3">
                        <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
                        <button type="button" id="resetColors" class="btn btn-secondary">{{ __('Reset Colors') }}</button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Time Settings Section -->
        <div id="timeSettings" class="settings-section">
            <h3>{{ __('Timing settings') }}</h3>
            <form action="{{ route('admin.settings.update') }}" method="POST">
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
                    <button type="submit" class="btn btn-primary mt-3">{{ __('save') }}</button>
                </div>
            </form>
        </div>

        <!-- App Settings Section -->
        <div id="appSettings" class="settings-section">
            <h3>{{ __('App settings') }}</h3>
            <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="form row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="app_primary_color">{{ __('Primary Color') }}</label>
                            <input type="color" id="app_primary_color" name="app_primary_color" 
                                   value="{{ $settings['app_primary_color'] ?? '#3498db' }}" 
                                   class="form-control">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="second_color">{{ __('Second Color') }}</label>
                            <input type="color" id="second_color" name="app_second_color" 
                                   value="{{ $settings['app_second_color'] ?? '#2ecc71' }}" 
                                   class="form-control">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="background_type">{{ __('Background Type') }}</label>
                            <select id="background_type" name="background_type" class="form-control" onchange="toggleBackgroundInput()">
                                <option value="color" {{ ($settings['background_type'] ?? 'color') == 'color' ? 'selected' : '' }}>{{ __('Color') }}</option>
                                <option value="image" {{ ($settings['background_type'] ?? 'color') == 'image' ? 'selected' : '' }}>{{ __('Image') }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group" id="background_color_group">
                            <label for="background_color">{{ __('Background Color') }}</label>
                            <input type="color" id="background_color" name="background_color" 
                                   value="{{ $settings['background_color'] ?? '#ffffff' }}" 
                                   class="form-control" onchange="updateBackgroundValue()">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group" id="background_image_group" style="display: none;">
                            <label for="background_image">{{ __('Background Image') }}</label>
                            <input type="file" id="background_image" name="app_background_image" 
                                   class="form-control" onchange="updateBackgroundValue()">
                            @if(!empty($settings['app_background_image']))
                                <img src="{{ getImagePath($settings['app_background_image']) }}" 
                                     width="100" class="mt-2">
                            @endif
                        </div>
                    </div>
                    <input type="hidden" id="app_background" name="app_background">
                    <div class="col-12 d-flex gap-3 mt-3">
                        <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
                        <button type="button" id="resetAppColors" class="btn btn-secondary">{{ __('Reset Colors') }}</button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Real Time Settings Section -->
        <div id="realTimeSetting" class="settings-section">
            <form action="{{ route('admin.update-agora-zego') }}" method="POST">
                @csrf
                <div class="form">
                    <label class="d-block">{{ __('Real Time system Setting:') }}</label>
                    
                    <div class="radio-options-container">
                        <div class="radio-option">
                            <input type="radio" id="libraryAgora" name="library" value="0" 
                                   class="radio-input" {{ $library == "0" ? "checked" : "" }}>
                            <label for="libraryAgora" class="radio-label">{{ __('admin.Agora') }}</label>
                        </div>
                        <div class="radio-option">
                            <input type="radio" id="libraryZego" name="library" value="1" 
                                   class="radio-input" {{ $library == "1" ? "checked" : "" }}>
                            <label for="libraryZego" class="radio-label">{{ __('admin.Zego') }}</label>
                        </div>
                        <div class="radio-option">
                            <input type="radio" id="libraryPusher" name="library" value="2" 
                                   class="radio-input" {{ $library == "2" ? "checked" : "" }}>
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
                                    <input type="text" id="agora_app_id" name="app_id" placeholder="app_id" 
                                           value="{{ $agora_app_id }}" class="form-control">
                                </div>
                            </div>
                        </div>
            
                        <!-- Zego Fields -->
                        <div id="zego-fields" class="library-fields" style="display: {{ $library == 1 ? 'flex' : 'none' }};">
                            <div style="flex: 1; margin-left: 10px;">
                                <h1 class="control-label text-center">{{ __('admin.Zego') }}</h1>
                                <div class="form-group">
                                    <label for="zego_server_secret" class="control-label">{{ __('admin.server_secret') }}:</label>
                                    <input type="text" id="zego_server_secret" name="zego_server_secret" 
                                           placeholder="server_secret" value="{{ $zego_server_secret }}" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label for="zego_app_id" class="control-label">{{ __('admin.app_id') }}:</label>
                                    <input type="text" id="zego_app_id" name="zego_app_id" placeholder="app_id" 
                                           value="{{ $zego_app_id }}" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label for="app_sign" class="control-label">{{ __('admin.app_sign') }}:</label>
                                    <input type="text" id="app_sign" name="app_sign" placeholder="app_sign" 
                                           value="{{ $app_sign }}" class="form-control">
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
    </div>

    <!-- Image Modal -->
    <div id="imageModal" class="modal" onclick="closeFullScreen()">
        <span class="close">&times;</span>
        <img class="modal-content" id="fullImage">
    </div>

    <!-- JavaScript -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // Initialize the active tab
        document.addEventListener("DOMContentLoaded", function() {
            function getQueryParam(name) {
                const urlParams = new URLSearchParams(window.location.search);
                return urlParams.get(name);
            }

            const activeTab = getQueryParam("firsttab") || "brandSettings";
            showSection(activeTab);
            
            // Initialize background type
            toggleBackgroundInput();
        });

        function showSection(sectionId) {
            // Hide all sections
            document.querySelectorAll('.settings-section').forEach(section => {
                section.classList.remove('active');
            });

            // Show selected section
            document.getElementById(sectionId).classList.add('active');

            // Update button styles
            document.querySelectorAll('.settings-menu button').forEach(button => {
                button.style.backgroundColor = '';
                button.style.color = '';
            });

            const activeButton = document.querySelector(`.settings-menu button[onclick="showSection('${sectionId}')"]`);
            if (activeButton) {
                activeButton.style.backgroundColor = 'var(--primary-color)';
                activeButton.style.color = 'var(--text-primary-color)';
            }

            // Update URL
            const url = new URL(window.location);
            url.searchParams.set("firsttab", sectionId);
            window.history.pushState({}, "", url);
        }

        function previewImage(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById('imagePreview');
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        }

        function previewFavIcon(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById('favIconPreview');
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        }

        function openFullScreen(imgElement) {
            const modal = document.getElementById("imageModal");
            const modalImg = document.getElementById("fullImage");
            modal.style.display = "block";
            modalImg.src = imgElement.src;
        }

        function closeFullScreen() {
            document.getElementById("imageModal").style.display = "none";
        }

        function toggleBackgroundInput() {
            const type = document.getElementById("background_type").value;
            document.getElementById("background_color_group").style.display = (type === "color") ? "block" : "none";
            document.getElementById("background_image_group").style.display = (type === "image") ? "block" : "none";
            updateBackgroundValue();
        }

        function updateBackgroundValue() {
            const type = document.getElementById("background_type").value;
            const hiddenInput = document.getElementById("app_background");

            if (type === "color") {
                hiddenInput.value = document.getElementById("background_color").value;
            } else if (type === "image") {
                const fileInput = document.getElementById("background_image");
                if (fileInput.files.length > 0) {
                    hiddenInput.value = fileInput.files[0].name;
                } else {
                    hiddenInput.value = "";
                }
            }
        }

        // Real-time settings radio buttons
        $(document).ready(function() {
            $(".radio-input").change(function() {
                const selectedValue = $(this).val();
                $(".library-fields").hide();
                $(`#${selectedValue === '0' ? 'agora' : selectedValue === '1' ? 'zego' : 'pusher'}-fields`).show();
            });
        });

        // Reset buttons
        document.getElementById("resetColors")?.addEventListener("click", function() {
            const colorInputs = {
                'primary_color': "#FF9428",
                'secondary_color': "#1A1A1A",
                'text_primary_color': "#fdf8f8",
                'text_secondary_color': "#c1b9b9",
                'box_background_color': "#222222",
                'table_background_color': "#c88213"
            };

            Object.keys(colorInputs).forEach(id => {
                const input = document.getElementById(id);
                if (input) input.value = colorInputs[id];
            });

            document.getElementById('themeSettingsForm').submit();
        });

        document.getElementById("resetAppColors")?.addEventListener("click", function() {
            document.getElementById('app_primary_color').value = "#32e5ac";
            document.getElementById('second_color').value = "#003FA6";
            document.getElementById('background_type').value = "color";
            document.getElementById('background_color').value = "#32e5ac";
            document.getElementById('app_background').value = "#32e5ac";
            document.getElementById('background_color_group').style.display = 'block';
            document.getElementById('background_image_group').style.display = 'none';
            document.querySelector('#appSettings form').submit();
        });

        // Color input styling
        document.querySelectorAll('input[type="color"]').forEach(input => {
            input.addEventListener("input", function() {
                this.style.background = this.value;
            });
        });
    </script>
</body>

