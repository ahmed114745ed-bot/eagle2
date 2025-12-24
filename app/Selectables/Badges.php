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
            return handleShowImageWithSvga($this->id, $url, 80, 80);
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

}
