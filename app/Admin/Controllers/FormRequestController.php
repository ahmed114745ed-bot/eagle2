<?php

namespace App\Admin\Controllers;


use App\Models\Agency;
use App\Models\Bd;
use App\Models\FormRequest;
use App\Models\ShippingAgency;
use App\Models\User;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Encore\Admin\Layout\Content;
use Encore\Admin\Widgets\Box;
use App\Admin\Services\UserService;
use Encore\Admin\Facades\Admin;

class FormRequestController extends AdminController
{
    protected $title;

    public function __construct()
    {
        $this->title = __('requests_title');
    }

   
    public function index(Content $content)
    {
        $type = request()->get('type', 'host_agency');

        $buttons = [
            'host_agency'    => __('Host Agency'),
            'bd_form'        => __('BD Form'),
            'shaping_agency' => __('Shaping Agency'),
        ];

        $header = '<div style="margin-bottom:15px;">';
        foreach ($buttons as $key => $label) {
            $active = $type === $key ? 'background:#1890ff;color:white;' : 'background:#f5f5f5;';
            $header .= "<a href='?type={$key}' class='btn btn-sm' style='margin-right:5px;{$active}'>{$label}</a>";
        }
        $header .= '</div>';

        return $content
            ->title($this->title)
            ->row($header)
            ->row($this->getGrid($type)->render());
    }

    protected function getGrid($type)
    {
        $grid = new Grid(new FormRequest());

        $grid->model()
        ->with(['user', 'template', 'bd'])
        ->where('form_template_type', $type)->orderByDesc('id');

        $grid->column('user', __('user'))
            ->display(function ($name) {

            $user = $this->user;
            if (! $user) {
                return '';
            }

            return app(UserService::class)->adminUserAvatar($user);
        });

        $grid->column('name', __('name'));

        if($type != 'bd_form'){
            $grid->column('bd_id', __('Bd'))->display(function ($name) {
                if (request()->filled('_export_')) {
                    return $name;
                }
                if ( !$this->bd) {
                    return  '-';
                }
    
                $id = $this->bd->id ?? '-';
                $name = $this->bd?->username ?? 'غير معروف';
                $path = $this->bd?->avatar;
                $defaultImage = asset("images/businessman-icon.jpg");
                $url = getImagePath($path) ?? $defaultImage;
    
                if (!isImageExists($url)) {
                    $url = $defaultImage;
                }
    
                $image = handleShowImageWithTypes($this->bd?->id, $url, 40, 40);
                $showUrl = url("admin/usersBd/{$this->bd?->id}");
    
                return "
                    <div style='display: flex; align-items: center; gap: 10px;'>
                        $image
                        <div>
                           <a href='{$showUrl}' style='text-decoration: none; color: inherit; display: flex; align-items: center; gap: 10px;'>
                             <span style='text-decoration: underline; cursor: pointer;'>$name</span>
                            </a>
                            <span style='font-size: smaller;'>ID: $id</span>
                        </div>
                    </div>
                ";
            });
            $grid->column('whatsapp_number', __('whatsapp_number'))->display(fn($v) => $v ?? '-');
        }
        if($type == 'bd_form'){
            $grid->column('country', __('country'))->display(fn($v) => $v ?? '-');

        }
        $grid->column('status', __('status'))->label([
            'pending' => 'default',
            'approved' => 'success',
            'rejected' => 'danger'
        ]);

        $grid->column('actions', __('Actions'))->display(function () {
            $approveUrl = route('admin.requests.approve', $this->id);
            $rejectUrl  = route('admin.requests.reject', $this->id);
    
            if ($this->status === 'rejected') {
                return '<span class="text-danger">' . __('Rejected') . '</span>';
            }
    
            if ($this->status === 'approved') {
                return '<span class="text-success">' . __('Approved') . '</span>';
            }
    
            $approveText = __('Approved');
            $rejectText  = __('Reject');
    
            return <<<HTML
                <button class="btn btn-success btn-sm approve-btn" data-url="{$approveUrl}">{$approveText}</button>
                <button class="btn btn-danger btn-sm reject-btn" data-url="{$rejectUrl}">✖ {$rejectText}</button>
            HTML;
        });
    
        Admin::script("
            document.addEventListener('DOMContentLoaded', function () {
    
                function sendRequest(url) {
                    return fetch(url, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': LA.token,
                            'Accept': 'application/json',
                        },
                    }).then(res => res.json());
                }
    
                function handleAction(button, actionType) {
                    button.addEventListener('click', function(e){
                        e.preventDefault();
    
                        const messages = {
                            approve: {
                                title: 'هل أنت متأكد من الموافقة على هذا الطلب؟',
                                confirm: 'نعم',
                                cancel: 'إلغاء',
                                color: '#28a745'
                            },
                            reject: {
                                title: 'هل أنت متأكد من رفض هذا الطلب؟',
                                confirm: 'نعم',
                                cancel: 'إلغاء',
                                color: '#dc3545'
                            },
                            success: {
                                en: 'Action completed successfully!',
                                ar: 'تمت العملية بنجاح!',
                                hi: 'क्रिया सफलतापूर्वक पूरी हुई!',
                                tr: 'İşlem başarıyla tamamlandı!'
                            },
                            error: {
                                en: 'An error occurred!',
                                ar: 'حدث خطأ أثناء العملية',
                                hi: 'एक त्रुटि हुई!',
                                tr: 'İşlem sırasında hata oluştu!'
                            }
                        };
    
                        const locale = document.documentElement.lang || 'ar';
    
                        Swal.fire({
                            title: messages[actionType].title,
                            icon: 'question',
                            showCancelButton: true,
                            confirmButtonText: messages[actionType].confirm,
                            cancelButtonText: messages[actionType].cancel,
                            confirmButtonColor: messages[actionType].color,
                            cancelButtonColor: '#6c757d',
                        }).then((result) => {
                            if (result.value) {
                                const url = button.dataset.url;
                                sendRequest(url).then(res => {
                                    if (res.success) {
                                        Swal.fire({
                                            title: res.message || messages.success[locale],
                                            icon: 'success',
                                            timer: 2000,
                                            showConfirmButton: false
                                        });
                                        button.closest('tr').remove();
                                    } else {
                                        Swal.fire('خطأ', res.message || messages.error[locale], 'error');
                                    }
                                }).catch(() => {
                                    Swal.fire('خطأ', messages.error[locale], 'error');
                                });
                            }
                        });
                    });
                }
    
                document.querySelectorAll('.approve-btn').forEach(btn => handleAction(btn, 'approve'));
                document.querySelectorAll('.reject-btn').forEach(btn => handleAction(btn, 'reject'));
            });
        ");
    
        $grid->disableActions();
        $grid->disableCreateButton();

        return $grid;
    }

    protected function detail($id)
    {
        $show = new Show(FormRequest::findOrFail($id));

        $show->field('user.name', __('name'));
        $show->field('bd_id', __('bd_id'));
        $show->field('agency_name', __('agency_name'));
        $show->field('whatsapp_number', __('whatsapp_number'));
        $show->field('country', __('country'));
        $show->field('form_template_type', __('form_template_type'));
        $show->field('status', __('status'));
        $show->field('data', __('additional_info'))->as(function ($data) {
            $array = json_decode($data, true);
            $html = '';
            if ($array && is_array($array)) {
                foreach ($array as $key => $value) {
                    $html .= "<b>{$key}:</b> {$value}<br/>";
                }
            }
            return $html;
        })->unescape();

        return $show;
    }
    protected function form()
    {
        $form = new Form(new FormRequest());
    
        $form->display('user.name', __('name'));
        $form->display('bd_id', __('bd_id'));
        $form->display('agency_name', __('agency_name'));
        $form->display('whatsapp_number', __('whatsapp_number'));
        $form->display('country', __('country'));
        $form->display('form_template_type', __('form_template_type'));
        $form->display('status', __('status'));
        $form->textarea('data', __('additional_info'))->readonly();
    
        return $form;
    }
    
    public function approve($id)
    {
        $request = FormRequest::findOrFail($id);
    
        switch ($request->form_template_type) {
            case 'host_agency':
                return $this->approveHostAgency($request);
    
            case 'bd_form':
                return $this->approveBdForm($request);
    
            case 'shaping_agency':
                return $this->approveShapingAgency($request);
    
            default:
                admin_toastr(__('Unknown form type'), 'error');
                return redirect()->back();
        }
    }


    protected function approveHostAgency($request)
    {
      
        $owner= User::where('id',$request->submitted_by)->first();
        Agency::create([
                'name' => $request->name,
                'phone' => $request->whatsapp_number,
                'app_owner_id' => $owner->id,
                'bd_id' => $request->bd_id,
                'country_id' => $owner->country_id,
        ]);
        $request->update(['status' => 'approved']);

        return response()->json(['success' => true, 'message' => __('تمت الموافقة بنجاح')]);

    }

    protected function approveBdForm($request)
    {
        $data = $request->data;

        if (is_string($data)) {
            $data = json_decode(trim($data, '"'), true);
        }
    
       
        
        $phoneUser= User::where('id',$request->submitted_by)->first();
        Bd::create([
                'username' => $request->name,
                'app_id' => $phoneUser->id,
                'country_id' => $phoneUser->country_id,
                'password' => $data['password'] ?? 123456789,
        ]);

        $request->update(['status' => 'approved']);

        return response()->json(['success' => true, 'message' => __('تمت الموافقة بنجاح')]);

    }

    protected function approveShapingAgency($request)
    {
        $owner= User::where('id',$request->submitted_by)->first();
        ShippingAgency::create([
                'name' => $request->name,
                'phone' => $request->whatsapp_number,
                'app_owner_id' => $owner->id,
                'bd_id' => $request->bd_id,
                'country_id' => $owner->country_id,
        ]);
        $request->update(['status' => 'approved']);
        return response()->json(['success' => true, 'message' => __('تمت الموافقة بنجاح')]);

    }



    
    public function reject($id)
    {
        $request = FormRequest::findOrFail($id);
        $request->status = 'rejected';
        $request->save();
    
        admin_toastr(__('rejected_message'), 'error');
        return redirect()->back();
    }
    
}
