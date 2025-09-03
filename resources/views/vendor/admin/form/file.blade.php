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

            <div style="margin-bottom:10px;">
                @if(in_array($ext, ['png','jpg','jpeg','gif','webp','svg']))
                    <img src="{{ $url }}" class="img img-thumbnail" style="max-height:150px">
                @else
                    {!! handleShowImageWithTypes($uniqueId, $url, 100, 100, 10) !!}
                @endif
            </div>
        @endif

        <input type="file" class="{{$class}}" name="{{$name}}" {!! $attributes !!} />

        @include('admin::form.help-block')

    </div>
</div>

<script>
    $('.file').fileinput({
        allowedPreviewTypes: ['image', 'html', 'text', 'video', 'audio', 'flash', 'object'],
        previewFileExtSettings: {
            'svga': function(ext) {
                return ext.match(/(svga)$/i);
            }
        },
        previewFileIcon: '',
        previewContentTemplates: {
            svga: '<div class="file-preview-frame">' +
                '<div class="kv-file-content">' +
                '{data}' +
                '</div>' +
                '<div class="file-thumbnail-footer">' +
                '<div class="file-footer-caption">{caption}</div>' +
                '</div>' +
                '</div>'
        }
    });
</script>
