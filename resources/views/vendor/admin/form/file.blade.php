<div class="{{$viewClass['form-group']}} {!! !$errors->has($errorKey) ? '' : 'has-error' !!}">

    <label for="{{$id}}" class="{{$viewClass['label']}} control-label">{{$label}}</label>

    <div class="{{$viewClass['field']}}">

        @include('admin::form.error')

        <input type="file" class="{{$class}}" name="{{$name}}" {!! $attributes !!} />

        @include('admin::form.help-block')

    </div>
</div>

<script>
    $('input[name="img2"]').fileinput({
        allowedPreviewTypes: ['image', 'html', 'text', 'video', 'audio', 'flash', 'object'],
        previewFileExtSettings: {
            // define what counts as "image"
            'image': function(ext) {
                return /(jpg|jpeg|png|gif|webp|svg|svga)$/i.test(ext);
            },
            'svga': function(ext) {
                return /(svga)$/i.test(ext);
            }
        },
        previewContentTemplates: {
            svga: '<div class="file-preview-frame krajee-default kv-preview-thumb">' +
                '<div class="kv-file-content">' +
                '<div id="svga-preview" style="width:100px;height:100px;"></div>' +
                '</div>' +
                '<div class="file-thumbnail-footer"><div class="file-footer-caption">{caption}</div></div>' +
                '</div>'
        }
    });

    $.fn.fileinput.defaults.previewFileExtSettings.image = function(ext) {
        return /(jpg|jpeg|gif|png|webp|svg|svga)$/i.test(ext);
    };

    $.fn.fileinput.defaults.previewContentTemplates.svga =
        '<div class="file-preview-frame krajee-default kv-preview-thumb">' +
        '<div class="kv-file-content">' +
        '<div class="svga-container"></div>' +
        '</div>' +
        '<div class="file-thumbnail-footer">' +
        '<div class="file-footer-caption">{caption}</div>' +
        '</div>' +
        '</div>';
</script>
