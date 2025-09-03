<div class="{{$viewClass['form-group']}} {!! !$errors->has($errorKey) ? '' : 'has-error' !!}">

    <label for="{{$id}}" class="{{$viewClass['label']}} control-label">{{$label}}</label>

    <div class="{{$viewClass['field']}}">

        @include('admin::form.error')

        @if($value)
            @php
                $url = \Illuminate\Support\Facades\Storage::disk(config('admin.upload.disk'))->url($value);
                $ext = strtolower(pathinfo($url, PATHINFO_EXTENSION));
                $uniqueId = 'file_' . uniqid();
            @endphp

            @if(!in_array($ext, ['png','jpg','jpeg','gif','webp','svg']))
                <div style="margin-bottom:10px;">
                    {!! handleShowImageWithTypes($uniqueId, $url, 100, 100, 10) !!}
                </div>
            @endif
        @endif

        <input type="file" class="{{$class}}" name="{{$name}}" {!! $attributes !!} />

        @include('admin::form.help-block')

    </div>
</div>

<div id="preview-container"></div>

<script src="https://cdn.jsdelivr.net/npm/svgaplayerweb@2.3.2/build/svga.min.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const input = document.querySelector("input[name='img2']");  // ✅ select by name
        if (!input) return; // avoid null error

        const previewContainer = document.getElementById("preview-container");

        input.addEventListener("change", function(e) {
            const file = e.target.files[0];
            if (!file) return;

            let ext = "";
            if (file.name.indexOf(".") !== -1) {
                ext = file.name.split(".").pop().toLowerCase();
            }

            if (["png","jpg","jpeg","gif","webp","svg"].includes(ext)) {
                const reader = new FileReader();
                reader.onload = function(ev) {
                    previewContainer.innerHTML = `<img src="${ev.target.result}" class="img img-thumbnail" style="max-height:150px;" />`;
                };
                reader.readAsDataURL(file);
            } else if (ext === "svga") {
                const reader = new FileReader();
                reader.onload = function(ev) {
                    previewContainer.innerHTML = `<canvas id="svga-canvas" width="200" height="200"></canvas>`;
                    const player = new SVGA.Player('#svga-canvas');
                    const parser = new SVGA.Parser('#svga-canvas');
                    const uint8 = new Uint8Array(ev.target.result);
                    parser.load(uint8, function(videoItem) {
                        player.setVideoItem(videoItem);
                        player.startAnimation();
                    });
                };
                reader.readAsArrayBuffer(file);
            } else {
                previewContainer.innerHTML = `<div style="padding:10px;border:1px solid #ccc;display:inline-block;">
                <strong>Selected file:</strong> ${file.name}
            </div>`;
            }
        });
    });
</script>
