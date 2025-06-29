<?php

namespace App\Admin\Controllers;

use App\Models\User;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\Helpers\Common;
use App\Models\UserTarget;
use App\Models\UserSallary;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;

class UserTargetController extends MainController
{
    use HasResourceActions;

    public $permission_name = 'hosts-target';

    public function index(Content $content)
    {
        checkAgencyFeature();

        return parent::index($content
            ->title(trans('Hosts Target'))
            ->body($this->grid()));
    }

    /**
     * Show interface.
     *
     * @param mixed $id
     * @param Content $content
     * @return Content
     */
    public function show($id, Content $content)
    {
        return parent::show($id, $content
            ->title(trans('users targets'))
            ->body($this->detail($id)));
    }

    /**
     * Edit interface.
     *
     * @param mixed $id
     * @param Content $content
     * @return Content
     */
    public function edit($id, Content $content)
    {
        return parent::edit($id, $content
            ->title(trans('users targets'))
            ->body($this->form()->edit($id)));
    }

    public function create(Content $content)
    {
        return  parent::create($content
            ->title(trans('users targets'))
            ->body($this->form()));
    }


    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {

        $grid = new Grid(new UserSallary);

        $grid->filter(function (Grid\Filter $filter) {
            $filter->expand();

            $filter->disableIdFilter();

            $filter->where(function ($query) {
                $query->whereHas('user', function ($subQuery) {
                    $subQuery->where('uuid', 'like', "%{$this->input}%");
                });
            }, __('UUID'));
        });
        $grid->column('id', __('id'));
        $grid->column('user_id', __('user'))->display(function ($name) {
            $name =@$this->user->name ?? '';
            $uid = @$this->user->uuid;
            if (request()->filled('_export_')) {
                return "{$name} (UUID: {$uid})";
            }

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

        $grid->column('user_agency_id', __('agency'))->display(function () {
            $name = @$this->agency->name ?? '';
            if (request()->filled('_export_')) {
                return $name;
            }
            $path = @$this->agency->img;
            $defaultImage = asset("images/icon-agency.jpg");
            $url = getImagePath($path) ?? $defaultImage;

            // Check if the image exists
            if (!isImageExists($url)) {
                $url = $defaultImage;
            }
            $image = handleShowImageWithTypes($this->id, $url, 40, 40);
            $showUrl = $this->agency ? url("admin/agencies/profile/{$this->agency->id}") : '#';

            return "
            <div style='display: flex; align-items: center; gap: 10px;'>
                <a href='{$showUrl}' style='text-decoration: none; color: inherit; display: flex; align-items: center; gap: 10px;'>
                $image
                <span>$name</span>
            </div>
        ";
        });
        $grid->column('month', __('date'))->display(function ($month) {
            return $month . '/' . $this->year;
        });

        $grid->column('hours',__ ('hours'))->display(function ($hours) {
            return $hours;
//            return explode('/', $hours)[1] ?? 0;
        });
        $grid->column('days',__ ('days'))->display(function ($days) {
            return $days;
//            return explode('/', $days)[1] ?? 0;
        });

//        $grid->column('target_diamonds',__ ('target diamonds'))->display(function ($diamond) {
//            return explode('/', $diamond)[1] ?? 0;
//        });

//        $grid->column('hours', __('user hours'))->display(function ($hours) {
//            return explode('/', $hours)[0] ?? 0;
//        });
//        $grid->column('days', __('user days'))->display(function ($days) {
//            return explode('/', $days)[0] ?? 0;
//        });
        $grid->column('diamond', __('user diamonds'))->display(function ($diamond) {
            if (request()->filled('_export_')) {
                return $diamond;
            }
//            $usd = explode('/', $diamond)[0] ?? 0;
            $image = asset('images/diamond.jpg'); // Adjust path as needed
            return "<div style='display: flex; align-items: center; '>

                        <span>{$diamond}</span>
                          <img src='{$image}' alt='USD' width='20' height='20'>
                    </div>";
        });

        $grid->column('sallary', __('salary'))->display(function ($usd) {
            if (request()->filled('_export_')) {
                return $usd;
            }
            $image = asset('images/dollar.jpg'); // Adjust path as needed
            return "<div style='display: flex; align-items: center; '>

                        <span>{$usd}</span>
                          <img src='{$image}' alt='USD' width='20' height='20'>
                    </div>";
        });
        $grid->column('cut_amount', __('cut amount'))->display(function ($usd) {
            if (request()->filled('_export_')) {
                return $usd;
            }
            $image = asset('images/dollar.jpg'); // Adjust path as needed
            $formattedUsd = number_format((float)$usd, 2);
            return "<div style='display: flex; align-items: center; '>

                        <span>{$formattedUsd}</span>
                          <img src='{$image}' alt='USD' width='20' height='20'>
                    </div>";
        });
        $grid->column('Net Salary', __('User Net Salary'))->display(function ($usd) {
            $usd = $this->sallary - $this->cut_amount;
            if (request()->filled('_export_')) {
                return $usd;
            }
            $image = asset('images/dollar.jpg'); // Adjust path as needed
            return "<div style='display: flex; align-items: center; '>

                        <span>{$usd}</span>
                          <img src='{$image}' alt='USD' width='20' height='20'>
                    </div>";
        });
        $grid->column('agency_sallary', __('agency obtain'))->display(function ($usd) {
            if (request()->filled('_export_')) {
                return $usd;
            }
            $image = asset('images/dollar.jpg'); // Adjust path as needed
            $formattedUsd = number_format((float)$usd, 2);
            return "<div style='display: flex; align-items: center; '>

                        <span>{$formattedUsd}</span>
                          <img src='{$image}' alt='USD' width='20' height='20'>
                    </div>";
        });
        $grid->disableActions();
        $grid->disableCreateButton();
        $this->extendGrid($grid);
        return $grid;
    }
}
