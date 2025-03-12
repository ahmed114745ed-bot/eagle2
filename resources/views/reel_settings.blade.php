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
            <h2>{{ __('Settings') }}</h2>
            <div class="settings-menu">
                <button onclick="showSection('ReelSettings')"
                style="background: var(--primary-color); color: var(--text-primary-color);">{{ __('Reel Settings') }}</button>
            </div>
        </div>

        <div class="settings-content">
            <div id="ReelSettings" class="settings-section active">
                @php

                $vip=DB::table('configs')->where('name','upload_reel')->first();
               // $users=DB::table('users')->get();
    @endphp
                <h3> {{ __('Reel Settings') }}</h3>

                <form action="{{ route('admin.reel-config') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="form">
                        <label>{{__('dashboard.reelValue')}} </label>
                        <input type="text" name="number" min="1"  value="{{$vip->value ?? ''}}" class="form-control">

                        <button type="submit">{{ __('Save') }}</button>
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
