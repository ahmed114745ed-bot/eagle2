<div class="{{$viewClass['form-group']}} {!! !$errors->has($errorKey) ? '' : 'has-error' !!}">

    <label for="{{$id}}" class="{{$viewClass['label']}} control-label">{{$label}}</label>

    <div class="{{$viewClass['field']}}">

        @include('admin::form.error')

        <input type="file" class="{{$class}}" name="{{$name}}" {!! $attributes !!} />

        @include('admin::form.help-block')

    </div>
</div>

<script>
    $(function () {
        const $input = $('input[name="img2"]');

        // destroy the admin-initialized fileinput
        $input.fileinput('destroy');

        // now reinit with your custom config
        $input.fileinput({
            overwriteInitial: true,
            initialPreviewAsData: true,
            allowedPreviewTypes: ['image', 'html', 'text', 'video', 'audio', 'flash', 'object'],
            previewFileExtSettings: {
                'image': function(ext) {
                    return /(jpg|jpeg|png|gif|bmp|svg|webp)$/i.test(ext);
                },
                'svga': function(ext) {
                    return /(svga)$/i.test(ext);
                }
            },
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

        // init SVGA players after fileinput renders
        $input.on('fileloaded fileimageloaded fileloadederror', function(event, file, previewId, index, reader) {
            $('.svga-player').each(function() {
                let container = this;
                let player = new SVGA.Player(container);
                let parser = new SVGA.Parser(container);
                parser.load(file.previewAsData || file.name, function(videoItem) {
                    player.setVideoItem(videoItem);
                    player.startAnimation();
                });
            });
        });
    });
</script>
