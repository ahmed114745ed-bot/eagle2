<div class="{{$viewClass['form-group']}} {!! !$errors->has($errorKey) ? '' : 'has-error' !!}">
    <label for="{{$id}}" class="{{$viewClass['label']}} control-label">{{$label}}</label>
    <div class="{{$viewClass['field']}}">

        @include('admin::form.error')

        <input type="file"
               id="file-input-{{ $id }}"
               class="{{$class}}"
               name="{{$name}}"
            {!! $attributes !!}>

        {{-- Our custom preview container --}}
        <div id="preview-{{ $id }}" style="margin-top:10px;">
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

        @include('admin::form.help-block')
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const input = document.getElementById('file-input-{{ $id }}');
        input.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (!file) return;

            const ext = file.name.split('.').pop().toLowerCase();
            const preview = document.getElementById('preview-{{ $id }}');
            preview.innerHTML = ''; // reset

            if (['png','jpg','jpeg','gif','webp','svg'].includes(ext)) {
                const url = URL.createObjectURL(file);
                preview.innerHTML = `<img src="${url}" class="img img-thumbnail" style="max-height:150px">`;
            } else if (ext === 'svga' || ext === 'zz' || ext === 'mp4') {
                // either directly inline or call your PHP helper equivalent in JS
                preview.innerHTML = `<div id="svga_{{ $id }}" style="width:100px;height:100px;"></div>`;

                // Init SVGA player dynamically
                var player = new SVGA.Player('#svga_{{ $id }}');
                var parser = new SVGA.Parser('#svga_{{ $id }}');
                parser.load(URL.createObjectURL(file), function(videoItem) {
                    player.setVideoItem(videoItem);
                    player.startAnimation();
                });
            }
        });
    });
</script>
