<?php

namespace Modules\Form\Http\Controllers;


use App\Models\Bd;
use App\Models\User;
use App\Models\Agency;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\Models\ShippingAgency;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Admin\Services\UserService;
use App\Admin\Controllers\MainController;
use Modules\Form\Entities\FormRequest;
use Modules\Form\Services\FormRenderService;

class FormRequestController extends MainController
{
    protected $title;
    public $permission_name = 'form-request';

    public function __construct()
    {
        $this->title = __('requests_title');
    }


    public function index(Content $content)
    {
        $type = request()->get('type', 'host_agency');

        $buttons = [
            'host_agency' => __('Host Agency'),
            'bd_form' => __('BD Form'),
            'shipping_agency' => __('Shaping Agency'),
        ];

        $header = '<div style="margin-bottom:15px;">';
        foreach ($buttons as $key => $label) {
            $active = $type === $key ? 'background:var(--primary-color);color:white;' : '';
            $header .= "<a href='?type={$key}' class='btn tab-btn' style='margin-right:5px;{$active}'>{$label}</a>";
        }
        $header .= '</div>';
        $content->title(__('requests_title'));

        if (Admin::user()->can('type-switch-' . $this->permission_name) || Admin::user()->can('*')) {
            $content->row($header);
        }

        $content->row($this->getGrid($type)->render());

        return $content;
    }

    protected function getGrid($type)
    {
        $grid = new Grid(new FormRequest());

        $grid->model()
            ->with([
                'user',
                'user.country',
                'user.senderLevel',
                'user.receiverLevel',
                'user.packs' => fn($q) => $q->whereIn('type', [25])->where('is_used', true)->with('ware:id,value'),
                'template',
                'bd'
            ])
            ->where('form_template_type', $type)->orderByDesc('id');

        $grid->column('user', __('user'))
            ->display(function ($name) {

                $user = $this->user;
                if (!$user) {
                    return '';
                }

                return app(UserService::class)->adminUserAvatar($user);
            });

        $grid->column('name', __('name'));

        if ($type != 'bd_form' && $type != 'shipping_agency') {
            $grid->column('bd_id', __('Bd'))->display(function ($name) {
                if (request()->filled('_export_')) {
                    return $name;
                }
                if (!$this->bd) {
                    return '-';
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
        if ($type == 'bd_form') {
            $grid->column('country', __('country'))->display(fn($v) => $v ?? '-');
        }

        Admin::style('
            .grid-table .label-default {
                background-color: var(--primary-color) !important;
                color: #fff !important;
            }
        ');

        $grid->column('status', __('status'))->label([
            'pending' => 'default',
            'approved' => 'success',
            'rejected' => 'danger'
        ])->display(function ($status) {
            return __($status);
        });

        $grid->column('actions', __('Actions'))->display(function () {
            $approveUrl = admin_url("requests/{$this->id}/approve");
            $rejectUrl = admin_url("requests/{$this->id}/reject");
            $showUrl = admin_url('form-requests', $this->id);

            if ($this->status === 'rejected') {
                return '<span class="text-danger">' . __('Rejected') . '</span>';
            }

            if ($this->status === 'approved') {
                return '<span class="text-success">' . __('Approved') . '</span>';
            }

            $approveText = __('Approved');
            $rejectText = __('Reject');
            $viewText = __('Preview');

            $html = '';
            if (Admin::user()->can('show-' . $this->permission_name) || Admin::user()->can('*')) {
                $html .= <<<HTML
                    <a href="{$showUrl}" class="btn btn-info btn-sm me-1">
                        <i class="fa fa-eye"></i>
                    </a>
                HTML;
            }

            if (Admin::user()->can('approve-switch-' . $this->permission_name) || Admin::user()->can('*')) {
                $html .= <<<HTML
                    <button class="btn btn-success btn-sm approve-btn me-1" data-url="{$approveUrl}">
                        <i class="fa fa-check"></i>
                    </button>
                HTML;
            }

            if (Admin::user()->can('reject-switch-' . $this->permission_name) || Admin::user()->can('*')) {
                $html .= <<<HTML
                    <button class="btn btn-danger btn-sm reject-btn" data-url="{$rejectUrl}">
                        ✖
                    </button>
                HTML;
            }

            return $html;
        });

        Admin::script("
            function initFormRequestActions() {

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
                    button.addEventListener('click', function(e) {
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

                                Swal.fire({
                                    title: 'جاري التنفيذ...',
                                    allowOutsideClick: false,
                                    didOpen: () => Swal.showLoading()
                                });

                                sendRequest(url)
                                    .then(res => {
                                        Swal.close();

                                        if (res.success) {
                                            Swal.fire({
                                                title: res.message || messages.success[locale],
                                                icon: res.icon || 'success', // 💡 Use icon from backend
                                                timer: 2000,
                                                showConfirmButton: false
                                            });

                                            // Reload grid without full refresh
                                            $.pjax.reload('#pjax-container');
                                        } else {
                                            Swal.fire('خطأ', res.message || messages.error[locale], 'error');
                                        }
                                    })
                                    .catch((err) => {
                                        console.error('Fetch error:', err);
                                        Swal.fire('خطأ', messages.error[locale], 'error');
                                    });
                            }
                        });
                    });
                }

                document.querySelectorAll('.approve-btn').forEach(btn => handleAction(btn, 'approve'));
                document.querySelectorAll('.reject-btn').forEach(btn => handleAction(btn, 'reject'));
            }

            initFormRequestActions();

            $(document).off('pjax:end').on('pjax:end', function() {
                initFormRequestActions();
            });
        ");


        $grid->disableActions();
        $grid->disableCreateButton();

        return $grid;
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

            case 'shipping_agency':
                return $this->approveShapingAgency($request);

            default:
                admin_toastr(__('Unknown form type'), 'error');
                return redirect()->back();
        }
    }


    protected function approveHostAgency($request)
    {

        $owner = User::where('id', $request->submitted_by)->first();

        if (!$owner) {
            return response()->json([
                'success' => false,
                'message' => __('user_not_found')
            ], 404);
        }

        if ($this->checkUserAlreadyOwnsEntity($owner, Agency::class)) {
            return response()->json([
                'success' => false,
                'key' => 'user_already_has_agency',
                'message' => __('user_already_has_agency'),
            ], 400);
        }

        Agency::create([
            'name' => $request->name,
            'phone' => $request->whatsapp_number,
            'app_owner_id' => $owner->id,
            'bd_id' => $request->bd_id,
            'country_id' => $owner->country_id,
        ]);
        $request->update(['status' => 'approved']);

        // return response()->json(['success' => true, 'message' => __('تمت الموافقة بنجاح')]);
        return response()->json([
            'success' => true,
            'message' => __('تمت الموافقة بنجاح'),
        ]);
    }

    protected function approveBdForm($request)
    {
        $data = $request->data;

        if (is_string($data)) {
            $data = json_decode(trim($data, '"'), true);
        }
        $phoneUser = User::where('id', $request->submitted_by)->first();

        if (!$phoneUser) {
            return response()->json([
                'success' => false,
                'message' => __('user_not_found')
            ], 404);
        }

        if ($this->checkUserAlreadyOwnsEntity($phoneUser, Bd::class)) {
            return response()->json([
                'success' => false,
                'key' => 'user_already_has_bd',
                'message' => __('user_already_has_bd'),
            ], 400);
        }

        Bd::create([
            'username' => $request->name,
            'app_id' => $phoneUser->id,
            'country_id' => $phoneUser->country_id,
            'password' => $data['password'] ?? 123456789,
        ]);

        $request->update(['status' => 'approved']);

        //  return response()->json(['success' => true, 'message' => __('تمت الموافقة بنجاح')]);
        return response()->json([
            'success' => true,
            'message' => __('تمت الموافقة بنجاح'),
        ]);
    }

    protected function approveShapingAgency($request)
    {
        $owner = User::where('id', $request->submitted_by)->first();

        if (!$owner) {
            return response()->json([
                'success' => false,
                'message' => __('user_not_found')
            ], 404);
        }

        if ($this->checkUserAlreadyOwnsEntity($owner, ShippingAgency::class)) {

            return response()->json([
                'success' => false,
                'key' => 'user_already_has_shipping_agency',
                'message' => __('user_already_has_shipping_agency'),
            ], 400);
        }

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


    protected function checkUserAlreadyOwnsEntity(User $user, string $modelClass): bool
    {
        if (!class_exists($modelClass)) {
            throw new \InvalidArgumentException("Invalid model class: {$modelClass}");
        }

        $model = new $modelClass;
        $table = $model->getTable();

        if (!\Schema::hasTable($table)) {
            throw new \RuntimeException("Table for model {$modelClass} does not exist.");
        }

        $columns = \Schema::getColumnListing($table);
        $validFields = array_intersect(['app_owner_id', 'app_id'], $columns);

        foreach ($validFields as $field) {
            if ($modelClass::where($field, $user->id)->exists()) {
                return true;
            }
        }

        return false;
    }

    public function reject($id)
    {
        $request = FormRequest::findOrFail($id);
        $request->status = 'rejected';
        $request->save();
        return response()->json([
            'success' => true,
            'message' => __('admin.rejected_success'),
            'icon' => 'success',
        ]);
        admin_toastr(__('rejected_message'), 'error');
        return redirect()->back();
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
        return parent::show(
            $id,
            $content
                ->title(trans(''))
                ->body($this->detail($id))
        );
    }

    protected function detail($id)
    {
        $show = new Show(FormRequest::findOrFail($id));
        $show->panel()->tools(function ($tools) {
            $tools->disableEdit();
            $tools->disableList();
            $tools->disableDelete();
        });

        $renderer = new FormRenderService();

        $show->field('data', __('dodo'))->as(function ($jsonData) use ($renderer) {
            $data = json_decode($jsonData, true);
            return $renderer->renderFormData($data);
        })->unescape();

        return $show;
    }
}
