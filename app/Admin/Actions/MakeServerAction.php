<?php

namespace App\Admin\Actions;

use App\Facades\UserHandling;
use App\Models\Server;
use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class MakeServerAction extends RowAction
{
    public $name = 'تعيين سيرفر اساسي';

    public function handle(Model $model, Request $request)
    {
        $oldServer = Server::where("default",1)->first();
        if ($oldServer) {
            if ($oldServer->id == $model->id) {
                return $this->response()->success ('تم بنجاح');
            }
            $oldServer->default = 0;
            $oldServer->save();
        }

        Server::find($model->id)->update(['default' => 1]);
        return $this->response()->success ('تم بنجاح')->refresh();
    }

    public function dialog()
    {
        $this->confirm('سيتم وضع هذا السيرفر هو الاساسي ويتم حذف اي سيرفر اخر من هذا الوضع  هل تريد التاكيد؟','',[]);
    }
}
