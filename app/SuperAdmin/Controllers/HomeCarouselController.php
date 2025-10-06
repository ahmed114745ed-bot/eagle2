<?php

namespace App\SuperAdmin\Controllers;

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
        $grid->column('img', __('img'))->image('', 235, 77);
    
        $grid->column('actions', __('Show'))->display(function () {
            $types = [
                'display_discover' => 'Display Discover',
                'display_home_top' => 'Display Home Top',
                'display_home_middle' => 'Display Home Middle',
                'display_live' => 'Display Live',
                // 'display_country' => 'Display Country',
            ];
    
            $buttons = '';
            foreach ($types as $type => $label) {
                $buttons .= '<button class="btn btn-sm btn-primary request-banner me-1 mb-1"
                                onclic 
                                data-id="' . $this->id . '" 
                                data-type="' . $type . '">
                                <i class="fa fa-bullhorn"></i> ' . __($label) . '
                             </button>';
            }
            return $buttons;
        });
    
        $grid->disableExport();
        $grid->disableActions();
    
        Admin::script(<<<'JS'
        console.log('✅ Banner request script loaded');
        
        function bindBannerRequestButtons() {
            $(document).off('click', '.request-banner').on('click', '.request-banner', function() {
                var bannerId = $(this).data('id');
                var displayType = $(this).data('type');
                console.log('Clicked banner', bannerId, displayType);
        
                if (typeof Swal === 'undefined') {
                    alert('SweetAlert2 غير متوفر على الصفحة!');
                    return;
                }
        
                Swal.fire({
                    title: 'تأكيد الخصم',
                    text: "سيتم خصم 10 كوينز من محفظتك لإرسال طلب عرض (" + displayType + ").",
                    type: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'نعم، خصم وأرسل الطلب',
                    cancelButtonText: 'إلغاء'
                }).then((result) => {
                    console.log('Clicked banner', result);

                    if (result.value) {
                        $.ajax({
                            url: '/superadmin/banner-request/' + bannerId,
                            type: 'POST',
                            data: {
                                _token: LA.token,
                                field: displayType
                            },
                            success: function(response) {
                                Swal.fire({
                                    title: 'تم!',
                                    text: response.message,
                                    icon: 'success'
                                }).then(() => {
                                    $.pjax.reload('#pjax-container');
                                });
                            },
                            error: function(xhr) {
                                let msg = xhr.responseJSON?.message || 'حدث خطأ أثناء تنفيذ العملية';
                                Swal.fire('خطأ', msg, 'error');
                            }
                        });
                    }
                });
            });
        }
        
        $(function() {
            bindBannerRequestButtons();
            console.log('✅ Bound banner buttons');
        });
        
        $(document).on('pjax:complete', function() {
            bindBannerRequestButtons();
            console.log('🔁 Rebound after PJAX');
        });
        JS);
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
    
        //  if ($user->wallet_balance < 10) {
        //      return response()->json(['message' => 'رصيدك غير كافي!'], 422);
        //  }
 
        //  $user->wallet_balance -= 10;
        //  $user->save();
 
         SuperadminBannerRequest::create([
             'user_id' => $user->id,
             'home_carousel_id' => $banner->id,
             'coins_deducted' => 10,
             'status' => 'pending',
             'notes' =>  $request->field
         ]);
 
         return response()->json(['message' => 'تم إرسال الطلب بنجاح!']);
     }

}
