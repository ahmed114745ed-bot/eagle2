<?php

namespace App\Admin\Actions;

use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;


class DenyDeleteAction extends RowAction
{
    public $name;
    public function __construct($id = 0)
    {
        $this->name = __("dashboard.delete");
        parent::__construct();
    }
    
    public function handle(Model $model, Request $request)
    {
    
            return $this->response()->error(__('messages.denyDelete'))->refresh();
    }

    public function dialog()
    {
        $this->confirm(__("dashboard.delete_confirm"),'',[]);
    }
}
