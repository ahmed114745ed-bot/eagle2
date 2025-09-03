<div class="{{$viewClass['form-group']}} {!! !$errors->has($errorKey) ? '' : 'has-error' !!}">
    <label for="{{$id}}" class="{{$viewClass['label']}} control-label">{{$label}}</label>

    <div class="{{$viewClass['field']}}">

        @include('admin::form.error')

        <div id="preview-{{ $id }}" style="margin-bottom:10px;">
            @if($value)
                @php
                    $url = \Illuminate\Support\Facades\Storage::disk(config('admin.upload.disk'))->url($value);
                    $ext = strtolower(pathinfo($url, PATHINFO_EXTENSION));
                    $uniqueId = 'file_' . uniqid();
                @endphp

                @if(in_array($ext, ['png','jpg','jpeg','gif','webp','svg']))
                    <img src="{{ $url }}" class="img img-thumbnail" style="max-height:150px">
                @else
                    {!! handleShowImageWithTypes($uniqueId, $url, 100, 100, 10) !!}
                @endif
            @endif
        </div>

        <input type="file" id="input-{{ $id }}" class="{{$class}}" name="{{$name}}" {!! $attributes !!} />

        @include('admin::form.help-block')
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        let input = document.getElementById("input-{{ $id }}");
        let preview = document.getElementById("preview-{{ $id }}");

        input.addEventListener("change", function(event) {
            let file = event.target.files[0];
            if (!file) return;

            let ext = file.name.split('.').pop().toLowerCase();
            preview.innerHTML = '';

            if (["png","jpg","jpeg","gif","webp","svg"].includes(ext)) {
                let url = URL.createObjectURL(file);
                preview.innerHTML = `<img src="${url}" class="img img-thumbnail" style="max-height:150px">`;
            }
            else if (ext === "mp4") {
                let url = URL.createObjectURL(file);
                preview.innerHTML = `
                <video width="100" height="100" controls autoplay loop muted>
                   <source src="${url}" type="video/mp4">
                </video>`;
            }
            else if (ext === "svga" || ext === "zz") {
                let url = URL.createObjectURL(file);
                let uniqueId = "svga_" + Date.now();
                preview.innerHTML = `<div id="${uniqueId}" style="width:100px;height:100px;"></div>`;

                // Init SVGA player
                let player = new SVGA.Player("#" + uniqueId);
                let parser = new SVGA.Parser("#" + uniqueId);
                parser.load(url, function(videoItem) {
                    player.setVideoItem(videoItem);
                    player.startAnimation();
                });
            }
        });
    });
</script>
