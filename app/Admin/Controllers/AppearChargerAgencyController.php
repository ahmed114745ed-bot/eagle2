<?php

namespace App\Admin\Controllers;

use App\Models\User;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\Helpers\Common;
use App\Admin\Controllers\MainController;
use Encore\Admin\Layout\Content;

class AppearChargerAgencyController extends MainController
{
    /**
     *
     * Title for current resource.
     *
     * @var string
     */

    public $permission_name = 'appear-charger-agency';

    public function index(Content $content)
    {
        return parent::index($content
            ->title(trans('appear-charger-agency'))
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
        return parent::show($id,$content
            ->title(trans('appear-charger-agency'))
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
        return parent::edit($id,$content
            ->title(trans('appear-charger-agency'))
            ->body($this->form()->edit($id)));
    }

    public function create(Content $content)
    {
        return parent::create($content
            ->title(trans('appear-charger-agency'))
            ->body($this->form()));
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new User());

        $grid->filter(function (Grid\Filter $filter) {
            $filter->expand();
            $filter->column('1/2', function ($filter) {
                $filter->where(function ($query) {
                    $input = $this->input;
                    $query->where('name', $input)
                        ->orWhere('uuid', $input)->orWhere('phone', $input);
                }, __('User'))->placeholder(__('Search by name , UUID , phone'));
            });
        });
        $grid->model()->whereIn('type_user', [4, 3])->whereHas('ownAgency');
        $grid->column('id', __('Id'));
        // $grid->column('uuid', __('Uuid'));
        // $grid->column('name', __('Name'));

        $grid->column('agency.owner_id', __('Owner'))->display(function () {
          
        
            $name = $this->name ?? 'Unknown Owner';
            $uid = $this->uuid ?? 'N/A';
            $phone = $this->phone ?? '-';
            $path = $this->profile->avatar ?? '';
            $defaultImage = asset("images/businessman-icon.jpg");
            $url = getImagePath($path) ?? $defaultImage;
        
            if (!isImageExists($url)) {
                $url = $defaultImage;
            }
        
            $image = handleShowImageWithTypes($this->id, $url, 40, 40);
        
            return "
            <div style='display: flex; align-items: center; gap: 10px;'>
                $image
                <div>
                    <strong>$name</strong><br>
                    <span style=' font-size: smaller;'>UID: $uid</span><br>
                    <span style=' font-size: smaller;'>Phone: $phone</span>
                </div>
            </div>
            ";
        });
        
        $grid->column('agency_id', __('Agency'))->display(function () {
            if (!$this->agency) {
                return "<span style='color: #aaa;'>No Agency</span>";
            }
        
            $name = $this->agency->name ?? 'Unknown Agency';
            $coins = number_format($this->agency->coins ?? 0);
            $path = $this->agency->img ?? '';
            $defaultImage = asset("images/agency-icon.jpg");
            $url = getImagePath($path) ?? $defaultImage;
            $icon = asset('images/coin.jpg'); // تأكد من وجود الصورة في هذا المسار
            
            if (!isImageExists($url)) {
                $url = $defaultImage;
            }
        
            $image = handleShowImageWithTypes($this->id, $url, 40, 40);
        
            return "
            <div style='display: flex; align-items: center; gap: 10px;'>
                $image
                <div>
                    <strong>$name</strong><br>
                    <span style='color: green;'> Coins: $coins</span>
                    <img src='{$icon}' alt='Coin' width='20' height='20'>
                </div>
            </div>
            ";
        });
        
        // $grid->column('agency.name', __('agency_name'));
        // $grid->column('agency.coins', __('agency_coins'));
        // // $grid->column('agency.img', __('agency_image'));
        // // $grid->column('profile.avatar', __('image'))->image('', 50);
        // $grid->column('phone', __('Phone'));
        // $grid->column('agency_id', __('agency_id'));
        $grid->column('appear_charger_agency', __('Appear charger agency'))->switch(Common::getSwitchStates());
        $grid->column('is_frozen', __("frozen"))->switch(Common::getSwitchStates());
        $grid->disableCreateButton();
        $grid->disableActions();

        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(User::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('name', __('Name'));
        $show->field('phone', __('Phone'));
        $show->field('uuid', __('Uuid'));
        $show->field('appear_charger_agency', __('Appear charger agency'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new User());

        $form->text('name', __('Name'));
        $form->mobile('phone', __('Phone'));
        $form->text('uuid', __('Uuid'));
        $form->switch('appear_charger_agency', __('Appear charger agency'));

        return $form;
    }
}
