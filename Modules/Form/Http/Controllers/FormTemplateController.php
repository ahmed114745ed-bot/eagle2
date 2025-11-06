<?php

namespace  Modules\Form\Http\Controllers;

use App\Helpers\Common;
use App\Models\Bd;

use App\Models\Agency;
use App\Models\FormRequest;
use Illuminate\Http\Request;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;
use Modules\Form\Entities\FormField;
use Modules\Form\Entities\FormSection;
use Modules\Form\Entities\FormTemplate;
use Encore\Admin\Auth\Permission;
use Carbon\Carbon;

class FormTemplateController extends Controller
{
    public $permission_name = 'templates-form';
    public function index(Content $content)
    {
        if (!Admin::user()->can('*')) {
            Permission::check('browse-' . $this->permission_name);
        }

        $templates = FormTemplate::with('sections.fields')->latest()->get();
        return $content
            ->title(__(''))
            ->description(__(''))
            ->row(function ($row) use ($templates) {
                $row->column(12, view('Form::form-templates.index', compact('templates')));
            });
    }

    public function show($id, Content $content)
    {
        if (!Admin::user()->can('*')) {
            Permission::check('show-' . $this->permission_name);
        }
        $template = FormTemplate::with(['sections.fields'])->findOrFail($id);
        return $content
            ->title(__('Preview'))
            ->body(view('Form::form-templates.show', compact('template')));
    }

    public function create()
    {

        $template = new FormTemplate();
        return view('Form::form-templates.create', compact('template'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|array',
            'title.*' => 'required|string',
            'form_type' => 'required|string',
            'description' => 'nullable|array',
            'sections' => 'required|array',
        ]);

        // Create form template
        $template = FormTemplate::create([
            'title' => $request->title,
            'form_type' => $request->form_type,
            'description' => $request->description,
            'created_by' => auth()->id() ?? 1,
            'is_active' => true,
        ]);

        // Create sections and fields
        if ($request->has('sections')) {
            foreach ($request->sections as $sectionData) {
                $section = FormSection::create([
                    'form_template_id' => $template->id,
                    'title' => $sectionData['title'],
                    'section_order' => $sectionData['order'],
                    'is_visible' => true,
                ]);

                if (isset($sectionData['fields'])) {
                    foreach ($sectionData['fields'] as $fieldData) {
                        // Handle options based on type
                        $options = null;
                        $dataSource = null;

                        if (isset($fieldData['options_type'])) {
                            if ($fieldData['options_type'] === 'custom' && isset($fieldData['custom_options'])) {
                                // Parse custom options (either JSON or line-separated)
                                $customOptions = $fieldData['custom_options'];
                                if (is_string($customOptions)) {
                                    // Try to parse as JSON first
                                    $decoded = json_decode($customOptions, true);
                                    if (json_last_error() === JSON_ERROR_NONE) {
                                        $options = $decoded;
                                    } else {
                                        // Parse as line-separated values
                                        $lines = array_filter(array_map('trim', explode("\n", $customOptions)));
                                        $options = array_combine($lines, $lines);
                                    }
                                }
                            } elseif ($fieldData['options_type'] === 'predefined' && isset($fieldData['data_source'])) {
                                $dataSource = $fieldData['data_source'];
                            }
                        }

                        FormField::create([
                            'section_id' => $section->id,
                            'field_label' => $fieldData['label'],
                            'field_name' => $fieldData['name'],
                            'field_type' => $fieldData['type'],
                            'widget_id' => $fieldData['widget_id'] ?? null,
                            'widget_config' => isset($fieldData['widget_config']) ? json_decode($fieldData['widget_config'], true) : null,
                            'placeholder' => $fieldData['placeholder'] ?? null,
                            'options' => $options,
                            'data_source' => $dataSource,
                            'is_required' => isset($fieldData['required']) && $fieldData['required'] == '1',
                            'is_enabled' => isset($fieldData['enabled']) && $fieldData['enabled'] == '1',
                            'field_order' => $fieldData['order'],
                        ]);
                    }
                }
            }
        }
        admin_success(__('Form template created successfully!'));
        return redirect(admin_url('form-templates'));
    }

    public function edit($id, Content $content)
    {
        if (!Admin::user()->can('*')) {
            Permission::check('edit-' . $this->permission_name);
        }
        try {
            $template = FormTemplate::with(['sections.fields.widget'])->findOrFail($id);

            return $content
                ->title(__('Edit Form Template'))
                ->body(view('Form::form-templates.edit', compact('template')));
        } catch (\ModelNotFoundException $e) {
            admin_error(__('Error'), __('Form Template not found.'));
            return redirect()->back();
        } catch (\Exception $e) {
            \Log::error('Form Template Edit Error: ' . $e->getMessage(), ['id' => $id]);

            admin_error(__('Unexpected Error'), __('Something went wrong while loading the form template.'));
            return redirect()->back();
        }
    }

    public function update(Request $request, FormTemplate $formTemplate)
    {
        $validated = $request->validate([
            'title' => 'required|array',
            'title.*' => 'required|string',
            'form_type' => 'required|string',
            'description' => 'nullable|array',
            'sections' => 'required|array',
        ]);
        // Update form template basic info
        $formTemplate->update([
            'title' => $request->title,
            'form_type' => $request->form_type,
            'description' => $request->description,
        ]);

        // Delete old sections and fields (cascade will handle fields)
        $formTemplate->sections()->delete();

        // Create new sections and fields
        if ($request->has('sections')) {
            foreach ($request->sections as $sectionData) {
                // Create section
                $section = FormSection::create([
                    'form_template_id' => $formTemplate->id,
                    'title' => $sectionData['title'],
                    'section_order' => $sectionData['order'],
                    'is_visible' => true,
                    'can_not_delete' =>$sectionData['can_not_delete']
                ]);

                // Create fields for this section
                if (isset($sectionData['fields'])) {
                    foreach ($sectionData['fields'] as $fieldData) {
                        $fieldType = $fieldData['type'] ?? 'text';
                        $options = null;
                        $dataSource = null;
                        $widgetId = null;
                        $widgetConfig = null;

                        // ============================================
                        // Handle Custom Widget Fields
                        // ============================================
                        if ($fieldType === 'custom' && isset($fieldData['widget_id'])) {
                            $widgetId = $fieldData['widget_id'];
                            $widget = \Modules\Form\Entities\CustomFieldWidget::find($widgetId);
                            if ($widget) {
                                $widgetConfig = $widget->default_config;
                            }
                        }
                        // ============================================
                        // Handle Select/Checkbox/Radio Fields
                        // ============================================
                        elseif (in_array($fieldType, ['select', 'checkbox', 'radio'])) {
                            // Check if options_type is provided
                            if (isset($fieldData['options_type'])) {

                                // Custom Options
                                if ($fieldData['options_type'] === 'custom') {
                                    // Process custom options from the form
                                    // Format: sections[X][fields][Y][options][Z][label][locale] & [value]
                                    if (isset($fieldData['options']) && is_array($fieldData['options'])) {
                                        $processedOptions = [];

                                        foreach ($fieldData['options'] as $optionData) {
                                            if (isset($optionData['value']) && !empty($optionData['value'])) {
                                                $processedOptions[] = [
                                                    'label' => $optionData['label'] ?? [],  // Multi-language labels
                                                    'value' => $optionData['value']
                                                ];
                                            }
                                        }

                                        $options = !empty($processedOptions) ? $processedOptions : null;
                                    }
                                }
                                // Predefined Data Source
                                elseif ($fieldData['options_type'] === 'predefined') {
                                    if (isset($fieldData['data_source']) && !empty($fieldData['data_source'])) {
                                        $dataSource = $fieldData['data_source'];
                                    }
                                }
                            } elseif ($fieldData['options_type'] === 'predefined' && isset($fieldData['data_source'])) {
                                $dataSource = $fieldData['data_source'];
                            }
                        }

                        // ============================================
                        // Create Field Record
                        // ============================================
                        FormField::create([
                            'section_id' => $section->id,
                            'field_label' => $fieldData['label'],
                            'field_name' => $fieldData['name'],
                            'field_type' => $fieldType,
                            'widget_id' => $widgetId,
                            'widget_config' => $widgetConfig,
                            'placeholder' => $fieldData['placeholder'] ?? null,
                            'options' => $options,
                            'data_source' => $dataSource,
                            'is_required' => isset($fieldData['required']) && $fieldData['required'] == '1',
                            'is_enabled' => isset($fieldData['enabled']) && $fieldData['enabled'] == '1',
                            'field_order' => $fieldData['order'],
                            'can_not_delete' =>  $fieldData['can_not_delete']
                        ]);
                    }
                }
            }
        }

        admin_success(__('Form template updated successfully!'));
        return redirect(admin_url('form-templates'));
    }


    public function destroy(FormTemplate $formTemplate)
    {
        $formTemplate->delete();
        return redirect(admin_url('form-templates'))
            ->with('success', __('Form template deleted successfully!'));
    }

    public function showByType(Request $request)
    {

        $type = $request->query('type');
        $linkToken = $request->query('token');
        $defaultLang = $request->query('lang') ?? app()->getLocale();

        if (empty($linkToken)) {
            return response()->view('Form::forms.invalid', [
                'message' => 'Access denied. Please provide a valid token.',
            ], 403);
        }

        $token = PersonalAccessToken::findToken($linkToken);
        if (!$token) {
            return response()->view('Form::forms.invalid', [
                'message' => 'Invalid or expired token.',
            ], 403);
        }

        $user = $token->tokenable;

        if (!$user) {
            return response()->view('Form::forms.invalid', [
                'message' => 'User not found for this token.',
            ], 403);
        }

        $locale = $request->header('Accept-Language', $defaultLang);
        $locale = in_array($locale, ['ar', 'en', 'tr', 'hi']) ? $locale : $defaultLang;
        app()->setLocale($locale);

        $template = FormTemplate::with(['sections.fields'])
            ->where('form_type', $type)
            ->where('is_active', true)
            ->firstOrFail();

        return view('Form::web-view.dynamic-form', compact('template', 'locale', 'linkToken', 'user'));

    }



    public function storeSubmission(Request $request, string $type)
    {
        $template = FormTemplate::where('form_type', $type)->firstOrFail();
        $data = $request->except('_token');
        foreach ($request->file() as $key => $fileInput) {
            if (is_array($fileInput)) {
                $storedFiles = [];
                foreach ($fileInput as $file) {
                    if ($file && $file->isValid()) {
                        $storedFiles[] = Common::upload('data', $file);
                    }
                }
                $data[$key] = $storedFiles;
            } elseif ($fileInput instanceof \Illuminate\Http\UploadedFile && $fileInput->isValid()) {
                $path = Common::upload('data', $fileInput);
                $data[$key] = $path;
            } else {
                info("Unexpected file input type for {$key}");
                info('Type: ' . gettype($fileInput));
                info('Class: ' . (is_object($fileInput) ? get_class($fileInput) : 'not object'));
            }
        }
        FormRequest::create([
            'form_template_id' => $template->id,
            'submitted_by' => $request->user_id,
            'bd_id' => $request->bd_id,
            'name' => $request->agency_name ?? $request->bd_name,
            'whatsapp_number' => $request->whatsapp_number,
            'form_template_type' => $template->form_type,
            'data' => json_encode($data),
            'created_at' => now(),
        ]);


        return response()->json([
            'success' => true,
            'message' => __('Form submitted successfully!'),
        ], 200);
    }

    public function getTranslations(Request $request)
    {
        $locale = $request->get('locale', 'en');
        $templateId = $request->get('id', 'en');

        $template = FormTemplate::with('sections.fields')->findOrFail($templateId);

        return response()->json([
            'title' => $template->getTranslation('title', $locale),
            'sections' => $template->sections->map(function ($section) use ($locale) {
                return [
                    'title' => $section->getTranslation('title', $locale),
                    'fields' => $section->fields->map(function ($field) use ($locale) {
                        $options = null;
                        if ($field->options) {
                            $optionsArray = is_array($field->options) ? $field->options : json_decode($field->options, true);
                            $options = collect($optionsArray)->map(function ($value) use ($locale) {
                                if (is_array($value)) {
                                    return $value[$locale] ?? $value['en'] ?? $value;
                                }
                                return $value;
                            })->toArray();
                        }

                        return [
                            'label' => $field->getTranslation('field_label', $locale),
                            'placeholder' => $field->getTranslation('placeholder', $locale),
                            'options' => $options,
                        ];
                    })
                ];
            })
        ]);
    }


    public function search(Request $request)
    {
        $query = $request->get('query');
        $lang = $request->get('lang') ?? app()->getLocale();

        app()->setLocale($lang);
        $bds = Bd::where('name', 'like', "%{$query}%")
            ->orWhere('id', 'like', "%{$query}%")
            ->limit(6)
            ->get();

        $results = $bds->map(function ($bd) {
            $topAgencies = Agency::where('bd_id', $bd->id)
                ->withCount('members')
                ->orderByDesc('members_count')
                ->limit(3)
                ->get(['img','name', 'members_count']);
        
        $createdAt = Carbon::parse($bd->created_at);
        $now = now();
        $diffInYears = $createdAt->diffInYears($now);
        $diffInMonths = $createdAt->diffInMonths($now);
        $diffInDays = $createdAt->diffInDays($now);
    
        if ($diffInYears >= 1) {
            $since = __('Works since :value years', ['value' => $diffInYears]);
        } elseif ($diffInMonths >= 1) {
            $since = __('Works since :value months', ['value' => $diffInMonths]);
        } else {
            $since = __('Works since :value days', ['value' => $diffInDays]);
        }
        
            return [
                'id' => $bd->id,
                'name' => $bd->username,
                'phone' => $bd->phone_code . $bd->phone,
                'image' => $bd->avatar,
                'country' => $bd->country?->name,
                'is_default' => $bd->default,
                'bio' =>__('form_bd_bio'),
                'years' => $since,
                'top_agencies' => $topAgencies,
            ];
        });

        return response()->json(['data' => $results]);
    }
}
