<?php

namespace App\SuperAdmin\Controllers;

use App\helper\SuperAdminHelper;
use App\Models\Country;
use App\Models\SuperadminBannerRequest;
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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Encore\Admin\Facades\Admin;
use App\Admin\Controllers\MainController;
use Encore\Admin\Grid\Tools; 
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
        return $content
            ->title(trans('HomeCarousel'))
            ->body($this->detail($id));
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
        $grid->column('img', __('Image'))->image('', 235, 77);
    
        $grid->column('actions', __('Actions'))->display(function () {

           
            $types = [
                'display_discover' => __('Display Discover'),
                'display_home_top' => __('Display Home Top'),
                'display_home_middle' => __('Display Home Middle'),
                'display_live' => __('Display Live'),
            ];
    
            $buttons = '';
            foreach ($types as $type => $label) {
                $buttons .= '<button class="btn btn-sm btn-primary request-banner me-1 mb-1"
                                data-id="' . $this->id . '" 
                                data-type="' . $type . '">
                                <i class="fa fa-bullhorn"></i> ' . $label . '
                             </button>';
            }
            return $buttons;
        });
    
        $grid->disableExport();
        $grid->disableActions();
        // $deductAmount = SuperAdminHelper::bannerDeductAmount( );
        $deductAmount = 1;

        $translations = [
            'confirm_deduction' => __('Confirm Deduction'),
            'deduct_text' => __('coins will be deducted to send the display request: ', ['count' => $deductAmount]),
            'yes_deduct' => __('Yes, deduct and send request'),
            'cancel' => __('Cancel'),
            'done' => __('Done!'),
            'error_text' => __('An error occurred while processing'),
            'error' => __('Error'),
            'sweetalert_missing' => __('SweetAlert2 is not loaded!'),
            'display_types' => [
                'display_discover' => __('Display Discover'),
                'display_home_top' => __('Display Home Top'),
                'display_home_middle' => __('Display Home Middle'),
                'display_live' => __('Display Live'),
                // 'display_country' => __('عرض الدولة'),
            ],
        ];
        
        Admin::script("
        const translations = " . json_encode($translations) . ";
    
        function bindBannerRequestButtons() {
            $(document).off('click', '.request-banner').on('click', '.request-banner', function() {
                var bannerId = $(this).data('id');
                var displayType = $(this).data('type');
                var displayLabel = translations.display_types[displayType] || displayType;
    
                if (typeof Swal === 'undefined') {
                    alert(translations.sweetalert_missing);
                    return;
                }
    
                Swal.fire({
                    title: translations.confirm_deduction,
                    text: translations.deduct_text + displayLabel,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: translations.yes_deduct,
                    cancelButtonText: translations.cancel
                }).then((result) => {
                    if (result.value) {
                        $.ajax({
                            url: '/superadmin/banner-request/' + bannerId,
                            type: 'POST',
                            data: { _token: LA.token, field: displayType },
                            success: function(response) {
                                Swal.fire({
                                    title: translations.done,
                                    text: response.message,
                                    icon: 'success'
                                }).then(() => { $.pjax.reload('#pjax-container'); });
                            },
                            error: function(xhr) {
                                let msg = xhr.responseJSON?.message || translations.error_text;
                                Swal.fire(translations.error, msg, 'error');
                            }
                        });
                    }
                });
            });
        }
    
        $(function() { bindBannerRequestButtons(); });
        $(document).on('pjax:complete', function() { bindBannerRequestButtons(); });
    ");
    $grid->tools(function (Tools $tools) {

        $tools->append('<a href="' . superadmin_url('home-carousel/history') . '"  class="btn btn-sm btn-success">' . __('admin.history') . '</a>');

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
             $form->display_country = 1;
         });

         $form->saved(function (Form $form) {
             $userCountryId = auth()->user()->country_id ?? null;
             if ($userCountryId) {
                 $form->model()->countries()->sync([$userCountryId]);
             }
         });

         return $form;
     }



     public function storeBannerRequest(HomeCarousel $banner, Request $request)
    {
        $user = auth()->user();
        $deductAmount = SuperAdminHelper::bannerDeductAmount( $banner);


        // if ($user->di < $deductAmount) {
        //     return response()->json(['message' => __('insufficient_balance')], 422);
        // }

        \DB::transaction(function () use ($user, $banner, $request, $deductAmount) {
            // $user->di -= $deductAmount;
            // $user->save();

            SuperadminBannerRequest::create([
                'user_id' => $user->id,
                'home_carousel_id' => $banner->id,
                'coins_deducted' => $deductAmount,
                'status' => 'pending',
                'notes' => $request->field,
            ]);
        });

        return response()->json(['message' => __('request_sent_success')]);
    }
}

