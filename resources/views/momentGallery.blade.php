<head>
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
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/viewerjs/1.10.5/viewer.min.css" />
</head>

{{-- <body>
    <div class="all-page">
        <div class="settings-content">
            <div>
                @php
                        $url = url('admin/moments');
                    @endphp
                <a href="{{ $url }}" 
                        style="display: block; text-align: center; margin-top: 10px; padding: 8px 15px; background-color: #007bff; color: white; border-radius: 5px; text-decoration: none;">
                            {{__('back')}}
                        </a>

                </div>        
            <div id="image-gallery" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 15px; padding: 20px;">
                @foreach($galleries as $image)
                    @php
                        $imgUrl = getDriverUrl() . '/' . $image->image;
                    @endphp
                    <div style="overflow: hidden; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); transition: transform 0.3s ease;">
                        <img src="{{ $imgUrl }}" 
                             style="width: 100%; height: 200px; object-fit: cover; cursor: pointer; transition: transform 0.3s ease;"
                             data-original="{{ $imgUrl }}"
                            
                             loading="lazy">
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Viewer.js CSS -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/viewerjs/1.10.5/viewer.min.css" />
        
        <!-- Viewer.js JavaScript -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/viewerjs/1.10.5/viewer.min.js"></script>
        
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const gallery = document.getElementById('image-gallery');
                const viewer = new Viewer(gallery, {
                    inline: false,
                    button: true,
                    navbar: true,
                    title: false,
                    toolbar: {
                        zoomIn: true,
                        zoomOut: true,
                        oneToOne: true,
                        reset: true,
                        prev: true,
                        play: true,
                        next: true,
                        rotateLeft: true,
                        rotateRight: true,
                        flipHorizontal: true,
                        flipVertical: true,
                    },
                    viewed() {
                        viewer.toolbar.querySelector('.viewer-play').click();
                    },
                    transition: false,
                });
                
                // Optional: Add hover effect
                const images = gallery.querySelectorAll('img');
                images.forEach(img => {
                    img.parentElement.addEventListener('mouseenter', () => {
                        img.parentElement.style.transform = 'scale(1.03)';
                    });
                    img.parentElement.addEventListener('mouseleave', () => {
                        img.parentElement.style.transform = 'scale(1)';
                    });
                });
            });
        </script>
    </div>
</body> --}}

<body>
    <div class="all-page">
        <div class="settings-content">
            <div>
                @php
                    $url = url('admin/moments');
                @endphp
                <a href="{{ $url }}" 
                    style="display: block; text-align: center; margin-top: 10px; padding: 8px 15px; background-color: #007bff; color: white; border-radius: 5px; text-decoration: none;">
                    {{__('back')}}
                </a>
            </div>        
            <div id="image-gallery" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 15px; padding: 20px;">
                @foreach($galleries as $image)
                    @php
                        $imgUrl = getDriverUrl() . '/' . $image->image;
                    @endphp
                    <div style="overflow: hidden; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); transition: transform 0.3s ease;">
                        <img src="{{ $imgUrl }}" 
                             style="width: 100%; height: 200px; object-fit: cover; cursor: pointer; transition: transform 0.3s ease;"
                             data-original="{{ $imgUrl }}"  
                             loading="lazy">
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Viewer.js CSS -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/viewerjs/1.10.5/viewer.min.css" />

        <!-- Viewer.js JavaScript -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/viewerjs/1.10.5/viewer.min.js"></script>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const gallery = document.getElementById('image-gallery');
                if (gallery) {
                    const viewer = new Viewer(document.getElementById('image-gallery'), {
                        toolbar: {
                            zoomIn: 1,
                            zoomOut: 1,
                            oneToOne: 1,
                            reset: 1,
                            prev: 1,
                            play: { show: 1, size: 'large' },
                            next: 1,
                            rotateLeft: 1,
                            rotateRight: 1,
                            flipHorizontal: 1,
                            flipVertical: 1,
                        }
                    });
                }

                // Optional: Add hover effect
                const images = gallery ? gallery.querySelectorAll('img') : [];
                images.forEach(img => {
                    img.parentElement.addEventListener('mouseenter', () => {
                        img.parentElement.style.transform = 'scale(1.03)';
                    });
                    img.parentElement.addEventListener('mouseleave', () => {
                        img.parentElement.style.transform = 'scale(1)';
                    });
                });
            });
        </script>
    </div>
</body>
