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
    document.getElementById("file-input-img2").addEventListener("change", function(e) {
        const file = e.target.files[0];
        if (!file) return;

        const ext = file.name.split(".").pop().toLowerCase();
        if (ext === "svga") {
            const reader = new FileReader();
            reader.onload = function(ev) {
                const container = document.getElementById("preview-container");
                container.innerHTML = `<canvas id="svga-canvas" width="200" height="200"></canvas>`;

                const player = new SVGA.Player('#svga-canvas');
                const parser = new SVGA.Parser('#svga-canvas');

                parser.load(ev.target.result, function(videoItem) {
                    player.setVideoItem(videoItem);
                    player.startAnimation();
                });
            };
            reader.readAsArrayBuffer(file); // ✅ important for SVGA
        } else if (["png","jpg","jpeg","gif","webp","svg"].includes(ext)) {
            const reader = new FileReader();
            reader.onload = function(ev) {
                document.getElementById("preview-container").innerHTML =
                    `<img src="${ev.target.result}" class="img img-thumbnail" style="max-height:150px;" />`;
            };
            reader.readAsDataURL(file);
        } else {
            document.getElementById("preview-container").innerHTML =
                `<div style="padding:10px;border:1px solid #ccc;display:inline-block;">
                <strong>Selected file:</strong> ${file.name}
             </div>`;
        }
    });
</script>
