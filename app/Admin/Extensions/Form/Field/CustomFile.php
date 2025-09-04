<?php
namespace App\Admin\Extensions\Form\Field;

use Encore\Admin\Form\Field\File;

class CustomFile extends File
{
    protected $view = 'admin::form.file';
    protected function preview()
    {
        if (!$this->value) return '';

        $url = $this->objectUrl($this->value);
        $ext = strtolower(pathinfo($url, PATHINFO_EXTENSION));
        $uniqueId = 'file_' . uniqid();

        if (in_array($ext, ['png','jpg','jpeg','gif','webp','svg'])) {
            info('hiiiii');
            return "<img src='{$url}' class='file-preview-image img-responsive' style='max-height:150px'>";
        }

        return handleShowImageWithTypes($uniqueId, $url, 100, 100, 10);
    }

    protected function initialPreviewConfig(): array
    {
        $uniqueId = 'svga_' . uniqid();

        $config = [
            'caption'          => basename($this->value),
            'key'              => 0,
            'type'             => 'html',
            'filetype'         => 'svg',
            'previewAsData'    => false,
            'previewFileIcon'  => "<div id='{$uniqueId}' style='width:100px;height:100px;'></div>
            <script>
                var player = new SVGA.Player('#{$uniqueId}');
                var parser = new SVGA.Parser('#{$uniqueId}');
                parser.load('".url($this->value)."', function(videoItem) {
                    player.setVideoItem(videoItem);
                    player.startAnimation();
                });
            </script>",
        ];

        return [$config];
    }


}
