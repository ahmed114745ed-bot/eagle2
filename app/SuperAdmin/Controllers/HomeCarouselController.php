<?php

namespace App\SuperAdmin\Controllers;

use App\Models\Country;
use App\Selectables\Countries;
use App\Tik\Services\Files\ImageConverter;
use Carbon\Carbon;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\Helpers\Common;
use App\Models\HomeCarousel;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Layout\Content;
use Illuminate\Validation\Rule;
use Encore\Admin\Facades\Admin;
use App\Admin\Controllers\MainController;

class HomeCarouselController extends MainController
{
    use HasResourceActions;

    public function index(Content $content)
    {
        return $content
        ->header(trans('Banner'))
        ->row(function ($row) {
            $row->column(12, $this->grid());
        });
          
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
            ->title(trans('HomeCarousel'))
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
        return $content
        ->title(trans('HomeCarousel'))
        ->body($this->form()->edit($id));
    }

    public function create(Content $content)
    {
        return $content
        ->title(trans('HomeCarousel'))
            ->body($this->form());
    }


    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new HomeCarousel);
    
        $grid->model()->whereHas('countries', function ($q) {
            $q->where('countries.id', auth()->user()->country_id);
        });
    
        $grid->id(__('ID'));
        $grid->column('img', trans('img'))->image('', 235, 77);
        $grid->column('url', trans('url'));
    
        $grid->column('countries', __('Country'))->pluck('name')->label();
    
        // $this->extendGrid($grid);
        $grid->disableExport();
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
        $show = new Show(HomeCarousel::findOrFail($id));

     

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */

     protected function form()
     {
         $form = new Form(new HomeCarousel);
         $this->disableFormTools($form);
     
    
     
         $form->number('sort', __('sort'));
         $form->imagePath('img', trans('img'))->setResolution(80)->required();
         $form->switch('enable', trans('enable'))->states(Common::getSwitchStates())->default(true);
     
         $form->select('form', trans('time view type'))->options([
             0 => __(''),
             1 => __('hours'),
             2 => __('days'),
             3 => __('month')
         ])->when(1, fn(Form $form) => $form->text('input', trans('input')))
           ->when(2, fn(Form $form) => $form->text('input', trans('input')))
           ->when(3, fn(Form $form) => $form->text('input', trans('input')));
     
         $form->select('type', trans('type'))
             ->options([
                 'room'   => __('Room'),
                 'normal' => __('normal'),
                 'link'   => __('url'),
                 'event'  => __('events')
             ])
             ->when('room', fn(Form $form) =>
                 $form->select('owner_id', __('owner'))
                     ->options('/api/search/users2')
                     ->ajax('/api/search/users2', 'id', 'name')
             )
             ->when('link', fn(Form $form) =>
                 $form->url('url', trans('url'))->rules('required|url')
             )
             ->when('event', fn(Form $form) =>
                 $form->select('event_type', trans('events'))
                     ->options([
                         'event'        => __('events'),
                         'pk_event'     => __('pk_event'),
                         'weekly_star'  => __('weekly_star'),
                         'charge_event' => __('charge_event'),
                         'event_period' => __('event_period'),
                         'weekly_cp'    => __('weekly_cp'),
                     ])
                     ->when('event', fn(Form $form) => $form->url('url', trans('url')))
             );
     
         $form->hidden('display_at')->default(json_encode(['country']));
     
         $form->hidden('countries');
     
         $form->saving(function (Form $form) {
             $form->model()->display_at = json_encode(['country']);
             $form->model()->display_country = 1;
         });
     
         $form->saved(function (Form $form) {
             $userCountryId = auth()->user()->country_id ?? null;
             if ($userCountryId) {
                 $form->model()->countries()->sync([$userCountryId]);
             }
         });
     
         return $form;
     }
     
        
    
}
