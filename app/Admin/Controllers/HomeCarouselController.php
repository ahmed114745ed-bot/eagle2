<?php

namespace App\Admin\Controllers;

use Carbon\Carbon;
use Encore\Admin\Auth\Permission;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\Helpers\Common;
use App\Models\Setting;
use App\Models\HomeCarousel;
use Encore\Admin\Layout\Content;
use Encore\Admin\Controllers\HasResourceActions;
use Illuminate\Validation\Rule;
use App\Models\Country;
use App\Selectables\Countries;
use Encore\Admin\Facades\Admin;
use App\Tik\Services\Files\ImageConverter;

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


    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new HomeCarousel);
        $grid ->model()->with('displays');

        $grid->id(__('ID'));
            $grid->column('img', __('img'))->image('', 235, 77);

            $types = [
                'displayDiscover' => 'Discover',
                'displayHomeTop'  => 'Home Top',
                'displayHomeMiddle'=> 'Home Middle',
                'displayLive'     => 'Live',
                'displayCountry'  => 'Country',
                'displayRoom'  => 'Room',
            ];

            foreach ($types as $attr => $label) {
                $grid->column($attr, __($label))
                    ->display(function () use ($attr) {
                        return $this->{$attr} ? 1 : 0;
                    })
                    ->switch([
                        'on'  => ['value' => 1, 'text' => 'ON',  'color' => 'success'],
                        'off' => ['value' => 0, 'text' => 'OFF', 'color' => 'danger'],
                    ]);
            }



        $grid->column('enable', __('enable'))->switch();
        $grid->column('sort', __('sort'))->editable();

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
         $this->addBasicFields($form);
         $this->addTimeSettings($form);
         $this->addContentType($form);
         $this->addDisplayLocations($form);

         $this->syncDisplaysBeforeSave($form);
         $this->syncCountriesAfterSave($form);


         return $form;
     }


     protected function addBasicFields(Form $form)
     {
         $form->display(__('admin.ID'));
         $form->number('sort', __('sort'));
         $form->image('img', trans('img'))->setResolution(80)->required();
         $form->switch('enable', trans('enable'))->states(Common::getSwitchStates())->default(true);
     }


     protected function addTimeSettings(Form $form)
     {
        $form->select('form', trans('time view type'))->options([
            0 => __(''),
            1 => __('hours'),
            2 => __('days'),
            3 => __('months')
        ])->when('1', function (Form $form) {

            $form->text('input', trans('input'))
            ->rules('required|regex:/^\d+$/');
        
        })->when('2', function (Form $form) {
            $form->text('input', trans('input'))
            ->rules('required|regex:/^\d+$/');
        
        })->when('3', function (Form $form) {
            $form->text('input', trans('input'))
            ->rules('required|regex:/^\d+$/');
        
        });

     }


     protected function addContentType(Form $form)
     {
         $form->select('type', trans('type'))->options([
             'room'   => __('Room'),
             'normal' => __('Normal'),
             'link'   => __('URL'),
             'event'  => __('Events')
         ])->when('room', function (Form $form) {
             $form->select('owner_id', __('Owner'))
                 ->options('/api/search/users2')
                 ->ajax('/api/search/users2', 'id', 'name');
         })->when('link', function (Form $form) {
             $form->url('url', trans('url'))->rules('nullable|url');
         })->when('event', function (Form $form) {
             $form->select('event_type', trans('events'))->options([
                 'event'        => __('events'),
                 'pk_event'     => __('pk_event'),
                 'weekly_star'  => __('weekly_star'),
                 'charge_event' => __('charge_event'),
                 'event_period' => __('event_period'),
                 'weekly_cp'    => __('weekly_cp'),
             ])->when('event', fn(Form $form) => $form->url('url', trans('url')));
         });
     }


     protected function addDisplayLocations(Form $form)
     {
        $form->multipleSelect('display_at', __('Display At'))
        ->options([
            'discover'    => __('Discover'),
            'home_top'    => __('Home Top'),
            'home_middle' => __('Home Middle'),
            'live'        => __('Live'),
            'country'     => __('Country'),
            'room'     => __('Room'),
        ])
        ->rules(['array'])
        ->attribute('id', 'display_at_select');


         $form->belongsToMany('countries', Countries::class, trans('Country'));

         $form->html('<style>#countries_select { display:none; }</style>');

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
     }


     protected function syncDisplaysBeforeSave(Form $form)
     {
        $form->ignore(['duration']);

   
        // dd($form->display_at , $form->model()->display_at ,request('display_at'));


     }



     protected function syncCountriesAfterSave(Form $form)
     {
        $form->saved(function (Form $form) {
            $types = [
                'displayDiscover'   => 'discover',
                'displayHomeTop'    => 'home_top',
                'displayHomeMiddle' => 'home_middle',
                'displayLive'       => 'live',
                'displayCountry'    => 'country',
                'displayRoom'  => 'room',

            ];
            $reqKeys = array_keys($types);

            $foundKeys = array_filter($reqKeys, function($key) {
                return request()->has($key);
            });

            $existing = $form->model()->displays()->pluck('display_type')->toArray();
            $formInput = request('input') ?? $form->model()->input ?? 0;
            $formForm  = request('form') ?? $form->model()->form ?? 1;
            $displaysOrg =$form->display_at ?? $form->model()->display_at;

           
            if (is_array($displaysOrg)) {
                $displays = $displaysOrg;
            } elseif (is_string($displaysOrg)) {
                $decoded = json_decode($displaysOrg, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $displays = $decoded;
                } else {
                    $displays = array_map('trim', explode(',', $displaysOrg));
                }
            } else {
                $displays = [];
            }


            if (is_array($displays) && !empty($displays) && empty($foundKeys)) {
                $toDelete = array_diff($existing, $displays);
                if ($toDelete) {
                    $form->model()->displays()->whereIn('display_type', $toDelete)->delete();
                }

                $toAdd = array_diff($displays, $existing);

                $toAdd = array_filter($toAdd);
                foreach ($toAdd as $type) {
                    $display = $form->model()->displays()->where('display_type', $type)->first();

                    if ($display) {
                        $display->update([
                            'duration'      => $formInput,
                            'duration_unit' => match($formForm) {
                                1 => 'hours',
                                2 => 'days',
                                3 => 'months',
                                default => 'hours',
                            }
                        ]);
                    } else {
                        $form->model()->displays()->create([
                            'display_type'  => $type,
                            'duration'      => $formInput,
                            'duration_unit' => match($formForm) {
                                1 => 'hours',
                                2 => 'days',
                                3 => 'months',
                                default => 'hours',
                            }
                        ]);
                    }
                }




            } else {

                foreach ($types as $key => $type) {
                    if (request()->has($key)) {
                        $value = request($key);

                        $display = $form->model()->displays()->where('display_type', $type)->first();

                        if ($value) {
                            if ($display) {
                                $display->update([
                                    'duration'      => $formInput,
                                    'duration_unit' => match($formForm) {
                                        1 => 'hours',
                                        2 => 'days',
                                        3 => 'months',
                                        default => 'hours',
                                    }
                                ]);
                            } else {
                                $form->model()->displays()->create([
                                    'display_type'  => $type,
                                    'duration'      => $formInput,
                                    'duration_unit' => match($formForm) {
                                        1 => 'hours',
                                        2 => 'days',
                                        3 => 'months',
                                        default => 'hours',
                                    }
                                ]);
                            }

                            $existingDisplayAt = $form->model()->display_at ?? [];

                            if (!is_array($existingDisplayAt)) {
                                $existingDisplayAt = json_decode($existingDisplayAt, true) ?: [];
                            }

                            if (!in_array($type, $existingDisplayAt)) {
                                $existingDisplayAt[] = $type;
                                $form->model()->display_at = $existingDisplayAt; // ← احفظ كمصفوفة مباشرة
                                $form->model()->save();
                            }



                        } else {
                            if ($display) {
                                $display->delete();
                            }
                            $existingDisplayAt = $form->model()->display_at ?? [];

                            if (!is_array($existingDisplayAt)) {
                                $existingDisplayAt = json_decode($existingDisplayAt, true) ?: [];
                            }

                            $existingDisplayAt = array_values(array_diff($existingDisplayAt, [$type]));
                            $form->model()->display_at = $existingDisplayAt; // ← نحفظ كمصفوفة مباشرة
                            $form->model()->save();
                        }
                    }
                }


            }

            if (in_array('country', $displays ?? []) && $foundKeys == []) {
                $countries = array_filter(request('countries', []));
                $form->model()->countries()->sync($countries);
            } elseif (!empty(request('displayCountry'))) {
                if (request('displayCountry')) {
                    $countries = array_filter(request('countries', []));
                    $form->model()->countries()->sync($countries);
                } else {

                    $form->model()->countries()->detach();
                }
            }

        });
     }


     public function homeCarouselSettings(Content $content)
    {
        if (!Admin::user()->can('*')) {
            Permission::check('browse-' . 'banner-setting');
        }
        $config = Setting::whereIn('key', ['live', 'home_middle', 'home_top', 'discover'])->pluck('value', 'key')->toArray();
        return $content->view('homeCarouselSetting', compact('config'));
    }
}
