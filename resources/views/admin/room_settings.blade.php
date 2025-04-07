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
</style>
</head>

<body>
    <div class="all-page">
        <div class="settings-sidebar">
            <h2>إعدادات</h2>
            <div class="settings-menu">
                <button
                    onclick="showSection('custom_background_settings')">{{ __('Custom Background settings') }}</button>
                <button onclick="showSection('additional_settings')">{{ __('Additional settings') }}</button>
            </div>
        </div>

        <div class="settings-content">
            <div id="custom_background_settings" class="settings-section active">

                <h3> {{ __('Custom Background settings') }}</h3>

                <form action="{{ route('admin.room-settings.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="form">
                        <label>{{ __('Cost request background:') }} </label>
                        <input type="text" name="cost_request_background"
                            value="{{ $settings['cost_request_background'] ?? '' }}" class="form-control">

                        <label>{{ __('Background expiration in days:') }} </label>
                        <input type="text" name="background_expiration"
                            value="{{ $settings['background_expiration'] ?? '' }}" class="form-control">

                        <button type="submit">{{ __('save') }}</button>
                    </div>

                </form>
            </div>

            <div id="additional_settings" class="settings-section">
                <h3>{{ __('Additional settings') }}</h3>
                <form action="{{ route('admin.room-settings.store') }}" method="POST">
                    <div class="form">
                        @csrf

                        <label>{{ __('Room Rule:') }}</label>
                        <input class="form-control" type="text" name="room_rule"
                            value="{{ $settings['room_rule'] ?? '' }}">

                        <label>{{ __('Room Rule en:') }}</label>
                        <input class="form-control" type="text" name="room_rule_en"
                            value="{{ $settings['room_rule_en'] ?? '' }}">


                        <label>{{ __('Youtube Key:') }}</label>
                        <input class="form-control" type="text" name="youtube_key"
                            value="{{ $settings['youtube_key'] ?? '' }}">


                        <label>{{ __('Pk Background:') }}</label>
                        <input class="form-control" type="text" name="pk_background"
                            value="{{ $settings['pk_background'] ?? '' }}">


                        <label>{{ __('Private Comment Price:') }}</label>
                        <input class="form-control" type="text" name="private_comment_price"
                            value="{{ $settings['private_comment_price'] ?? '' }}">

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
            showSection('custom_background_settings');
            document.addEventListener("DOMContentLoaded", function() {
                // Function to get query parameter by name
                function getQueryParam(name) {
                    const urlParams = new URLSearchParams(window.location.search);
                    return urlParams.get(name);
                }

                // Get the 'firsttab' parameter from URL or default to 'brandSettings'
                const activeTab = getQueryParam("firsttab") || "custom_background_settings";

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
        </script>
    </div>
</body>
