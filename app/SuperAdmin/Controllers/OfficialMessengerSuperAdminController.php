<?php

namespace App\SuperAdmin\Controllers;

use Carbon\Carbon;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Str;
use Encore\Admin\Layout\Content;
use App\Models\AdminNotification;
use Illuminate\Support\Facades\App;
use App\Http\Controllers\Controller;
use App\Models\SuperAdminNotification;
use Encore\Admin\Controllers\HasResourceActions;
use App\Admin\Controllers\OfficialMessageController;

class OfficialMessengerSuperAdminController extends OfficialMessageController
{
    use HasResourceActions;
    public $permission_name = 'official-messages';



    public function index(Content $content)
    {
        return $content
            ->title(trans('Official messages'))
            ->body($this->grid());
    }

    public function create(Content $content)
    {
        return $content
            ->title(trans('official-messages'))
            ->body($this->form());
    }

    protected function grid()
    {
        $grid = parent::grid();

        return $grid;
    }


    protected function form()
    {
        $form = parent::form();

        return $form;
    }
}
