<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Download Live App</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .store-btn {
            @apply flex items-center justify-center gap-3 w-full bg-gray-900 hover:bg-gray-800 text-white py-3 px-4 rounded-xl font-semibold shadow-lg transition duration-200;
        }
        .store-icon {
            width: 24px;
            height: 24px;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-blue-100 to-white flex items-center justify-center min-h-screen">
<div class="max-w-md w-full mx-auto bg-white p-6 rounded-2xl shadow-2xl text-center space-y-6">
    <h1 class="text-3xl font-bold text-gray-800">Download Our Live App</h1>
    <p class="text-gray-500">Join us and enjoy live streaming like never before!</p>

    <div id="download-buttons" class="space-y-4">
        <!-- Dynamic download buttons -->
    </div>
</div>

<script>
    const androidLink = "{{ $androidLink }}";
    const iosLink = "{{ $iosLink }}";
    const huaweiLink = "{{ $huaweiLink }}";

    function isAndroid() {
        return /Android/i.test(navigator.userAgent);
    }

    function isIOS() {
        return /iPhone|iPad|iPod/i.test(navigator.userAgent);
    }

    function isHuawei() {
        return /Huawei|HONOR|HMSCore/i.test(navigator.userAgent);
    }

    function showButtons() {
        const container = document.getElementById('download-buttons');
        let buttons = '';

        const playBtn = `
                <a href="${androidLink}" class="store-btn">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/7/78/Google_Play_Store_badge_EN.svg" alt="Google Play" class="store-icon" />
                    Google Play
                </a>
            `;

        const appleBtn = `
                <a href="${iosLink}" class="store-btn">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/9/96/Apple_logo_black.svg" alt="App Store" class="store-icon" />
                    Apple Store
                </a>
            `;

        const huaweiBtn = `
                <a href="${huaweiLink}" class="store-btn">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/9/9e/Huawei_AppGallery_logo.svg" alt="AppGallery" class="store-icon" />
                    Huawei AppGallery
                </a>
            `;

        if (isAndroid()) {
            buttons += playBtn;
        } else if (isIOS()) {
            buttons += appleBtn;
        } else if (isHuawei()) {
            buttons += huaweiBtn;
        } else {
            // Desktop: Show all
            buttons += playBtn + appleBtn + huaweiBtn;
        }

        container.innerHTML = buttons;
    }

    showButtons();
</script>
</body>
</html>
