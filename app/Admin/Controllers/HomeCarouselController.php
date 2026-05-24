<?php

namespace App\Admin\Controllers;

use Carbon\Carbon;
use App\Models\User;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\Helpers\Common;
use App\Models\Setting;
use App\Helpers\WebPHelper;
use App\Models\HomeCarousel;
use Illuminate\Http\Request;
use App\Selectables\Countries;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use Encore\Admin\Auth\Permission;
use App\Models\HomeCarouselDisplay;
use Encore\Admin\Controllers\HasResourceActions;

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
        $grid->model()->with('displays');

        $grid->id(__('ID'));

        $grid->column('img', __('Banner'))->display(function ($img) {
            $url = $img ? getImagePath($img) : null;
            return "<div style='width: 250px; height: 80px; overflow: hidden; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);'>
                        <img src='{$url}' style='width: 100%; height: 100%; object-fit: cover; display: block;' alt='Banner'>
                    </div>";
        });

        $types = [
            'displayDiscover' => 'Discover',
            'displayHomeTop' => 'Home Top',
            'displayHomeMiddle' => 'Home Middle',
            'displayLive' => 'Live',
            'displayCountry' => 'Country',
            'displayRoom' => 'Room',
        ];

        $typeMapping = [
            'displayDiscover' => 'discover',
            'displayHomeTop' => 'home_top',
            'displayHomeMiddle' => 'home_middle',
            'displayLive' => 'live',
            'displayCountry' => 'country',
            'displayRoom' => 'room',
        ];

        foreach ($types as $attr => $label) {
            $grid->column($attr, __($label))
                ->display(function () use ($attr, $typeMapping) {

                    $type = $typeMapping[$attr];
                    $display = $this->displays->firstWhere('display_type', $type);

                    $status = $display && ($display->status) == 1 ? 1 : 0;

                    $duration = 0;
                    if ($display && $display->end_at && $display->duration != 0) {
                        $duration = Carbon::parse($display->end_at)->isFuture()
                            ? Carbon::parse($display->end_at)->diffForHumans(
                                now(),
                                ['parts' => 2, 'short' => true, 'syntax' => Carbon::DIFF_ABSOLUTE]
                            )
                            : 0;
                        $status = $display && $display->end_at && Carbon::parse($display->end_at)->isFuture() && ($display->status) == 1 ? 1 : 0;
                    } elseif ($display && $display->duration == 0) {
                        $duration = '∞';
                        $status = $display && ($display->status) == 1 ? 1 : 0;
                    }

                    $displayId = $display ? $display->id : 0;
                    $icon = "<i class='fa fa-clock-o text-success'></i>";

                    return "
                            <div style='text-align:center; margin-bottom:20px;'> <!-- add spacing -->
                                <label class='switch'>
                                    <input
                                        type='checkbox'
                                        class='display-switch'
                                        data-home-carousel-id='{$this->id}'
                                        data-display-type='{$type}'
                                        data-id='{$displayId}'
                                        " . ($status ? 'checked' : '') . ">
                                    <span class='slider round'></span>
                                </label>
                                <br>
                                <small class='duration-text' style='display:block; margin-top:5px;'>
                                {$duration} {$icon}
                                </small>
                            </div>
                            ";
                })
                ->style('text-align:center;');
        }



        Admin::script("
    if (typeof axios === 'undefined') {
        var script = document.createElement('script');
        script.src = 'https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js';
        document.head.appendChild(script);
    }

    function bindDisplaySwitch() {

        document.querySelectorAll('.display-switch').forEach(function(el) {

            // Clone and replace to remove all old event listeners
            var newEl = el.cloneNode(true);
            el.parentNode.replaceChild(newEl, el);

            newEl.addEventListener('change', function() {

                let checkbox = this;

                // Disable all switches while processing to prevent duplicate clicks
                document.querySelectorAll('.display-switch').forEach(function(sw) { sw.disabled = true; });

                let payload = {
                    home_carousel_id: checkbox.dataset.homeCarouselId,
                    display_type: checkbox.dataset.displayType,
                    status: checkbox.checked ? 1 : 0
                };

                axios.post('/admin/home-carousel-display-toggle', payload)
                    .then(function(res) {

                        if (!res.data.success) {
                            checkbox.checked = !payload.status;
                            toastr.clear();
                            toastr.error(res.data.message);
                            return;
                        }

                        checkbox.dataset.id = res.data.display_id ?? 0;

                        toastr.clear();
                        toastr.success(res.data.message);

                        $.pjax.reload('#pjax-container');

                    })
                    .catch(function(error) {

                        checkbox.checked = !payload.status;

                        toastr.clear();

                        if (error.response && error.response.data) {

                            let message = error.response.data.message || 'Validation error';

                            if (error.response.data.errors) {
                                message = Object.values(error.response.data.errors)
                                    .flat()
                                    .join('<br>');
                            }

                            toastr.error(message);

                        } else {
                            toastr.error('Something went wrong');
                        }

                    })
                    .finally(function() {
                        document.querySelectorAll('.display-switch').forEach(function(sw) { sw.disabled = false; });
                    });
            });
        });
    }

    bindDisplaySwitch();
    $(document).off('pjax:complete', bindDisplaySwitch).on('pjax:complete', bindDisplaySwitch);
");


        $grid->column('enable', __('enable'))->switch();
        $grid->column('sort', __('sort'))->editable();

        Admin::style('
            .switch {
              position: relative;
              display: inline-block;
              width: 50px;
              height: 24px;
            }

            .switch input {
              opacity: 0;
              width: 0;
              height: 0;
            }

            .slider {
              position: absolute;
              cursor: pointer;
              top: 0;
              left: 0;
              right: 0;
              bottom: 0;
              background-color: #ccc;
              transition: .4s;
              border-radius: 24px;
            }

            .slider:before {
                position: absolute;
                content: "";
                height: 18px;
                width: 18px;
                left: 3px;
                bottom: 3px;
                background-color: white;
                transition: .4s;
                border-radius: 50%;
            }

            input:checked + .slider {
            background-color: #4caf50;
            }

            input:focus + .slider {
            box-shadow: 0 0 1px #4caf50;
            }

            input:checked + .slider:before {
            transform: translateX(26px);
            }
        ');


        Admin::script("
        if (window.innerWidth >= 1024) { // Example threshold for desktop screens
            $('.table-responsive').removeClass('table-responsive');
            }
        ");
        return $grid;
    }




    public function toggleStatus(Request $request)
    {
        $data = $request->validate([
            'home_carousel_id' => 'required|exists:home_carousels,id',
            'display_type' => 'required|string',
            'status' => 'required|boolean',
        ]);

        $homeCarousel = HomeCarousel::findOrFail($data['home_carousel_id']);

        $durationUnit = match ($homeCarousel->form) {
            2 => 'days',
            3 => 'months',
            4 => 'lifetime',
            default => 'hours',
        };

        $duration = $homeCarousel->input ?? 1;

        $endAt = match ($durationUnit) {
            'days' => now()->addDays($duration),
            'months' => now()->addMonths($duration),
            'lifetime' => now(),
            default => now()->addHours($duration),
        };

        $maxTimestamp = Carbon::create(2038, 1, 19, 3, 14, 7);

        if ($endAt->greaterThan($maxTimestamp)) {
            return response()->json([
                'success' => false,
                'message' => __('Duration is too large. Maximum allowed date is 19-01-2038.')
            ], 422);
        }

        $display = HomeCarouselDisplay::firstOrNew([
            'home_carousel_id' => $data['home_carousel_id'],
            'display_type' => $data['display_type'],
        ]);

        if ($data['status']) {
            $display->status = 1;

            if (!$display->exists || Carbon::parse($display->end_at)->isPast()) {
                $display->end_at = $endAt;
            }

            $display->duration_unit = $durationUnit;
            $display->duration = $duration;
        } else {
            if ($display->exists) {
                $display->status = 0;
            }
        }

        $display->save();

        return response()->json([
            'success' => true,
            'message' => __('Submission status updated successfully!'),
            'display_id' => $display->id,
        ]);
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
        $form->number('sort', __('sort'));
        $form->imagePath('img', trans('img'))
            /**->setResolution(80)*/
            ->required();
    }


    protected function addTimeSettings(Form $form)
    {
        $form->select('form', trans('time view type'))->options([
            0 => __(''),
            1 => __('hours'),
            2 => __('days'),
            3 => __('months'),
            4 => __('lifetime')
        ])->when('1', function (Form $form) {

            $form->text('input', trans('input'));
        })->when('2', function (Form $form) {
            $form->text('input', trans('input'));
        })->when('3', function (Form $form) {
            $form->text('input', trans('input'))
                ->rules('integer|min:1|max:99');
        })->when('4', function (Form $form) {
        });
        $form->switch('enable', trans('enable'))->states(Common::getSwitchStates())->default(true);
    }


    protected function addContentType(Form $form)
    {
        $form->select('type', trans('type'))->options([
            'room' => __('Room'),
            'normal' => __('Normal'),
            'link' => __('URL'),
            'event' => __('Events')
        ])->when('room', function (Form $form) {
            $form->select('owner_id', __('Owner'))
                ->options($this->ownerOptions())
                ->ajax('/api/search/owner-rooms', 'id', 'name');
        })->when('link', function (Form $form) {
            $form->url('url', trans('url'))->rules('nullable|url');
        })->when('event', function (Form $form) {
            $form->select('event_type', trans('events'))->options([
                'event' => __('events'),
                'pk_event' => __('pk_event'),
                'weekly_star' => __('weekly_star'),
                'charge_event' => __('charge_event'),
                'event_period' => __('event_period'),
                'weekly_cp' => __('weekly_cp'),
            ])->when('event', fn(Form $form) => $form->url('url', trans('url')));
        });
    }

    protected function ownerOptions($editing = false)
    {
        return function ($value) {
            if (!$value) return [];
            $user = User::where('id', $value)->whereHas('ownerAudioRoom')->first();
            return $user ? [$user->id => $user->id . '_' . $user->name] : [];
        };
    }


    protected function addDisplayLocations(Form $form)
    {
        $form->multipleSelect('display_at', __('Display At'))
            ->options([
                'discover' => __('Discover'),
                'home_top' => __('Home Top'),
                'home_middle' => __('Home Middle'),
                'live' => __('Live'),
                'country' => __('Country'),
                'room' => __('Room'),
            ])
            ->rules(['array'])
            ->attribute('id', 'display_at_select');

        Admin::style('
            .select2-container--default .select2-selection--multiple .select2-selection__choice {
                background-color: var(--primary-color) !important;
            }
        ');

        $form->html('<div class="full-column-width countries-wrapper" id="countries_wrapper">')->setWidth(12, 0);
        $form->belongsToMany('countries', Countries::class, trans('Country'));
        $form->html('</div>')->setWidth(12, 0);

        $form->html('<style>#countries_select { display:none; }</style>');

        Admin::style('
            .rtl .fields-group .form-group {
                display: block!important;
            }
        ');

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



        $form->saving(function (Form $form) {
            try {
                if (request()->hasFile('img')) {

                    $path = WebPHelper::uploadWebp(
                        request()->file('img'),
                        'images',
                        'splash',
                        async: false  // Admin: keep sync for immediate feedback

                    );

                    $form->image_url = $path;
                }
            } catch (\Exception $e) {
                dd($e->getMessage());
            }
        });
        // dd($form->display_at , $form->model()->display_at ,request('display_at'));


    }


    protected function syncCountriesAfterSave(Form $form)
    {
        $form->saved(function (Form $form) {


            $types = [
                'displayDiscover' => 'discover',
                'displayHomeTop' => 'home_top',
                'displayHomeMiddle' => 'home_middle',
                'displayLive' => 'live',
                'displayCountry' => 'country',
                'displayRoom' => 'room',

            ];
            $reqKeys = array_keys($types);

            $foundKeys = array_filter($reqKeys, function ($key) {
                return request()->has($key);
            });

            $existing = $form->model()->displays()->pluck('display_type')->toArray();
            $formInput = request('input') ?? ($form->model()->input ?? 0);
            $formForm = request('form') ?? ($form->model()->form ?? 1);

            $displaysOrg = $form->display_at ?? $form->model()->display_at;


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

            $displays = array_filter($displays, fn($v) => !is_null($v) && $v !== '');
            // dd( $displays);
            if (is_array($displays) && !empty($displays) && empty($foundKeys)) {

                $toDelete = array_diff($existing, $displays);

                if ($toDelete) {
                    $form->model()->displays()->whereIn('display_type', $toDelete)->delete();
                }

                // $toAdd = array_diff($displays, $existing);
                // $toAdd = array_filter($toAdd);
                //  $toAdd = array_filter($toAdd, fn($v) => $v !== null && $v !== '');

                foreach ($displays as $type) {

                    $display = $form->model()->displays()->where('display_type', $type)->first();
                    switch ($formForm) {
                        case 1:
                            $duration_unit = 'hours';
                            break;
                        case 2:
                            $duration_unit = 'days';
                            break;
                        case 3:
                            $duration_unit = 'months';
                            break;
                        case 4:
                            $duration_unit = 'lifetime';
                            break;
                        default:
                            $duration_unit = 'hours';
                    }

                    if ($display) {
                        $display->update([
                            'duration' => $formInput,
                            'duration_unit' => $duration_unit,
                        ]);
                    } else {
                        $form->model()->displays()->create([
                            'display_type' => $type,
                            'duration' => $formInput,
                            'duration_unit' => $duration_unit,
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
                                    'duration' => $formInput,
                                    'duration_unit' => match ($formForm) {
                                        1 => 'hours',
                                        2 => 'days',
                                        3 => 'months',
                                        4 => 'lifetime',
                                        default => 'hours',
                                    }
                                ]);
                            } else {
                                $form->model()->displays()->create([
                                    'display_type' => $type,
                                    'duration' => $formInput,
                                    'duration_unit' => match ($formForm) {
                                        1 => 'hours',
                                        2 => 'days',
                                        3 => 'months',
                                        4 => 'lifetime',
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


    public function updateBannerDisplay()
    {
        $bannerDisplay = HomeCarouselDisplay::whereHas('carousel')->with('carousel')->get();

        foreach ($bannerDisplay as $banner) {
            $formForm = $banner->carousel->form;
            switch ($formForm) {
                case 1:
                    $duration_unit = 'hours';
                    break;
                case 2:
                    $duration_unit = 'days';
                    break;
                case 3:
                    $duration_unit = 'months';
                    break;
                default:
                    $duration_unit = 'hours';
            }
            $banner->update([
                'duration' => $banner->carousel->input,
                'duration_unit' => $duration_unit,
            ]);
        }
        return 'done';
    }
}
