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
        background: var(--secondary-color);
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

    /* Toggle Switch Styling */
    .feature-toggle-container {
        display: flex;
        align-items: center;
        margin-bottom: 20px;
    }

    .toggle-label {
        margin-left: 10px;
    }

    .switch {
        position: relative;
        display: inline-block;
        width: 60px;
        height: 34px;
    }

    .switch input {
        opacity: 0;
        width: 0;
        height: 0;
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
    }

    .slider:before {
        position: absolute;
        content: "";
        height: 26px;
        width: 26px;
        left: 4px;
        bottom: 4px;
        background-color: white;
        transition: .4s;
    }

    input:checked + .slider {
        background-color: #2196F3;
    }

    input:focus + .slider {
        box-shadow: 0 0 1px #2196F3;
    }

    input:checked + .slider:before {
        transform: translateX(26px);
    }

    .slider.round {
        border-radius: 34px;
    }

    .slider.round:before {
        border-radius: 50%;
    }

    .feature-description-container {
        border: 1px solid #ddd;
        padding: 15px;
        border-radius: 5px;
        margin-bottom: 20px;
    }

    .external-content {
        min-height: 200px;
    }

    .loading {
        color: #888;
        font-style: italic;
    }
</style>

<body>
<div class="all-page">
    <div class="settings-sidebar">
        <h2>{{ __('Settings') }}</h2>
        <div class="settings-menu">
            <button onclick="showSection('AppFeature')"
                    style="background: var(--primary-color); color: var(--text-secondary-color);">
                {{ __('Agency Feature') }}
            </button>
            <button onclick="showSection('ReelSettings')"
                    style="text-align: right;">
                {{ __('Reel Settings') }}
            </button>
        </div>
    </div>

    <div class="settings-content">
        <div id="AppFeature" class="settings-section active">
            <h2>{{ __('Agency Feature') }}</h2>
            <form id="agencyFeatureForm" action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @php
                    $errorMessage = $errors ? $errors->first('msg') : null;
                @endphp
                @if ($errorMessage)
                    <div class="alert alert-danger text-center" style="margin-bottom: 20px;">{{ $errorMessage }}</div>
                @endif

                <div class="form">
                    <div class="feature-toggle-container">
                        <span class="toggle-label">{{ __('Enable Agency Feature') }}</span>
                        <label class="switch">
                            <input type="checkbox" id="agency_toggle" {{ $hostAgencyStatus ? 'checked' : '' }}
                            onchange="document.getElementById('host_agency_value').value = this.checked ? '1' : '0';
                                document.getElementById('agencyFeatureForm').submit();">
                            <span class="slider round"></span>
                        </label>
                        <input type="hidden" name="host_agency" id="host_agency_value" value="{{ $hostAgencyStatus ? '1' : '0' }}">
                    </div>

                    <div class="feature-description-container">
                        <h4>{{ __('Feature Description') }}</h4>
                        <div id="feature-description-content" class="external-content">
                            <div class="loading">{{ __('Loading feature description...') }}</div>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div id="ReelSettings" class="settings-section">
            <h2>{{ __('Reel Settings') }}</h2>
            <form id="reelFeatureForm" action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @php
                    $errorMessage = $errors ? $errors->first('msg') : null;
                @endphp
                @if ($errorMessage)
                    <div class="alert alert-danger text-center" style="margin-bottom: 20px;">{{ $errorMessage }}</div>
                @endif

                <div class="form">
                    <div class="feature-toggle-container">
                        <span class="toggle-label">{{ __('Enable Reel Feature') }}</span>
                        <label class="switch">
                            <input type="checkbox" id="reel_toggle" {{ $reelSettings ? 'checked' : '' }}
                            onchange="document.getElementById('host_reel_value').value = this.checked ? '1' : '0';
                                document.getElementById('reelFeatureForm').submit();">
                            <span class="slider round"></span>
                        </label>
                        <input type="hidden" name="reel_status" id="host_reel_value" value="{{ $reelSettings ? '1' : '0' }}">
                    </div>

                    <div class="feature-description-container">
                        <h4>{{ __('Feature Description') }}</h4>
                        <div id="feature-description-content" class="external-content">
                            <div class="loading">{{ __('Loading feature description...') }}</div>
                        </div>
                    </div>
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
    document.addEventListener("DOMContentLoaded", function() {
        function getQueryParam(name) {
            const urlParams = new URLSearchParams(window.location.search);
            return urlParams.get(name);
        }

        const activeTab = getQueryParam("firsttab") || "AppFeature";
        showSection(activeTab);
    });

    function showSection(sectionId) {
        document.querySelectorAll('.settings-section').forEach(section => {
            section.classList.remove('active');
        });

        document.getElementById(sectionId).classList.add('active');

        document.querySelectorAll('.settings-menu button').forEach(button => {
            button.style.backgroundColor = '';
            button.style.color = '';
        });

        const activeButton = document.querySelector(`.settings-menu button[onclick="showSection('${sectionId}')"]`);
        if (activeButton) {
            activeButton.style.backgroundColor = 'var(--primary-color)';
            activeButton.style.color = 'var(--text-secondary-color)';
        }

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

    document.addEventListener('DOMContentLoaded', function() {
        var agencyHiddenValue = document.getElementById('host_agency_value').value;
        document.getElementById('agency_toggle').checked = (agencyHiddenValue === '1');

        var reelHiddenValue = document.getElementById('host_reel_value').value;
        document.getElementById('reel_toggle').checked = (reelHiddenValue === '1');
    });
</script>
</body>
