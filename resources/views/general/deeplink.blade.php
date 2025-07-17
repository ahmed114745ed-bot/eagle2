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

        .store-btn {
    display: inline-block;
    margin: 10px;
    text-decoration: none;
}

.btn-content {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 15px;
    border: 1px solid #ddd;
    border-radius: 8px;
    background-color: #f8f8f8;
    transition: background-color 0.2s;
}

.btn-content:hover {
    background-color: #e8e8e8;
}

.store-icon {
    width: 24px;
    height: 24px;
    object-fit: contain;
}

    </style>
</head>
<body class="bg-gradient-to-br from-blue-100 to-white flex items-center justify-center min-h-screen">
<div class="max-w-md w-full mx-auto bg-white p-6 rounded-2xl shadow-2xl text-center space-y-6">
<h1 class="text-3xl font-bold text-gray-700">{{ __('Download Our App') }}{{' '}} {{$appName}}</h1>
    <p class="text-gray-500">{{_('Join us and enjoy live streaming like never before!')}}</p>

    <div id="download-buttons" class="space-y-4">
        <!-- Dynamic download buttons -->
    </div>
</div>

<script>
    const androidLink = "{{ $androidLink }}";
    const iosLink = "{{ $iosLink }}";
    const huaweiLink = "{{ $huaweiLink }}";
   
    const androidLogo = "{{ asset('images/android_logo_PNG27.png') }}";
    const appleLogo = "{{ asset('images/Apple-IOS-jpg.png') }}";
    const huaweiLogo = "{{ asset('images/huawel.jpg') }}";


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
        <div class="btn-content">
            <img src="${androidLogo}" alt="Google Play" class="store-icon" />
            <span>Google Play</span>
        </div>
    </a>
`;

const appleBtn = `
    <a href="${iosLink}" class="store-btn">
        <div class="btn-content">
            <img src="${appleLogo}" alt="App Store" class="store-icon" />
            <span>Apple Store</span>
        </div>
    </a>
`;

const huaweiBtn = `
    <a href="${huaweiLink}" class="store-btn">
        <div class="btn-content">
            <img src="${huaweiLogo}" alt="AppGallery" class="store-icon" />
            <span>Huawei AppGallery</span>
        </div>
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
