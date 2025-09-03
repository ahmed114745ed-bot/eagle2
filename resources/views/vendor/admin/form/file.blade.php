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
        // allow .svga as "object" preview
        allowedPreviewTypes: ['image', 'html', 'text', 'video', 'audio', 'flash', 'object'],

        // tell FileInput: when ext=svga, treat it special
        previewFileExtSettings: {
            // keep defaults
            'image': function(ext) {
                return /(jpg|jpeg|png|gif|bmp|svg|webp)$/i.test(ext);
            },
            // define our new type
            'svga': function(ext) {
                return /(svga)$/i.test(ext);
            }
        },

        // define how to render .svga
        previewContentTemplates: {
            svga:
                '<div class="file-preview-frame krajee-default kv-preview-thumb">' +
                '<div class="kv-file-content">' +
                '<div class="svga-player" style="width:100px;height:100px;"></div>' +
                '</div>' +
                '<div class="file-thumbnail-footer">' +
                '<div class="file-footer-caption">{caption}</div>' +
                '</div>' +
                '</div>'
        }
    });

    // After fileinput initialized:
    $('.svga-player').each(function() {
        var container = this;
        var player = new SVGA.Player(container);
        var parser = new SVGA.Parser(container);
        parser.load($(container).closest('.file-preview-frame').find('.kv-file-caption').text(), function(videoItem) {
            player.setVideoItem(videoItem);
            player.startAnimation();
        });
    });
</script>
