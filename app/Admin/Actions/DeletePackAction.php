<?php

namespace App\Admin\Actions;

use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class DeletePackAction extends RowAction
{
    public $name;
    public function __construct($id = 0)
    {
        $this->name = __("dashboard.delete");
        parent::__construct();
    }

    public function handle(Model $model, Request $request)
    {
        $model->delete ();
        return $this->response()->success (__('dashboard.successful'));
    }

    public function dialog()
    {
        $this->confirm(__('dashboard.chickDelete'),'',[]);
    }
}
