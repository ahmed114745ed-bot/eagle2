<?php

namespace  Modules\Form\Http\Controllers;


use App\Models\Bd;
use App\Models\User;
use App\Models\Agency;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\Models\FormRequest;
use Encore\Admin\Widgets\Box;
use App\Models\ShippingAgency;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Admin\Services\UserService;
use App\Admin\Controllers\MainController;
use Encore\Admin\Controllers\AdminController;

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
            'host_agency'    => __('Host Agency'),
            'bd_form'        => __('BD Form'),
            'shipping_agency' => __('Shaping Agency'),
        ];

        $header = '<div style="margin-bottom:15px;">';
        foreach ($buttons as $key => $label) {
            $active = $type === $key ? 'background:#1890ff;color:white;' : 'background:#f5f5f5;';
            $header .= "<a href='?type={$key}' class='btn btn-sm' style='margin-right:5px;{$active}'>{$label}</a>";
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

        if ($type != 'bd_form' && $type != 'shipping_agency') {
            $grid->column('bd_id', __('Bd'))->display(function ($name) {
                if (request()->filled('_export_')) {
                    return $name;
                }
                if (!$this->bd) {
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
        if ($type == 'bd_form') {
            $grid->column('country', __('country'))->display(fn($v) => $v ?? '-');
        }
        $grid->column('status', __('status'))->label([
            'pending' => 'default',
            'approved' => 'success',
            'rejected' => 'danger'
        ]);


        $grid->column('actions', __('Actions'))->display(function () {
            $approveUrl = admin_url("requests/{$this->id}/approve");
            $rejectUrl  = admin_url("requests/{$this->id}/reject");
            $showUrl = admin_url('form-requests', $this->id);

            if ($this->status === 'rejected') {
                return '<span class="text-danger">' . __('Rejected') . '</span>';
            }

            if ($this->status === 'approved') {
                return '<span class="text-success">' . __('Approved') . '</span>';
            }

            $approveText = __('Approved');
            $rejectText  = __('Reject');
            $viewText    = __('View');

            // return <<<HTML
            // if (Admin::user()->can('charge-switch-' . $permission) || Admin::user()->can('*')) {
            //        <a href="{$showUrl}" class="btn btn-info btn-sm me-1">
            //             <i class="fa fa-eye"></i> {$viewText}
            //         </a>}
            //         if (Admin::user()->can('charge-switch-' . $permission) || Admin::user()->can('*')) {
            //     <button class="btn btn-success btn-sm approve-btn" data-url="{$approveUrl}">{$approveText}</button>
            //         }
            //         if (Admin::user()->can('charge-switch-' . $permission) || Admin::user()->can('*')) {
            //     <button class="btn btn-danger btn-sm reject-btn" data-url="{$rejectUrl}">✖ {$rejectText}</button>
            //         }
            // HTML;

            $html = '';

            if (Admin::user()->can('show-' . $this->permission_name) || Admin::user()->can('*')) {
                $html .= <<<HTML
                    <a href="{$showUrl}" class="btn btn-info btn-sm me-1">
                        <i class="fa fa-eye"></i> {$viewText}
                    </a>
                    HTML;
            }

            if (Admin::user()->can('approve-switch-' . $this->permission_name) || Admin::user()->can('*')) {
                $html .= <<<HTML
                    <button class="btn btn-success btn-sm approve-btn" data-url="{$approveUrl}">
                        {$approveText}
                    </button>
                    HTML;
            }

            if (Admin::user()->can('reject-switch-' . $this->permission_name) || Admin::user()->can('*')) {
                $html .= <<<HTML
                    <button class="btn btn-danger btn-sm reject-btn" data-url="{$rejectUrl}">
                        ✖ {$rejectText}
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
                        type: 'question',
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
                                didOpen: () => {
                                    Swal.showLoading()
                                }
                            });
                            sendRequest(url).then(res => {
                                 Swal.close();
                                console.group('Form Request Action Response');
                                console.log('Request URL:', url);
                                console.log('Response:', res);
                                console.groupEnd();
    
                                if (res.success) {
                                    Swal.fire({
                                        title: res.message || messages.success[locale],
                                        type: 'success',
                                        timer: 2000,
                                        showConfirmButton: false
                                    });
    
                                    // إعادة تحميل محتوى الـ grid بدون refresh
                                    $.pjax.reload('#pjax-container');
                                } else {
                                    Swal.fire('خطأ', res.message || messages.error[locale], 'error');
                                }
                            }).catch((err) => {
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
    
        // يعمل أول مرة عند تحميل الصفحة
        initFormRequestActions();
    
        // يعمل بعد كل تحديث PJAX (reload)
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
                'key'     => 'user_already_has_agency',
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

        return response()->json(['success' => true, 'message' => __('تمت الموافقة بنجاح')]);
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
                'key'     => 'user_already_has_bd',
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

        return response()->json(['success' => true, 'message' => __('تمت الموافقة بنجاح')]);
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

        if ($this->checkUserAlreadyOwnsEntity($owner,  ShippingAgency::class)) {

            return response()->json([
                'success' => false,
                'key'     => 'user_already_has_shipping_agency',
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
        if (! class_exists($modelClass)) {
            throw new \InvalidArgumentException("Invalid model class: {$modelClass}");
        }

        $model = new $modelClass;
        $table = $model->getTable();

        if (! \Schema::hasTable($table)) {
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
                ->title(trans('appear-charger-agency'))
            ->body($this->detail($id))
        );
    }

    protected function detail($id)
    {
        $show = new Show(FormRequest::findOrFail($id));
        $formRequest = FormRequest::findOrFail($id);
    
        $show->field('user.name', __('Name'));
        $show->field('bd_id', __('BD ID'));
        $show->field('agency_name', __('Agency Name'));
        $show->field('whatsapp_number', __('WhatsApp Number'));
        $show->field('country', __('Country'));
        $show->field('form_template_type', __('Form Type'));
        $show->field('status', __('Status'));
    
        // استخدم $this داخل closure
        $controller = $this;
        
        $show->field('data', __('Form Data'))->as(function ($data) use ($formRequest, $controller) {
            $formData = json_decode($data, true);
            if (!$formData || !is_array($formData)) {
                return '<p class="text-muted">No data available</p>';
            }
    
            // Get the form template with all fields
            $template = \Modules\Form\Entities\FormTemplate::where('form_type', $formRequest->form_template_type)
                ->with(['sections.fields'])
                ->first();
    
            if (!$template) {
                return $controller->renderSimpleData($formData);
            }
    
            // Build fields map with their configurations
            $fieldsMap = [];
            foreach ($template->sections as $section) {
                foreach ($section->fields as $field) {
                    $fieldsMap[$field->field_name] = [
                        'label' => $field->field_label,
                        'type' => $field->field_type,
                        'options' => $field->options,
                        'data_source' => $field->data_source,
                        'widget_id' => $field->widget_id,
                        'section_title' => $section->title,
                    ];
                }
            }
    
            $html = '<div class="form-data-display">';
            
            // Group data by sections
            $currentSection = null;
            foreach ($formData as $fieldName => $value) {
                if (!isset($fieldsMap[$fieldName])) {
                    continue; // Skip unknown fields
                }
    
                $fieldConfig = $fieldsMap[$fieldName];
                $sectionTitle = $fieldConfig['section_title'];
                $currentLocale = app()->getLocale();
    
                // Display section header
                if ($currentSection !== $sectionTitle) {
                    if ($currentSection !== null) {
                        $html .= '</div>'; // Close previous section
                    }
                    $currentSection = $sectionTitle;
                    $sectionName = is_array($sectionTitle) ? ($sectionTitle[$currentLocale] ?? $sectionTitle['en'] ?? '') : $sectionTitle;
                    $html .= '<div class="section-group" style="margin-bottom: 20px; padding: 15px; background: #f8f9fa; border-radius: 8px;">';
                    $html .= '<h4 style="color: #2c3e50; margin-bottom: 15px; border-bottom: 2px solid #3498db; padding-bottom: 8px;">' . htmlspecialchars($sectionName) . '</h4>';
                }
    
                // Get field label
                $label = $fieldConfig['label'];
                $labelText = is_array($label) ? ($label[$currentLocale] ?? $label['en'] ?? $fieldName) : $label;
    
                // Render field based on type
                $html .= '<div class="field-row" style="margin-bottom: 12px; padding: 8px; background: white; border-radius: 4px;">';
                $html .= '<strong style="color: #34495e; display: inline-block; min-width: 200px;">' . htmlspecialchars($labelText) . ':</strong> ';
                $html .= $controller->renderFieldValue($value, $fieldConfig);
                $html .= '</div>';
            }
    
            if ($currentSection !== null) {
                $html .= '</div>'; // Close last section
            }
    
            $html .= '</div>';
    
            return $html;
        })->unescape();
    
        return $show;
    }
    
    /**
     * Render field value based on field type
     */
    public function renderFieldValue($value, $fieldConfig)
    {
        $type = $fieldConfig['type'];
        $currentLocale = app()->getLocale();
    
        // Handle empty values
        if ($value === null || $value === '' || (is_array($value) && empty($value))) {
            return '<span class="text-muted" style="color: #95a5a6;">—</span>';
        }
    
        switch ($type) {
            case 'file':
                return $this->renderFileField($value);
    
            case 'select':
            case 'radio':
                return $this->renderSelectField($value, $fieldConfig, $currentLocale);
    
            case 'checkbox':
                return $this->renderCheckboxField($value, $fieldConfig, $currentLocale);
    
            case 'date':
                return $this->renderDateField($value);
    
            case 'textarea':
                return $this->renderTextareaField($value);
    
            case 'email':
                return $this->renderEmailField($value);
    
            case 'tel':
                return $this->renderPhoneField($value);
    
            case 'custom':
                return $this->renderCustomWidget($value, $fieldConfig);
    
            case 'number':
                return '<span style="font-weight: 500;">' . number_format($value) . '</span>';
    
            default: // text and others
                return '<span>' . htmlspecialchars($value) . '</span>';
        }
    }
    
    /**
     * Render file field (images and documents)
     */
    public function renderFileField($value)
    {
        if (!$value) return '<span class="text-muted">—</span>';
    
        $files = is_array($value) ? $value : [$value];
        $html = '<div class="file-preview" style="display: flex; flex-wrap: wrap; gap: 10px;">';
    
        foreach ($files as $file) {
            $url = asset('storage/' . $file);
            $extension = pathinfo($file, PATHINFO_EXTENSION);
    
            if (in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'])) {
                // Image preview
                $html .= '<div style="position: relative;">';
                $html .= '<a href="' . $url . '" target="_blank">';
                $html .= '<img src="' . $url . '" style="max-width: 150px; max-height: 150px; border-radius: 4px; border: 1px solid #ddd;"/>';
                $html .= '</a>';
                $html .= '</div>';
            } else {
                // Document link
                $icon = $this->getFileIcon($extension);
                $html .= '<a href="' . $url . '" target="_blank" style="padding: 8px 12px; background: #ecf0f1; border-radius: 4px; text-decoration: none; color: #2c3e50;">';
                $html .= '<i class="fa fa-' . $icon . '"></i> ' . basename($file);
                $html .= '</a>';
            }
        }
    
        $html .= '</div>';
        return $html;
    }
    
    /**
     * Render select/radio field with option label
     */
    public function renderSelectField($value, $fieldConfig, $locale)
    {
        if (!$value) return '<span class="text-muted">—</span>';
    
        // Check if using predefined data source
        if (!empty($fieldConfig['data_source'])) {
            return '<span style="padding: 4px 10px; background: #3498db; color: white; border-radius: 3px;">' . htmlspecialchars($value) . '</span>';
        }
    
        // Check custom options
        if (!empty($fieldConfig['options']) && is_array($fieldConfig['options'])) {
            foreach ($fieldConfig['options'] as $option) {
                if (isset($option['value']) && $option['value'] == $value) {
                    $label = $option['label'];
                    $labelText = is_array($label) ? ($label[$locale] ?? $label['en'] ?? $value) : $label;
                    return '<span style="padding: 4px 10px; background: #3498db; color: white; border-radius: 3px;">' . htmlspecialchars($labelText) . '</span>';
                }
            }
        }
    
        return '<span style="padding: 4px 10px; background: #3498db; color: white; border-radius: 3px;">' . htmlspecialchars($value) . '</span>';
    }
    
    /**
     * Render checkbox field (multiple values)
     */
    public function renderCheckboxField($value, $fieldConfig, $locale)
    {
        $values = is_array($value) ? $value : [$value];
        if (empty($values)) return '<span class="text-muted">—</span>';
    
        $html = '<div style="display: flex; flex-wrap: wrap; gap: 6px;">';
    
        foreach ($values as $val) {
            if (!empty($fieldConfig['options']) && is_array($fieldConfig['options'])) {
                foreach ($fieldConfig['options'] as $option) {
                    if (isset($option['value']) && $option['value'] == $val) {
                        $label = $option['label'];
                        $labelText = is_array($label) ? ($label[$locale] ?? $label['en'] ?? $val) : $label;
                        $html .= '<span style="padding: 4px 10px; background: #2ecc71; color: white; border-radius: 3px; font-size: 13px;">' . htmlspecialchars($labelText) . '</span>';
                        continue 2;
                    }
                }
            }
            $html .= '<span style="padding: 4px 10px; background: #2ecc71; color: white; border-radius: 3px; font-size: 13px;">' . htmlspecialchars($val) . '</span>';
        }
    
        $html .= '</div>';
        return $html;
    }
    
    /**
     * Render date field
     */
    public function renderDateField($value)
    {
        try {
            $date = \Carbon\Carbon::parse($value);
            return '<span style="color: #16a085;"><i class="fa fa-calendar"></i> ' . $date->format('Y-m-d') . '</span>';
        } catch (\Exception $e) {
            return '<span>' . htmlspecialchars($value) . '</span>';
        }
    }
    
    /**
     * Render textarea field
     */
    public function renderTextareaField($value)
    {
        return '<div style="padding: 10px; background: #f8f9fa; border-left: 3px solid #3498db; border-radius: 4px; white-space: pre-wrap;">' . htmlspecialchars($value) . '</div>';
    }
    
    /**
     * Render email field
     */
    public function renderEmailField($value)
    {
        return '<a href="mailto:' . htmlspecialchars($value) . '" style="color: #3498db;"><i class="fa fa-envelope"></i> ' . htmlspecialchars($value) . '</a>';
    }
    
    /**
     * Render phone field
     */
    public function renderPhoneField($value)
    {
        return '<a href="tel:' . htmlspecialchars($value) . '" style="color: #27ae60;"><i class="fa fa-phone"></i> ' . htmlspecialchars($value) . '</a>';
    }
    
    /**
     * Render custom widget field
     */
    public function renderCustomWidget($value, $fieldConfig)
    {
        if (!is_array($value)) {
            return '<span>' . htmlspecialchars($value) . '</span>';
        }
    
        $html = '<div style="padding: 10px; background: #fff3cd; border-left: 3px solid #ffc107; border-radius: 4px;">';
        $html .= '<ul style="list-style: none; padding: 0; margin: 0;">';
        
        foreach ($value as $key => $val) {
            if (is_array($val)) {
                $html .= '<li style="margin-bottom: 8px;"><strong>' . htmlspecialchars($key) . ':</strong>';
                $html .= '<ul style="padding-left: 20px;">';
                foreach ($val as $k => $v) {
                    $html .= '<li>' . htmlspecialchars($k) . ': ' . htmlspecialchars($v) . '</li>';
                }
                $html .= '</ul></li>';
            } else {
                $html .= '<li style="margin-bottom: 4px;"><strong>' . htmlspecialchars($key) . ':</strong> ' . htmlspecialchars($val) . '</li>';
            }
        }
        
        $html .= '</ul></div>';
        return $html;
    }
    
    /**
     * Fallback: Render simple data for unknown templates
     */
    public function renderSimpleData($data)
    {
        $html = '<div class="simple-data-display">';
        
        foreach ($data as $key => $value) {
            $html .= '<div style="margin-bottom: 10px; padding: 8px; background: #f8f9fa; border-radius: 4px;">';
            $html .= '<strong>' . htmlspecialchars(str_replace('_', ' ', ucfirst($key))) . ':</strong> ';
            
            if (is_array($value)) {
                $html .= '<pre style="background: white; padding: 8px; border-radius: 4px; margin-top: 5px;">' . json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . '</pre>';
            } elseif (preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $value)) {
                $url = asset('storage/' . $value);
                $html .= '<br><img src="' . $url . '" style="max-width: 200px; max-height: 200px; border-radius: 4px; margin-top: 5px;"/>';
            } else {
                $html .= '<span>' . htmlspecialchars($value) . '</span>';
            }
            
            $html .= '</div>';
        }
        
        $html .= '</div>';
        return $html;
    }
    
    /**
     * Get icon for file type
     */
    public function getFileIcon($extension)
    {
        $icons = [
            'pdf' => 'file-pdf',
            'doc' => 'file-word',
            'docx' => 'file-word',
            'xls' => 'file-excel',
            'xlsx' => 'file-excel',
            'ppt' => 'file-powerpoint',
            'pptx' => 'file-powerpoint',
            'zip' => 'file-archive',
            'rar' => 'file-archive',
            'txt' => 'file-text',
        ];
    
        return $icons[$extension] ?? 'file';
    }
}
