<?php

namespace App\SuperAdmin\Controllers;

use App\Admin\Services\UserSuperAdminService;
use App\Models\SuperadminBannerRequest;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\helper\SuperAdminHelper;
use App\Admin\Controllers\MainController;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;
use Illuminate\Support\Facades\Auth;
use Encore\Admin\Grid\Tools; 



class SuperadminBannerHistoryController extends MainController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'SuperadminBannerRequest';


    public function __construct(UserSuperAdminService $userService)
    {
        $this->userService = $userService;
    }

    public function index(Content $content)
    {
        return $content
            ->title(__('Banner Request History'))
            ->body($this->grid());
    }
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new SuperadminBannerRequest());
        $grid->model()->with(['homeCarousel:home_carousel_id.img']);
        $grid->model()->where('user_id', Auth::user()->id);
    
        $grid->column('id', __('ID'));
    
 
        $grid->column('homeCarousel.img', __('img'))->image('', 235, 77);

    
        $grid->column('coins_deducted', __('Coins Deducted'));
    
        $grid->column('status', __('Status'))->display(function ($status) {
            switch ($status) {
                case 'pending': return '<span class="text-warning">Pending</span>';
                case 'approved': return '<span class="text-success">Approved</span>';
                case 'rejected': return '<span class="text-danger">Rejected</span>';
                default: return $status;
            }
        });
    
        $grid->column('notes', __('Type'))->display(function ($value) {
            if ($value === 'display_discover') {
                return __('Display Discover');
            } elseif ($value === 'display_home_top') {
                return __('Display Home Top');
            } elseif ($value === 'display_home_middle') {
                return __('Display Home Middle');
            } elseif ($value === 'display_live') {
                return __('Display Live');
            } 
            else {
                return $value; 
            }
        });
        $grid->column('created_at', __('Created At'))->display(function ($createdAt) {
            return \Carbon\Carbon::parse($createdAt)->format('d/m/Y H:i');
        });
    
        $grid->column('actions', __('Actions'))->display(function () {
            if ($this->status === 'rejected') {
                        return  '<button class="btn btn-sm btn-primary resend-btn" 
                        data-id="' . $this->home_carousel_id . '" 
                        data-notes="' . e($this->notes) . '">
                    <i class="fa fa-redo"></i> ' . __('Resend') . '
                </button>';
            }
    
            return $this->status;
        });
    
        $grid->disableActions();
        $grid->disableCreateButton();
    
        Admin::script(<<<'JS'
        // ✅ تأكيد تحميل السكريبت
        console.log('✅ Resend banner script loaded');
    
        // 🔁 دالة ربط زر إعادة الإرسال
        function bindResendButtons() {
            $(document).off('click', '.resend-btn').on('click', '.resend-btn', function() {
                var bannerId = $(this).data('id');
                var notes = $(this).data('notes');
    
                // تأكد أن SweetAlert موجود
                if (typeof Swal === 'undefined') {
                    alert('SweetAlert2 غير متوفر على الصفحة!');
                    return;
                }
    
                // ✅ SweetAlert2
                Swal.fire({
                    title: 'تأكيد إعادة الإرسال',
                    text: "سيتم خصم 10 كوينز لإعادة إرسال الطلب بنفس الملاحظة.",
                    type: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'نعم، خصم وأرسل',
                    cancelButtonText: 'إلغاء'
                }).then((result) => {
                    if(result.value){
                        $.ajax({
                            url: '/superadmin/banner-request/' + bannerId,
                            type: 'POST',
                            data: {
                                _token: LA.token,
                                field: notes ,
                                id :bannerId
                            },
                            success: function(res){
                                Swal.fire({
                                    title: 'تم!',
                                    text: res.message,
                                    icon: 'success',
                                    timer: 2000,
                                    showConfirmButton: false
                                }).then(() => {
                                    $.pjax.reload('#pjax-container'); // إعادة تحميل الجدول
                                });
                            },
                            error: function(xhr){
                                let msg = xhr.responseJSON?.message || 'حدث خطأ أثناء العملية';
                                Swal.fire('خطأ', msg, 'error');
                            }
                        });
                    }
                });
            });
        }
    
        // ⏳ عند تحميل الصفحة
        $(function() {
            bindResendButtons();
            console.log('✅ Bound resend buttons');
        });
    
        // 🔁 عند أي PJAX إعادة تحميل
        $(document).on('pjax:complete', function() {
            bindResendButtons();
            console.log('🔁 Rebound resend buttons after PJAX');
        });
    JS);
    
    $grid->tools(function (Tools $tools) {
        $tools->append('<a href="' . superadmin_url('home-carousel') . '" class="btn btn-sm btn-default">
            <i class="fa fa-arrow-left"></i> ' . __('Back') . '</a>');
      
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
        $show = new Show(SuperadminBannerRequest::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('user_id', __('User id'));
        $show->field('home_carousel_id', __('Home carousel id'));
        $show->field('coins_deducted', __('Coins deducted'));
        $show->field('status', __('Status'));
        $show->field('notes', __('Notes'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new SuperadminBannerRequest());

        $form->number('user_id', __('User id'));
        $form->number('home_carousel_id', __('Home carousel id'));
        $form->number('coins_deducted', __('Coins deducted'))->default(10);
        $form->text('status', __('Status'))->default('pending');
        $form->text('notes', __('Notes'));

        return $form;
    }



}
