<?php

namespace App\Selectables;

use App\Models\Gift;
use Modules\Vip\Entities\VipPrivilege;
use Encore\Admin\Grid\Filter;
use Encore\Admin\Grid\Selectable;

class Gifts extends Selectable
{

    public $model = Gift::class;

    public function make()
    {
        $this->grid->model()->orderBy('sort')->orderBy('type')->where('enable', 1);

        $this->column('id');
        $this->column('name');
        $this->column('img',__('Image'))->image();
        $this->column('price', __('price'))->display(function ($coin) {
            $path = 'coin.png';
            $url = getImagePath($path);

            // Generate the image/video/svga element
            $media = handleShowImageWithTypes($this->id, $url, 50, 50);

            // Show the coin value beside it
            return "<div style='display:flex; align-items:center; gap:8px;'>
                $media
                <span style='font-weight:bold; font-size:14px; color:#333;'>{$coin}</span>
            </div>";
        });


//        $grid->column('price', __('price'))->display(function ($coin) {
//            $icon = asset('images/coin.png'); // Ensure this path is correct
//            return '<img src="' . $icon . '" alt="$" style="width: 20px; height: 20px; margin-right: 5px;">' . number_format($coin);
//        });

        $this->filter(function (Filter $filter) {
            $filter->like('name');
        });
    }
}
