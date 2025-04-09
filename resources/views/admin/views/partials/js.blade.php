@foreach($js as $j)
<script src="{{ admin_asset ("$j") }}">
      

</script>
@endforeach


<script src="https://cdn.jsdelivr.net/npm/svgaplayerweb@2.3.1/build/svga.min.js"
        onerror="this.onerror=null; this.src='';"></script>
 <script src="https://cdnjs.cloudflare.com/ajax/libs/viewerjs/1.10.5/viewer.min.js"></script>
 <script src="https://cdn..net/npm/svgaplayerweb@2.3.1/build/svga.min.js"></script>
 <script src="https://cdn.jsdelivr.net/npm/svgaplayerweb@2.3.1/build/svga.min.js"></script>
 <script>
    // تحقق إذا كان السكربت قد تم تحميله بنجاح
    if (typeof SVGAPlayer !== 'undefined') {
        console.log("SVGA script has been loaded successfully!");
    } else {
        console.log("Failed to load SVGA script.");
    }
</script>
<script>
    $(document).ready(function () {
        console.log('done');
        $('.view-lang').click(function (e) {
            e.preventDefault();

            var lang = $(this).data('lang');
            var value = $(this).data('value');

            $('#modalLangTitle').text(lang + " Content");
            $('#modalLangContent').text(value);

            $('#langModal').modal('show');
        });
    });


    document.addEventListener('DOMContentLoaded', function() {
                const imageUrl = @json(getImagePath($oVip->img));
                const containerId = 'imageContainer{{ $oVip->id }}';
                const container = document.getElementById(containerId);
                
                function getFileExtension(url) {
                    try {
                        const pathname = new URL(url).pathname;
                        return pathname.split('.').pop().toLowerCase();
                    } catch (e) {
                        return url.split('.').pop().toLowerCase();
                    }
                }
                
                function showImage(show_img, canvasId) {
                    const extension = getFileExtension(show_img);
                    const isSvga = extension === 'svga';
                    let content = '';
                    
                    if (isSvga) {
                        content = `<canvas id="${canvasId}" width="350" height="350" style="margin: 0 auto;"></canvas>`;
                    } else if (extension === 'mp4') {
                        content = `<video width="350" controls style="margin: 0 auto;">
                                    <source src="${show_img}" type="video/mp4">
                                    Your browser does not support the video tag.
                                </video>`;
                    } else {
                        content = `<img src="${show_img}" alt="" style="width: 350px; height: 350px; object-fit: cover; margin: 0 auto; border-radius: 50%; border: 3px solid #ff9800;"/>`;
                    }
                    
                    return { content, isSvga };
                }
                
                const canvasId = 'svgaCanvas{{ $oVip->id }}';
                const { content, isSvga } = showImage(imageUrl, canvasId);
                container.innerHTML = content;
                
                if (isSvga) {
                    // Load SVGA library dynamically if not already loaded
                    if (typeof SVGA === 'undefined') {
                        const script = document.createElement('script');
                        script.src = 'https://cdn.jsdelivr.net/npm/svgaplayerweb@2.3.1/build/svga.min.js';
                        script.onload = function() {
                            initializeSvgaPlayer();
                        };
                        document.head.appendChild(script);
                    } else {
                        initializeSvgaPlayer();
                    }
                }
                
                function initializeSvgaPlayer() {
                    const checkCanvasExist = setInterval(() => {
                        const canvas = document.getElementById(canvasId);
                        if (canvas) {
                            clearInterval(checkCanvasExist);
                            
                            try {
                                const player = new SVGA.Player('#' + canvasId);
                                const parser = new SVGA.Parser('#' + canvasId);
                                
                                player.loops = 100;
                                player.clearsAfterStop = false;
                                
                                parser.load(imageUrl, function(videoItem) {
                                    player.setVideoItem(videoItem);
                                    player.startAnimation();
                                });
                            } catch (error) {
                                console.error('SVGA Player Error:', error);
                                // Fallback to image if SVGA fails
                                container.innerHTML = `<img src="${imageUrl.replace('.svga', '.png')}" alt="" style="width: 350px; height: 350px; object-fit: cover; margin: 0 auto; border-radius: 50%; border: 3px solid #ff9800;"/>`;
                            }
                        }
                    }, 100);
                }
            });
</script>
