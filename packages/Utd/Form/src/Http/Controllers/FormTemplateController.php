<?php

namespace Utd\Form\Http\Controllers;

use App\Helpers\Common;
use Utd\Bd\Entities\Bd;
use App\Support\PackageHelper;
use App\Models\Agency;
use Illuminate\Http\Request;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;
use Utd\Form\Entities\FormField;
use Utd\Form\Entities\FormRequest;
use Utd\Form\Entities\FormSection;
use Utd\Form\Entities\FormTemplate;
use Utd\Form\Entities\CustomFieldWidget;
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
                $row->column(12, view('form::form-templates.index', compact('templates')));
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
            ->body(view('form::form-templates.show', compact('template')));
    }

    public function create()
    {
        $template = new FormTemplate();
        return view('form::form-templates.create', compact('template'));
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

        $template = FormTemplate::create([
            'title' => $request->title,
            'form_type' => $request->form_type,
            'description' => $request->description,
            'created_by' => auth()->id() ?? 1,
            'is_active' => true,
        ]);

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
                        $options = null;
                        $dataSource = null;

                        if (isset($fieldData['options_type'])) {
                            if ($fieldData['options_type'] === 'custom' && isset($fieldData['custom_options'])) {
                                $customOptions = $fieldData['custom_options'];
                                if (is_string($customOptions)) {
                                    $decoded = json_decode($customOptions, true);
                                    if (json_last_error() === JSON_ERROR_NONE) {
                                        $options = $decoded;
                                    } else {
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
                ->body(view('form::form-templates.edit', compact('template')));
        } catch (\ModelNotFoundException $e) {
            admin_error(__('Error'), __('Form Template not found.'));
            return redirect()->back();
        } catch (\Exception $e) {
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

        $allFieldNames = [];
        foreach ($request->sections as $sectionData) {
            if (isset($sectionData['fields'])) {
                foreach ($sectionData['fields'] as $fieldData) {
                    $name = trim($fieldData['name'] ?? '');
                    if ($name !== '') {
                        if (in_array($name, $allFieldNames)) {
                            return back()->withErrors(['duplicate_field' => "حقل '$name' مكرر داخل نفس النموذج."])->withInput();
                        }
                        $allFieldNames[] = $name;
                    }
                }
            }
        }

        $existingNames = FormField::whereHas('section', function ($q) use ($formTemplate) {
            $q->where('form_template_id', $formTemplate->id);
        })
            ->pluck('field_name')
            ->toArray();

        $duplicatesInDb = array_intersect($allFieldNames, $existingNames);

        if (!empty($duplicatesInDb)) {
            $duplicateList = implode(', ', $duplicatesInDb);
            return back()->withErrors(['duplicate_field' => "الأسماء التالية موجودة مسبقاً في هذا النموذج: $duplicateList"])->withInput();
        }

        $formTemplate->update([
            'title' => $request->title,
            'form_type' => $request->form_type,
            'description' => $request->description,
        ]);

        $formTemplate->sections()->delete();

        if ($request->has('sections')) {
            foreach ($request->sections as $sectionData) {
                $section = FormSection::create([
                    'form_template_id' => $formTemplate->id,
                    'title' => $sectionData['title'],
                    'section_order' => $sectionData['order'],
                    'is_visible' => true,
                    'can_not_delete' => $sectionData['can_not_delete']
                ]);

                if (isset($sectionData['fields'])) {
                    foreach ($sectionData['fields'] as $fieldData) {
                        $fieldType = $fieldData['type'] ?? 'text';
                        $options = null;
                        $dataSource = null;
                        $widgetId = null;
                        $widgetConfig = null;

                        if ($fieldType === 'custom' && isset($fieldData['widget_id'])) {
                            $widgetId = $fieldData['widget_id'];
                            $widget = CustomFieldWidget::find($widgetId);
                            if ($widget) {
                                $widgetConfig = $widget->default_config;
                            }
                        } elseif (in_array($fieldType, ['select', 'checkbox', 'radio'])) {
                            if (isset($fieldData['options_type'])) {
                                if ($fieldData['options_type'] === 'custom') {
                                    if (isset($fieldData['options']) && is_array($fieldData['options'])) {
                                        $processedOptions = [];

                                        foreach ($fieldData['options'] as $optionData) {
                                            if (isset($optionData['value']) && !empty($optionData['value'])) {
                                                $processedOptions[] = [
                                                    'label' => $optionData['label'] ?? [],
                                                    'value' => $optionData['value']
                                                ];
                                            }
                                        }

                                        $options = !empty($processedOptions) ? $processedOptions : null;
                                    }
                                } elseif ($fieldData['options_type'] === 'predefined') {
                                    if (isset($fieldData['data_source']) && !empty($fieldData['data_source'])) {
                                        $dataSource = $fieldData['data_source'];
                                    }
                                }
                            } elseif ($fieldData['options_type'] === 'predefined' && isset($fieldData['data_source'])) {
                                $dataSource = $fieldData['data_source'];
                            }
                        }

                        FormField::create([
                            'section_id' => $section->id,
                            'field_label' => $fieldData['label'],
                            'field_name' => trim($fieldData['name']),
                            'field_type' => $fieldType,
                            'widget_id' => $widgetId,
                            'widget_config' => $widgetConfig,
                            'placeholder' => $fieldData['placeholder'] ?? null,
                            'options' => $options,
                            'data_source' => $dataSource,
                            'is_required' => isset($fieldData['required']) && $fieldData['required'] == '1',
                            'is_enabled' => isset($fieldData['enabled']) && $fieldData['enabled'] == '1',
                            'field_order' => $fieldData['order'],
                            'can_not_delete' => $fieldData['can_not_delete']
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
        $type = $request->query('type') ?? $request->type;
        $linkToken = $request->query('token') ?? $request->token;
        $defaultLang = $request->query('lang') ?? app()->getLocale();

        if (empty($linkToken)) {
            return response()->view('form::forms.invalid', [
                'message' => 'Access denied. Please provide a valid token.',
            ], 403);
        }

        $token = PersonalAccessToken::findToken($linkToken);
        if (!$token) {
            return response()->view('form::forms.invalid', [
                'message' => 'Invalid or expired token.',
            ], 403);
        }

        $user = $token->tokenable;

        if (!$user) {
            return response()->view('form::forms.invalid', [
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

        return view('form::web-view.dynamic-form', compact('template', 'locale', 'linkToken', 'user'));
    }

    public function storeSubmission(Request $request, string $type)
    {
        $template = FormTemplate::where('form_type', $type)->firstOrFail();

        $data = $request->except('_token');

        $existingRequest = FormRequest::where('form_template_id', $template->id)
            ->where('submitted_by', $request->user_id)->where('status', '!=', 'rejected')
            ->first();
        if ($existingRequest) {
            return redirect()->route('forms.show.reqs', [
                'id' => $existingRequest->id,
                'token' => $request->token,
                'lang' => $request->lang,
                'user_id' => $request->user_id,
            ]);
        }

        $data = $this->processFiles($data);

        $fields = $request->fields ?? [];

        $save = FormRequest::create([
            'form_template_id' => $template->id,
            'submitted_by' => $request->user_id,
            'bd_id' => $request->bd_id,
            'name' => $request->agency_name ?? $request->bd_name,
            'whatsapp_number' => $request->whatsapp_number,
            'form_template_type' => $template?->form_type,
            'data' => json_encode($data, JSON_UNESCAPED_UNICODE),
            'country' => $request->country_id,
            'created_at' => now(),
        ]);
        if (!$save) {
            return response()->json([
                'success' => false,
                'message' => __('something got wrong'),
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => __('Request under review!'),
            'data' => [
                'order_id' => $save->id,
            ]
        ], 200);
    }

    protected function processFiles($input)
    {
        foreach ($input as $key => $value) {
            if ($value instanceof \Illuminate\Http\UploadedFile) {
                $input[$key] = Common::upload('data', $value);
            } elseif (is_array($value)) {
                $input[$key] = $this->processFiles($value);
            }
        }
        return $input;
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
        if (!PackageHelper::isInstalled('bd')) {
            return response()->json(['data' => []]);
        }
        $bds = Bd::where('name', 'like', "%{$query}%")
            ->orWhere('id', 'like', "%{$query}%")
            ->limit(6)
            ->get();

        $results = $bds->map(function ($bd) {
            $topAgencies = Agency::where('bd_id', $bd->id)
                ->withCount('members')
                ->orderByDesc('members_count')
                ->limit(3)
                ->get(['img', 'name', 'members_count'])
                ->map(function ($agency) {
                    $defaultAgencyImage = asset("images/agency-placeholder.jpg");
                    $agencyImage = getImagePath($agency->img) ?? $defaultAgencyImage;

                    if (!isImageExists($agencyImage)) {
                        $agencyImage = $defaultAgencyImage;
                    }

                    return [
                        'name' => $agency->name,
                        'members_count' => $agency->members_count,
                        'image' => $agencyImage,
                    ];
                });

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

            $defaultImage = asset("images/businessman-icon.jpg");
            $bdAvatar = getImagePath($bd->avatar) ?? $defaultImage;

            if (!isImageExists($bdAvatar)) {
                $bdAvatar = $defaultImage;
            }

            return [
                'id' => $bd->id,
                'name' => $bd->username,
                'phone' => $bd->phone_code . $bd->phone,
                'image' => $bdAvatar,
                'country' => $bd->country?->name,
                'is_default' => $bd->default,
                'bio' => __('form_bd_bio'),
                'years' => $since,
                'top_agencies' => $topAgencies,
            ];
        });

        return response()->json(['data' => $results]);
    }

    public function checkName(Request $request)
    {
        $name = trim($request->get('name'));

        if (!$name) {
            return response()->json([
                'status' => false,
                'message' => 'Name is required',
            ]);
        }

        $exists = FormField::where('field_name', $name)->exists();

        return response()->json([
            'status' => true,
            'exists' => $exists,
            'message' => $exists ? 'Name already exists' : 'Name is available',
        ]);
    }

    public function showReqs($id)
    {
        $formRequest = FormRequest::with('template')->findOrFail($id);

        $token = request('token');
        $lang = request('lang');
        $user_id = request('user_id');
        if ($lang) {
            app()->setLocale($lang);
        }
        return view('form::forms.show_request', compact('formRequest', 'token', 'lang', 'user_id'));
    }

    public function destroyReqs($id, Request $request)
    {
        $linkToken = $request->token;
        $defaultLang = $request->lang;
        if (empty($linkToken)) {
            return response()->view('form::forms.invalid', [
                'message' => 'Access denied. Please provide a valid token.',
            ], 403);
        }

        $token = PersonalAccessToken::findToken($linkToken);
        if (!$token) {
            return response()->view('form::forms.invalid', [
                'message' => 'Invalid or expired token.',
            ], 403);
        }

        $user = $token->tokenable;

        if (!$user) {
            return response()->view('form::forms.invalid', [
                'message' => 'User not found for this token.',
            ], 403);
        }

        $formRequest = FormRequest::with('template')->findOrFail($id);
        $type = $formRequest->template->form_type;
        $formRequest->delete();
        return redirect()->to(route('forms.showByType') . "?type={$type}&token={$linkToken}&lang={$defaultLang}");
    }

    public function removeRepetition()
    {
        DB::transaction(function () {
            $duplicates = FormTemplate::select('form_type')
                ->groupBy('form_type')
                ->havingRaw('COUNT(*) > 1')
                ->pluck('form_type');

            foreach ($duplicates as $formType) {
                $templates = FormTemplate::where('form_type', $formType)
                    ->orderBy('id')
                    ->get();

                $keep = $templates->shift();

                foreach ($templates as $template) {
                    foreach ($template->sections as $section) {
                        $section->fields()->delete();
                    }
                    $template->sections()->delete();
                    $template->delete();
                }
            }
        });
        return response()->json([
            'message' => 'remove repetition',
        ]);
    }
}
