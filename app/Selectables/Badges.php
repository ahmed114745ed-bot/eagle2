<?php

namespace App\Selectables;

use Encore\Admin\Grid\Filter;
use Encore\Admin\Grid\Selectable;
use Modules\Badge\Entities\Badge;
use Encore\Admin\Admin;

class Badges extends Selectable
{

    public $model = Badge::class;

    public function make()
    {
        $this->column('id', __('ID'));
        $this->column('name', __('name'));
        $this->column('image', __('image'))->display(function ($path) {
            /** @var Ware $this */
            $url = getImagePath($path);
            return $this->handleShowImageWithTypes($this->id, $url, 50, 50);
        });

        $this->column('show_image', __('show image'))->display(function ($path) {
            /** @var Ware $this */
            $url = getImagePath($path);
            return handleShowImageWithTypes($this->id, $url, 50, 50);
        });

        $this->column('priority', __('Priority'))->sortable();

        $this->filter(function (Filter $filter) {
            $filter->expand();
            $filter->like('name', __('name'));
            $filter->equal('priority', __('Priority'));
        });

        Admin::script(<<<JS
function initSvgaPlayers(context = document) {
    context.querySelectorAll('.svga-player').forEach(el => {
        if (el.dataset.loaded) return;
        el.dataset.loaded = true;

        const player = new SVGA.Player(el);
        const parser = new SVGA.Parser(el);

        parser.load(el.dataset.url, videoItem => {
            player.setVideoItem(videoItem);
            player.loops = 100;
            player.clearsAfterStop = false;
            player.startAnimation();
        });
    });
}

// Initialize when selectable modal opens
$(document).on('shown.bs.modal', function () {
    initSvgaPlayers();
});
JS);
    }

    protected function handleShowImageWithTypes(string $uniqueId, ?string $url, int $width = null, int $height = null, int $borderRadius = 50, string $objectFit = 'cover'): string
    {
        $imageType = getFileExtension($url);

        // SVGA / ZZ animation
        if ($imageType === 'svga' || $imageType === 'zz') {
            $id = 'svga_' . uniqid();
            return "<div class='svga-player' data-url='{$url}' id='{$id}' style='width: {$width}px; height: {$height}px;'></div>";
        }

        // MP4 Video
        if ($imageType === 'mp4') {
            return "
                <video width='{$width}' height='{$height}' controls autoplay muted loop>
                    <source src='{$url}' type='video/mp4'>
                    <source src='{$url}' type='video/webm'>
                    Your browser does not support the video tag.
                </video>
            ";
        }

        // Normal Image
        if ($objectFit !== 'cover') {
            return '<img src="' . e($url) . '" style="width: 100px; height: 100px; object-fit: contain; border-radius: 4px; margin-right: 4px;">';
        }

        return "<img src='{$url}' style='height: {$height}px !important; width: {$width}px !important; border-radius: {$borderRadius}%; object-fit: {$objectFit};' alt='' />";
    }
}
