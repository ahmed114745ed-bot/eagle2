<?php
namespace App\Admin\Extensions\Form\Field;

use Encore\Admin\Form\Field\File;

class CustomFile extends File
{
    protected $view = 'admin::form.file';
    protected function preview()
    {
        if (!$this->value) {
            return '';
        }

        $url = $this->objectUrl($this->value);
        $ext = strtolower(pathinfo($url, PATHINFO_EXTENSION));

        // treat your special svga-ish extensions here
        if (in_array($ext, ['svga', 'zz'])) {
            $uniqueId = 'svga_' . uniqid();
            // return a container with the URL in a data attribute
            return "<div id=\"{$uniqueId}\" class=\"svga-preview\" data-svga-url=\"{$url}\" style=\"width:150px;height:150px;\"></div>";
        }

        // fallback to default behavior (for images / files)
        return parent::preview();
    }

    /**
     * Tell bootstrap-fileinput that the preview is raw HTML for svga files.
     */
    protected function initialPreviewConfig(): array
    {
        if (empty($this->value)) {
            return parent::initialPreviewConfig();
        }

        $url = $this->objectUrl($this->value);
        $ext = strtolower(pathinfo($url, PATHINFO_EXTENSION));

        if (in_array($ext, ['svga', 'zz'])) {
            return [[
                'caption' => basename($this->value),
                'key'     => 0,
                'type'    => 'html',
                'filetype'=> $ext,
                // initialPreviewAsData false means the initialPreview is raw HTML
                'previewAsData' => false,
            ]];
        }

        return parent::initialPreviewConfig();
    }

    /**
     * Append JS after the fileinput init to instantiate SVGA players.
     */
    protected function setupScripts($options)
    {
        // This sets up the fileinput init JS (same as base class)
        parent::setupScripts($options);

        // Append our SVGA initialization code.
        // It will:
        // - initialize any .svga-preview found on the page
        // - also hook fileloaded event to initialize newly-rendered previews
        $this->script .= <<<'JS'

(function () {
    function initSvgaContainer($el) {
        if (!$el || $el.data('svga-initialized')) return;
        var url = $el.data('svga-url');
        var id = $el.attr('id');
        if (!url || !id) return;
        try {
            var player = new SVGA.Player('#' + id);
            var parser = new SVGA.Parser('#' + id);
            parser.load(url, function (videoItem) {
                player.setVideoItem(videoItem);
                player.startAnimation();
                $el.data('svga-initialized', true);
            });
        } catch (e) {
            console.error('SVGA init error', e);
        }
    }

    // initialize any svga previews already in DOM (initial preview)
    $('.svga-preview').each(function () {
        initSvgaContainer($(this));
    });

    // re-initialize when fileinput renders new preview(s)
    // use the same selector the base class uses to init fileinput
    $("input' . $this->getElementClassSelector() . '").on("fileloaded fileimageloaded", function (event, file, previewId, index, reader) {
        // some previews might be injected dynamically, initialize them
        $('.svga-preview').each(function () {
            initSvgaContainer($(this));
        });
    });

})();
JS;
    }

}
