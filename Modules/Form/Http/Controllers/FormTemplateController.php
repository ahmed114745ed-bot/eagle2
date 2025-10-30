<?php

namespace  Modules\Form\Http\Controllers;

use App\Http\Controllers\Controller;

use App\Models\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Form\Entities\FormField;
use Modules\Form\Entities\FormSection;
use Modules\Form\Entities\FormTemplate;
use Encore\Admin\Layout\Content;

class FormTemplateController extends Controller
{

    public function index(Content $content)
    {
        $templates = FormTemplate::with('sections.fields')->latest()->get();
        return $content
            ->title(__(''))
            ->description(__(''))
            ->row(function ($row) use ($templates) {
                $row->column(12,view('Form::form-templates.index', compact('templates')));
            });
    }

    public function show($id,Content $content)
    {
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

        // Update template
        $formTemplate->update([
            'title' => $request->title,
            'form_type' => $request->form_type,
            'description' => $request->description,
        ]);

        // Delete existing sections and fields
        $formTemplate->sections()->delete();

        // Recreate sections and fields
        if ($request->has('sections')) {
            foreach ($request->sections as $sectionData) {
                $section = FormSection::create([
                    'form_template_id' => $formTemplate->id,
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
        $type =request('type');
        $locale = $request->header('Accept-Language', app()->getLocale());
        $locale = in_array($locale, ['ar', 'en', 'tr', 'hi']) ? $locale : app()->getLocale();
        app()->setLocale($locale);

        $template = FormTemplate::with(['sections.fields'])
            ->where('form_type', $type)
            ->where('is_active', true)
            ->firstOrFail();

        return view('Form::web-view.dynamic-form', compact('template','locale'));
    }

    public function storeSubmission(Request $request, string $type)
    {
        $template = FormTemplate::where('form_type', $type)->firstOrFail();

        FormRequest::create([
            'form_template_id' => $template->id,
            'submitted_by'=> Auth::user()->id  ,
            'bd_id' => $request->bd_id,
            'name' => $request->agency_name ?? $request->bd_name,
            'whatsapp_number' => $request->whatsapp_number,
            'form_template_type' => $template->form_type,
            'data' => json_encode($request->except('_token')),
            'created_at' => now(),
         
        ]);
        return response()->json([
            'success' => true,
            'message' => __('Form submitted successfully!'),
        ], 200);
    }

    public function getTranslations( Request $request)
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

}
