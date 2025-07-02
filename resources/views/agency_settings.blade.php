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
        color: var(--primary-color);
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
        background: var(--primary-color);
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

    .form {
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

    img {
        width: 201px;
        display: block;
        height: 99px;
        margin-bottom: 20px;
    }

    button {
        width: 200px;

    }

    /* Tab styling */
    .tab-buttons {
        display: flex;
        border-bottom: 1px solid #444;
        margin-bottom: 20px;
    }

    .tab-button {
        padding: 10px 20px;
        background: #333;
        border: none;
        color: white;
        cursor: pointer;
        margin-right: 5px;
        border-radius: 5px 5px 0 0;
    }

    .tab-button:hover {
        background: #555;
    }

    .tab-button.active {
        background: #ff9800;
        color: #121212;
    }

    .tab-content {
        display: none;
        padding: 20px;
        background: #222;
        border-radius: 0 5px 5px 5px;
    }

    .tab-content.active {
        display: block;
    }

    .tab-content form {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 15px;
    }

    .tab-content label {
        grid-column: 1;
    }

    .tab-content input[type="file"] {
        grid-column: 2;
    }

    .tab-content button {
        grid-column: 1 / span 2;
        justify-self: center;
    }



    /* Badge Upload Section Specific Styles */
    .badge-upload-container {
        display: grid;
        width: 200%;
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
        margin-bottom: 20px;
    }

    .badge-upload-item {
        display: flex;
        flex-direction: column;
        gap: 8px;
        padding: 15px;
        background: #333;
        border-radius: 5px;
        min-height: 200px;
        /* Ensures consistent height */
    }

    .badge-upload-item label {
        font-weight: bold;
        color: #ff9800;
        margin-bottom: 5px;
    }

    .badge-upload-item input[type="file"] {
        padding: 8px;
        background: #444;
        border: 1px solid #555;
        color: white;
        width: 100%;
    }

    .badge-preview {
        margin-top: 10px;
        text-align: center;
        flex-grow: 1;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .badge-preview img {
        max-width: 100%;
        max-height: 100px;
        cursor: pointer;
        border: 2px solid #555;
        transition: transform 0.3s;
    }

    .badge-preview img:hover {
        transform: scale(1.05);
        border-color: #ff9800;
    }

    .upload-button {
        display: block;
        width: auto;
        margin: 20px auto 0;
        padding: 10px 30px;
        background: #ff9800;
        color: #121212;
        font-weight: bold;
        border-radius: 5px;
        transition: background 0.3s;
        grid-column: 1 / -1;
        /* Span full width */
    }

    .upload-button:hover {
        background: #ffab40;
    }
    .swal-wide {
        width: 900px !important;
        font-size: 25px;
    }

    /* Responsive adjustments */
    @media (max-width: 2000px) {
        .badge-upload-container {
            grid-template-columns: 1fr;
        }
    }

    .switch-container {
        display: flex;
        flex-wrap: wrap;
        justify-content: flex-start;
        margin-top: 16px;
    }

    .switch-item {
        display: flex;
        align-items: center;
        margin-bottom: 15px;
        margin-right: 20px;
    }

    .switch-label {
        margin-left: 10px;
        font-weight: 500;
    }

    .switch {
        position: relative;
        display: inline-block;
        width: 50px;
        height: 24px;
    }

    .switch input {
        display: none;
    }

    .slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #ccc;
        transition: .4s;
        border-radius: 34px;
    }

    .slider:before {
        position: absolute;
        content: "";
        height: 18px;
        width: 18px;
        left: 3px;
        bottom: 3px;
        background-color: white;
        transition: .4s;
        border-radius: 50%;
    }

    input:checked + .slider {
        background-color: #00e6c3;
    }

    input:checked + .slider:before {
        transform: translateX(26px);
    }

    .grid-per-pager .input, select{
        width: auto !important;
    }

    .exportPdfForm {
        background: transparent !important;
    }

    .modal-body input,
    .modal-body select {
        width: auto !important;
    }

    .close {
        right: 0 !important;
        top: 0 !important;
        font-size: 30px !important;
    }

    button.close {
        width: auto !important;
    }

    .btn-sm {
        margin: 0 !important;
    }
</style>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</head>

<body>
    <div class="all-page">
        <div class="settings-sidebar">
            <h2>{{ __('Settings') }}</h2>
            <div class="settings-menu">
                <button onclick="showSection('PercentageTarget')"
                    style="background: var(--primary-color); color: var(--text-secondary-color);">{{ __('Percentage target') }}</button>
                <button onclick="showSection('Badges')">{{ __('Badges') }}</button>
                <button onclick="showSection('user_days')">{{ __('user days') }}</button>
                <button onclick="showSection('agency_settings')">{{ __('Agency Settings') }}</button>

                <button onclick="showSection('targets_table')">{{ __('Targets') }}</button>
            </div>
        </div>

        <div class="settings-content">
            <div id="PercentageTarget" class="settings-section active">

                <h3> {{ __('Percentage target') }}</h3>

                <form id="target-percentage-form" action="{{ route('admin.target-percentage') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @php
                        $errorMessage = $errors ? $errors->first('msg') : null;
                    @endphp
                    @if ($errorMessage)
                        <div class="alert alert-danger text-center" style="margin-bottom: 20px;"> {{ $errorMessage }}
                        </div>;
                    @endif
                    <div class="form">
                        <label>{{ __('Hours:') }} </label>
                        <input type="text" name="hours" value="{{ $hours }}" class="form-control">
                        <label>{{ __('Days:') }} </label>
                        <input type="text" name="days" value="{{ $days }}" class="form-control">
                        <label>{{ __('Moments:') }} </label>
                        <input type="text" name="moments" value="{{ $moments }}" class="form-control">
                        <label>{{ __('Reels:') }} </label>
                        <input type="text" name="reels" value="{{ $reels }}" class="form-control">
                        <label>{{ __('Diamonds') }} </label>
                        <input type="text" name="diamonds" value="{{ $diamonds }}" class="form-control">
                        <button type="submit">{{ __('Save') }}</button>
                    </div>

                </form>
            </div>

            <div id="user_days" class="settings-section ">

            <!-- <h3> {{ __('Percentage target') }}</h3> -->

            <form id="target-percentage-form" action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @php
                    $errorMessage = $errors ? $errors->first('msg') : null;
                @endphp
                @if ($errorMessage)
                     <div class="alert alert-danger text-center" style="margin-bottom: 20px;"> {{ $errorMessage }}
                    </div>;
                @endif
                <div class="form">
                    <label>{{ __('Hours:') }} </label>
                    <input type="number" name="hours_days" value="{{ Cache::get('hours_days') }}" class="form-control">

                    <button type="submit">{{ __('Save') }}</button>

                </div>

            </form>
            </div>

            <div id="Badges" class="settings-section">
                <h3>{{ __('Badges') }}</h3>

                <!-- Language Tabs Navigation -->
                <div class="tab-buttons">
                    {{-- <button class="tab-button active" onclick="openLanguageTab(event, 'defaultInput')">
                        Default <div>
                            English
                        </div>
                    </button> --}}
                    @foreach ($languages as $index => $language)
                        <button class="tab-button" onclick="openLanguageTab(event, '{{ $language->code }}')">
                            {{ $language->name }}
                            @if ($language->name == 'English')
                               <small>(Default)</small>
                            @endif
                        </button>
                    @endforeach
                </div>

                <div id="defaultInput" class="tab-content">
                    <form action="{{ route('admin.upload.badges') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="language" value="defaultInput">

                        <div class="badge-upload-container">
                            @foreach (['shipping', 'host', 'agency_owner'] as $type)
                                <div class="badge-upload-item">
                                    <label for="default_{{ $type }}">
                                        @if ($type == 'host')
                                            {{ __('Hosting') }} Default:
                                        @else
                                            {{ __(ucfirst($type)) }} Default:
                                        @endif
                                    </label>
                                    <input type="file" id="default_{{ $type }}"
                                        name="default_{{ $type }}"
                                        onchange="previewImage(this, 'preview_default_{{ $type }}')">

                                    <div class="badge-preview">
                                        @php
                                            $row = $configAll->where('name', 'default' . '_' . $type)->first();
                                        @endphp
                                        @if ($row)
                                            <img id="preview_default_{{ $type }}"
                                                src="{{ getImagePath($row?->value) }}" alt="{{ $type }} badge"
                                                onclick="openFullScreen(this)">
                                        @else
                                            <img id="preview_default_{{ $type }}" src=""
                                                alt="No image uploaded" style="display: none;">
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <button type="submit" class="upload-button">
                            {{ __('Submit') }}
                        </button>
                    </form>
                </div>

                <!-- Language Tab Contents -->
                @foreach ($languages as $index => $language)
                    <div id="{{ $language->code }}" class="tab-content">
                        <form action="{{ route('admin.upload.badges') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="language" value="{{ $language->code }}">

                            <div class="badge-upload-container">
                                @foreach (['shipping', 'host', 'agency_owner', 'bd'] as $type)
                                    <div class="badge-upload-item" style="padding: 12px; border: 1px solid #444; margin-bottom: 20px; background: #333; color: #ffa500;">
                                        <label>
                                            @if ($type == 'host')
                                                {{ __('Hosting') }} ({{ strtoupper($language->code) }}):
                                            @else
                                                {{ __(ucfirst($type)) }} ({{ strtoupper($language->code) }}):
                                            @endif
                                        </label>

                                        @php
                                            $suffixes = ['badge', 'intro', 'frame'];
                                        @endphp

                                        <div style="display: flex; gap: 20px; flex-wrap: wrap; margin-top: 10px;">
                                            @foreach ($suffixes as $suffix)
                                                @php
                                                if ($suffix == 'badge'){
                                                    $inputName = $language->code . '_' . $type;
                                                }else{
                                                    $inputName = $language->code . '_' . $type . '_' . $suffix;
                                                }
                                                    $row = $configAll->where('name', $inputName)->first();
                                                @endphp

                                                <div>
                                                    <label> {{ __($suffix) }} </label>
                                                    <input type="file"
                                                           id="{{ $inputName }}"
                                                           name="{{ $inputName }}"
                                                           onchange="previewImage(this, 'preview_{{ $inputName }}')"
                                                           style="display: block; width: 100%; max-width: 200px;">

                                                    <div class="badge-preview" style="margin-top: 5px;">
                                                        @if ($row)
                                                            <img id="preview_{{ $inputName }}"
                                                                 src="{{ getImagePath($row?->value) }}"
                                                                 alt="{{ $type }} {{ $suffix }}"
                                                                 style="max-height: 50px; cursor: pointer;"
                                                                 onclick="openFullScreen(this)">
                                                        @else
                                                            <img id="preview_{{ $inputName }}"
                                                                 src=""
                                                                 alt="No image uploaded"
                                                                 style="display: none; max-height: 50px;">
                                                        @endif
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <button type="submit" class="upload-button">
                                {{ __('Submit') }}
                            </button>
                        </form>
                    </div>
                @endforeach
            </div>

            <div id="agency_settings" class="settings-section ">

                <form id="agency_settings-form">
                    <div class="switch-container mt-4">
                        <div class="switch-item">
                            <label for="stopCharge" class="switch-label">{{ __('dashboard.frazeCharge') }}</label>
                            <label class="switch">
                                <input type="checkbox" id="stopCharge" {{ $stop_charge == 1 ? 'checked' : '' }}>
                                <span class="slider round"></span>
                            </label>
                        </div>
                    </div>
                    <div class="switch-container mt-4">
                        <div class="switch-item">
                            <label for="stopInviteCode" class="switch-label">{{ __("dashboard.closeCose") }}</label>
                            <label class="switch">
                                <input type="checkbox" id="stopInviteCode" {{ $stop_invite_code == 1 ? 'checked' : '' }}>
                                <span class="slider round"></span>
                            </label>
                        </div>
                    </div>
                    <div class="switch-container mt-4">
                        <div class="switch-item">
                            <label for="stopTransferSalary" class="switch-label">{{ __("dashboard.transSalary") }}</label>
                            <label class="switch">
                                <input type="checkbox" id="stopTransferSalary" {{ $transfer_salary == 1 ? 'checked' : '' }}>
                                <span class="slider round"></span>
                            </label>
                        </div>
                    </div>
                    <div class="switch-container mt-4">
                        <div class="switch-item">
                            <label for="stopGiftCheckbox" class="switch-label">ايقاف ارسال الهدايا للجميع</label>
                            <label class="switch">
                                <input type="checkbox" id="stopGiftCheckbox" {{ $make_gift_top == 1 ? 'checked' : '' }}>
                                <span class="slider round"></span>
                            </label>
                        </div>
                    </div>
                </form>

            </div>

            <div id="targets_table" class="settings-section">
                <h3>{{ __('Targets table') }}</h3>

                {!! $targetGrid !!}
            </div>

        <div id="imageModal" class="modal" onclick="closeFullScreen()">
            <span class="close">&times;</span>
            <img class="modal-content" id="fullImage">
        </div>




        <script>
            function previewImage(input, previewId) {
                const preview = document.getElementById(previewId);
                const file = input.files[0];

                if (file) {
                    const reader = new FileReader();

                    reader.onload = function(e) {
                        preview.src = e.target.result;
                        preview.style.display = 'block';
                    }

                    reader.readAsDataURL(file);
                }
            }

            function openFullScreen(imgElement) {
                var modal = document.getElementById("imageModal");
                var modalImg = document.getElementById("fullImage");

                modal.style.display = "block";
                modalImg.src = imgElement.src;
            }

            function openLanguageTab(evt, languageCode) {
                // Hide all tab contents
                document.querySelectorAll('.tab-content').forEach(content => {
                    content.classList.remove('active');
                });

                // Remove active class from all buttons
                document.querySelectorAll('.tab-button').forEach(button => {
                    button.classList.remove('active');
                });

                // Show the current tab and mark button as active
                document.getElementById(languageCode).classList.add('active');
                evt.currentTarget.classList.add('active');
            }

        </script>

        <!-- كود JavaScript -->
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                // Function to get query parameter by name
                function getQueryParam(name) {
                    const urlParams = new URLSearchParams(window.location.search);
                    return urlParams.get(name);
                }

                // Get the 'firsttab' parameter from URL or default to 'brandSettings'
                const activeTab = getQueryParam("firsttab") || "PercentageTarget";

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


                if (sectionId === 'Badges') {
                    const EnglishTabBtn = document.querySelector('.tab-button[onclick*="en"]');
                    if (EnglishTabBtn) {
                        EnglishTabBtn.click(); // fire real click event
                    }
                }
                // if (sectionId === 'user_days') {
                //     const EnglishTabBtn = document.querySelector('.tab-button[onclick*="en"]');
                //     if (EnglishTabBtn) {
                //         EnglishTabBtn.click();
                //     }
                // }

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

            document.getElementById('target-percentage-form').addEventListener('submit', function(e) {
                    e.preventDefault(); // إيقاف الإرسال مؤقتًا

                    const hours = parseFloat(document.querySelector('input[name="hours"]').value) || 0;
                    const days = parseFloat(document.querySelector('input[name="days"]').value) || 0;
                    const moments = parseFloat(document.querySelector('input[name="moments"]').value) || 0;
                    const reels = parseFloat(document.querySelector('input[name="reels"]').value) || 0;
                    const diamonds = parseFloat(document.querySelector('input[name="diamonds"]').value) || 0;

                    const total = hours + days + moments + reels + diamonds;

                    if (total !== 100) {
                        Swal.fire({
                            icon: 'error',
                            title: 'تحذير',
                            text: "{{ __('total_percentage_must_be_100') }}",
                            confirmButtonText: 'حسنًا'
                        });
                        return;
                    }

                    e.target.submit();
                });

            $(document).on('change', '#stopCharge,#stopInviteCode,#stopTransferSalary,#stopGiftCheckbox', function () {

                const id        = this.id;
                const isChecked = $(this).is(':checked');

                const map = {
                    stopCharge:         ['/admin/send-request-stop-charge',  'stop_charge'],
                    stopInviteCode:     ['/admin/send-request-invite-code',        'stop_invite_code'],
                    stopTransferSalary: ['/admin/send-request-transfer-salary','transfer_salary'],
                    stopGiftCheckbox:   ['/admin/close-open-gift',           'make_rooms_top'],
                };

                const [url, key] = map[id];

                $.ajax({
                    url,
                    type: 'POST',
                    data: { [key]: isChecked },
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
                })
                    .done(()   => toastr.success('Saved'))
                    .fail(err => toastr.error('Error'));
            });

        </script>
    </div>
</body>
