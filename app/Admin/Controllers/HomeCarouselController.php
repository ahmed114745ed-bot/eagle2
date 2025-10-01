<?php

namespace App\Admin\Controllers;

use App\Models\Country;
use App\Selectables\Countries;
use App\Tik\Services\Files\ImageConverter;
use Carbon\Carbon;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\Helpers\Common;
use App\Models\HomeCarousel;
use Encore\Admin\Layout\Content;
use App\Models\Admin as AdminModel;
use Illuminate\Support\Facades\Auth;
use App\Admin\Actions\DenyDeleteAction;
use Encore\Admin\Controllers\HasResourceActions;
use Illuminate\Validation\Rule;
use Encore\Admin\Facades\Admin;
class HomeCarouselController extends MainController
{
    use HasResourceActions;
    public $permission_name = 'banner';
    public function index(Content $content)
    {
        return parent::index($content
            ->title(trans('Banner'))
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
        return parent::edit($id, $content
            ->title(trans('HomeCarousel'))
            ->body($this->form()->edit($id)));
    }

    public function create(Content $content)
    {
        return parent::create($content
            ->title(trans('HomeCarousel'))
            ->body($this->form()));
    }

    public function update($id)
    {
        // $id = request()->route('id');
        $banner = HomeCarousel::find($id);
        $admin = Auth::user();
        $created = AdminModel::find($banner->created_by);
        if ((!$admin->isRole('developer')) && $created && ($created->isRole('developer'))) {
            admin_info(trans('messages.denyDelete'));
            return redirect()->route('admin.home_carousels.index');
        } else {
            return $this->form()->update($id);
        }
    }


    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new HomeCarousel);
    
        $grid->id(__('ID'));
        $grid->column('img', trans('img'))->image('', 235, 77);
        $grid->column('url', trans('url'))->url();

        foreach (['display_discover' => 'Display Discover',
        'display_home_top' => 'Display Home Top',
        'display_home_middle' => 'Display Home Middle',
        'display_live' => 'Display Live',
        'display_country' => 'Display Country'] as $field => $label) {
  
            $grid->column($field, __($label))
                ->switch([
                    'on'  => ['value' => 1, 'text' => 'ON',  'color' => 'success'],
                    'off' => ['value' => 0, 'text' => 'OFF', 'color' => 'danger'],
                ]);
            }
        $grid->column('enable', trans('enable'))
            ->switch(Common::getSwitchStates())
            ->display(function ($enable) {
                if ($this->duration > Carbon::now()->timestamp || $this->duration == null) {
                    return $enable;
                }
                return null;
            });
    
        $grid->column('sort', trans('sort'))->editable();
    
        $this->extendGrid($grid);
        $grid->disableExport();
        $grid->actions(function ($actions) {
            $model = $actions->row;
            $admin = Auth::user();
            $created = AdminModel::find($model->created_by);

           // dd( $admin ,$created);
           if ((!$admin->isRole('developer')) && $created && ($created->isRole('developer'))) {
                $actions->disableDelete();
                $actions->add(new DenyDeleteAction());
            }
        });
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

        //        $show->id('ID');
        //        $show->img('img');
        //        $show->contents('contents');
        //        $show->url('url');
        //        $show->enable('enable');
        //        $show->sort('sort');
        //        $show->created_at(trans('admin.created_at'));
        //        $show->updated_at(trans('admin.updated_at'));

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
        
            // hidden fields
            $form->hidden('display_discover')->default(0);
            $form->hidden('display_home_top')->default(0);
            $form->hidden('display_home_middle')->default(0);
            $form->hidden('display_live')->default(0);
            $form->hidden('display_country')->default(0);
        
            $form->display(__('admin.ID'));
            $form->number('sort', __('sort'));
             $form->imagePath('img', trans('img'))->setResolution(80)->required();

            $form->switch('enable', trans('enable'))->states(Common::getSwitchStates())->default(true);
       
            $form->select('form', trans('time view type'))->options([
                0 => __(''),
                1 => __('hours'),
                2 => __('days'),
                3 => __('month')
            ])->when(1, function (Form $form) {
                $form->text('input', trans('input'));
            })->when(2, function (Form $form) {
                $form->text('input', trans('input'));
            })->when(3, function (Form $form) {
                $form->text('input', trans('input'));
            });
        
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
        
            // display_at options
            $form->multipleSelect('display_at', __('Display At'))
                ->options([
                    'discover'    => __('Discover'),
                    'home_top'    => __('Home Top'),
                    'home_middle' => __('Home Middle'),
                    'live'        => __('Live'),
                    'country'     => __('Country'),
                ])
                ->rules(['array'])
                ->attribute('id', 'display_at_select');
        
            $form->ignore('display_at');
            $form->belongsToMany('countries', Countries::class, trans('Country'));

        $form->select('type', trans('type'))
            ->options(['room' => __('Room'), 'normal' => __('normal'), 'link' => __('url'), 'event' => __('events')])
            ->when('room', function (Form $form) {
                $form->select('owner_id', __('owner'))->options('/api/search/users2')->ajax('/api/search/users2', 'id', 'name');
            })->when('link', function (Form $form) {
                $form->url('url', trans('url'))->rules('required|url');
            })->when('event', function (Form $form) {
                $form->select('event_type', trans('events'))->options(['event' => __('events'), 'pk_event' => __('pk_event'), 'weekly_star' => __('weekly_star'), 'charge_event' => __('charge_event'), 'event_period' => __('event_period'), 'weekly_cp' => __('weekly_cp')])->when('event', function (Form $form) {
                    $form->url('url', trans('url'));
                });
            });
            if ($form->isCreating()) {
                $form->model()->created_by = auth()->id();
            }
            $form->model()->updated_by = auth()->id();


            $form->html('<style>#countries_select { display:none; }</style>');
        
            // script to toggle
            Admin::script("
                function toggleCountriesField() {
                    var displayAt = document.getElementById('display_at_select');
                    var countriesField = document.querySelector('#countries_select').closest('.form-group');
                    if (!countriesField) return;
        
                    var values = Array.from(displayAt.selectedOptions).map(o => o.value);
                    countriesField.style.display = values.includes('country') ? 'block' : 'none';
                }
        
                document.addEventListener('DOMContentLoaded', function() {
                    document.getElementById('display_at_select').addEventListener('change', toggleCountriesField);
                    toggleCountriesField();
                });
            ");
        
            // before save
            $form->saving(function (Form $form) {
                $displays = request('display_at', []);
                if (request()->has('display_at')) {
                    
        
                $form->model()->display_discover    = in_array('discover', $displays);
                $form->display_discover    = in_array('discover', $displays);
                $form->model()->display_home_top    = in_array('home_top', $displays);
                $form->display_home_top    = in_array('home_top', $displays);
                $form->model()->display_home_middle = in_array('home_middle', $displays);
                $form->display_home_middle = in_array('home_middle', $displays);
                $form->model()->display_live        = in_array('live', $displays);
                $form->display_live        = in_array('live', $displays);
                $form->model()->display_country     = in_array('country', $displays);
                $form->display_country     = in_array('country', $displays);
        
                $form->model()->display_at = json_encode($displays);
            }
        });
        
            $form->saved(function (Form $form) {

                if (request()->has('display_at')) {
                    $displays = json_decode($form->model()->display_at ?? '[]', true);
        
                    if (in_array('country', $displays)) {
                        $countries = array_filter(request('countries', []));
                        $form->model()->countries()->sync($countries);
                    } else {
                        $form->model()->countries()->detach();
                    }
                }
            });
        
            return $form;
        }
        
    
}
