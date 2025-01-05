<?php
namespace App\Admin\Controllers;
use App\Admin\Extensions\AgencyExporter;
use App\Admin\Extensions\UserExporter;
use App\Helpers\Common;
use App\Models\Charge;
use App\Models\CoinLog;
use App\Models\User;
use App\Models\UserCodeInvitation;
use App\Models\UserEarnInvitation;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Illuminate\Support\Facades\Request;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class ParentUsersController extends MainController {
    public $permission_name = 'user-parent';

    public function index ( Content $content )
    {
        if (request("name") != null) {
            if (request("name") == 'users') {
                $user=User::select("id","name")->find(request("ids"));
                $title='المستخدمين التابعين لل مستخدم : '.$user?->name;
            }elseif (request("name") == 'operations') {
                $user=User::select("id","name")->find(request("useer_operation_id"));
                $title='العمليات التابعه لل مستخدم : '.$user?->name;
            }
        }
        return $content
            ->title(__('parent-users'))
            ->row(function($row) {
                $row->column(12, $this->grid());
            });
    }

    protected function grid(){
        $name= "parents";
        if (request("name") != null) {
            $name= request("name");
        }


        $grid = $name;
        $grid = $this->{$grid}();
        $grid->disableexport();
        $grid->disableActions ();
        $grid->disableCreateButton ();
        $grid->disableColumnSelector ();
        return $grid;
    }



    protected function parents()
    {
        $grid = new Grid(new User());
        $grid->model ()->orderByDesc('created_at')
            ->has('codeInvitations');



            $grid->filter(function (Grid\Filter $filter) {
                $filter->expand();
              
              
                   
                    $filter->column('1/2', function ($filter) {
                        $filter->where(function ($query) {
                            $input = $this->input;
                            $query->where('uuid', $input);
                        }, __('User'))->placeholder(__('Search by  UUID '));
                    });
            
            });
        $grid->column ('uuid',__ ('uuid'));
        
        $grid->column ('name',__("name"));
        
        $grid->column ('user_count',__("user_count"))->display (function (){
            return count($this->codeInvitations);
        });
        $grid->column ('earn',__("user_earn"))->display (function (){
            return $this->codeInvitationsEarn->sum("parent_percentage");
        });

        $grid->column('created_at', __('created'))->sortable()->diffForHumans();
        $grid->column('action', __('action'))->display (function (){
            return '<a href="?name=users&ids='.$this->id.'" class="btn btn-xs btn-primary">'.__("users").'</a>';
        });



        return $grid;
    }

    protected function users()
    {
        $userId=request("ids");

        $grid = new Grid(new UserCodeInvitation());
        $grid->model ()->orderByDesc('created_at')
            ->where("user_id",$userId);

        $grid->column ('id',__ ('ID'));
        $grid->column ('invited.name',__("name"));
        $grid->column ('user_percentage',__("user_earn"));

        $grid->column('created_at', __('created'))->sortable()->diffForHumans();
        $grid->column('action', __('action'))->display (function (){
            return '<a href="?name=operations&useer_operation_id='.$this->invited_id.'" class="btn btn-xs btn-primary">'.__("Operation log").'</a>';
        });
        return $grid;
    }

    protected function operations()
    {
        $userId=request("useer_operation_id");

        $grid = new Grid(new UserEarnInvitation());
        $grid->model ()->orderByDesc('created_at')
            ->where("user_id",$userId);

        $grid->column ('id',__ ('ID'));
        $grid->column ('user_charge',__("user_charge"));
        $grid->column ('parent_percentage',__("parent_earn"));

        $grid->column('created_at', __('created'))->sortable()->diffForHumans();
        return $grid;
    }

}
