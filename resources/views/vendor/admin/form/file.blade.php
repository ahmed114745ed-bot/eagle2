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

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const input = document.querySelector('input[name="img2"]');
        const previewContainer = document.createElement("div");
        input.parentNode.appendChild(previewContainer);

        input.addEventListener("change", function(e) {
            const file = e.target.files[0];
            if (!file) return;

            const ext = file.name.split('.').pop().toLowerCase();
            const reader = new FileReader();

            reader.onload = function(e) {
                if (['png','jpg','jpeg','gif','webp','svg'].includes(ext)) {
                    previewContainer.innerHTML = `<img src="${e.target.result}" style="max-height:150px" class="img img-thumbnail" />`;
                } else {
                    // here you could call handleShowImageWithTypes but you'd need a JS version
                    previewContainer.innerHTML = `<span class="label label-info">${file.name}</span>`;
                }
            };

            reader.readAsDataURL(file);
        });
    });
</script>
