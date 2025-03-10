<?php

namespace App\Admin\Controllers\AgencyControllers;

use App\Models\User;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\Helpers\Common;
use App\Models\UserTarget;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use App\Admin\Controllers\MainController;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Controllers\HasResourceActions;

class UserTargetController extends MainController
{
    use HasResourceActions;
    public $permission_name = 'agent-target';


    public function index(Content $content)
    {
        return parent::index($content
            ->title(trans('Achieved Target'))
            ->body($this->grid()));
    }
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new UserTarget);
        $grid->model ()->ofAgency()->where('agency_obtain','>',0);
        $grid->id('ID');
        $grid->column('user_id',__('user'))->display(function ($name) {
            $name =@$this->user->name ?? '';
            $uid = @$this->user->uuid;
            $path = @$this->user?->profile?->avatar;
            $defaultImage = asset("images/businessman-icon.jpg");
            $url = getImagePath($path) ?? $defaultImage;

            // Check if the image exists
            if (!isImageExists($url)) {
                $url = $defaultImage;
            }

            $image = handleShowImageWithTypes($this->id, $url, 40, 40);
            $showUrl =  ($this->user) ? url("admin/users/{$this->user->id}") : 0;
            return "
                <div style='display: flex; align-items: center; gap: 10px;'>
                    $image
                    <div>
                       <a href='{$showUrl}' style='text-decoration: none; color: inherit; display: flex; align-items: center; gap: 10px;'>
                         <span style='text-decoration: underline; cursor: pointer;'>$name</span>
                        </a>
                        <span style='color: #aaa; font-size: smaller;'>UUID: $uid</span>
                    </div>
                </div>
            ";
        });
        $grid->column('agency_id',__ ('agency'))->display(function () {
            $name = @$this->agency->name ?? '';
            $path = @$this->agency->img;
            $defaultImage = asset("images/icon-agency.jpg");
            $url = getImagePath($path) ?? $defaultImage;

            // Check if the image exists
            if (!isImageExists($url)) {
                $url = $defaultImage;
            }
            $image = handleShowImageWithTypes($this->id, $url, 40, 40);

            return "
            <div style='display: flex; align-items: center; gap: 10px;'>
                $image
                <span>$name</span>
            </div>
        ";
        });
        $grid->column('target_id',__ ('target id'));
        $grid->column('target_diamonds',__ ('target diamonds'));
        $grid->column('add_month', __('date'))->display(function ($month) {
            return $month . '/' . $this->add_year;
        });
        $grid->column('target_usd',__('usd').' '.__ ('deserved'));
        $grid->column('target_hours',__ ('target hours'));
        $grid->column('target_days',__ ('target days'));
        $grid->column('target_agency_share',__ ('agency share').'(%)');
        $grid->column('user_diamonds',__ ('user diamonds'));
        $grid->column('user_hours',__ ('user hours'));
        $grid->column('user_days',__ ('user days'));
        $grid->column('user_obtain',__ ('user obtain'))->display(function ($usd) {
            $image = asset('images/dollar.jpg'); // Adjust path as needed
            return "<div style='display: flex; align-items: center; '>
                      
                        <span>{$usd}</span>
                          <img src='{$image}' alt='USD' width='20' height='20'>
                    </div>";
        });
        $grid->column('agency_obtain',__ ('agency obtain'))->display(function ($usd) {
            $image = asset('images/dollar.jpg'); // Adjust path as needed
            return "<div style='display: flex; align-items: center; '>
                      
                        <span>{$usd}</span>
                          <img src='{$image}' alt='USD' width='20' height='20'>
                    </div>";
        });
        $grid->disableActions ();
        $grid->disableCreateButton ();
        return $grid;
    }


}
