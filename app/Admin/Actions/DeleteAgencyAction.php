<?php

namespace App\Admin\Actions;

use App\Models\User;
use App\Models\Agency;
use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DeleteAgencyAction extends RowAction
{
    public $name;


       
        // User::where('agency_id', $model->id)->update([
        //     'agency_id' => 0,
        //     'type_user' => 0,
        // ]);

        // // Delete the agency
        // $model->delete();

        
            public function __construct($id = 0)
            {
                $this->name = __("dashboard.delete");
                parent::__construct();
            }
            public function handle(Model $model, Request $request)
            {
                try{
                    DB::beginTransaction ();
                    $model->delete ();
                    User::where('agency_id', $model->id)->update([
                        'agency_id' => 0,
                        'type_user' => 0,
                    ]);
                    DB::commit ();
                    return $this->response()->success (__('dashboard.successful'))->refresh ();
                }catch (\Exception $exception){
                    DB::rollBack ();
                    return $this->response()->error($exception->getMessage ())->refresh();
                }
            }
        
            public function dialog()
            {
                $this->confirm(__('dashboard.chickDelete'),'',[]);
            }

        }
