<?php

namespace Modules\Form\Services;

use Modules\Form\Entities\FormField;

class FormRenderService
{

    public function renderFormData(array $data): string
    {
        $fields = $data;
        $mainFields = $data;
        $sections = $data['sections'] ?? [];

        if (!empty($sections)) {
            foreach ($sections as $section) {
                if (!empty($section['fields'])) {
                    foreach ($section['fields'] as $subField) {
                        if (!empty($subField['items'])) {
                            foreach ($subField['items'] as $item) {
                                foreach ($item as $key => $val) {
                                    $fields[$key] = $val; 
                                }
                            }
                        }
                    }
                }
            }
        }

        unset($fields['sections']);

        $html = '<div class="form-data" style="max-width:100%;">';
        foreach ($fields as $name => $value) {
            $html .= $this->renderField($name, $value);
        }
        $html .= '</div>';

        return $html;
    }

    public function renderField(string $name, $value): string
    {
        $field = FormField::where('field_name', $name)->first();

        $label = $field?->field_label ?? ucwords(str_replace('_', ' ', $name));
        $type = $field?->field_type ?? $this->detectFieldType($name, $value);

        $html = '<div style="margin-bottom:15px; padding:12px; background:#f9f9f9; border-radius:6px; display:flex; flex-direction:column;">';
        $html .= '<strong style="color:#2c3e50; margin-bottom:5px;">' . htmlspecialchars($label) . ':</strong> ';

        switch ($type) {
            case 'file':
                $html .= $this->renderFile($value);
                break;

            case 'email':
                $html .= '<a href="mailto:' . e($value) . '" style="color:#3498db; text-decoration:none;">' . e($value) . '</a>';
                break;

            case 'tel':
                $html .= '<a href="tel:' . e($value) . '" style="color:#27ae60; text-decoration:none;">' . e($value) . '</a>';
                break;

            case 'textarea':
                $html .= '<div style="white-space:pre-wrap; line-height:1.4;">' . e($value) . '</div>';
                break;

            case 'number':
                $html .= '<span style="font-weight:500; color:#2c3e50;">' . e($value) . '</span>';
                break;

            default:
                // نصوص عادية
                $html .= '<span>' . nl2br(htmlspecialchars($value)) . '</span>';
        }

        $html .= '</div>';
        return $html;
    }

    public function renderFile($value): string
    {
        $files = is_array($value) ? $value : [$value];
        $html = '<div style="display:flex; flex-wrap:wrap; gap:10px; margin-top:5px;">';

        foreach ($files as $file) {
            if (!$file) continue;

            $path = getImagePath($file);
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));

            if (in_array($ext, ['jpg','jpeg','png','gif','webp','svg'])) {
                $html .= '<a href="' . $path . '" target="_blank">
                            <img src="' . $path . '" style="max-width:150px; max-height:150px; border-radius:6px; border:1px solid #ddd;">
                          </a>';
            } else {
                $html .= '<a href="' . $path . '" target="_blank" style="display:inline-flex; align-items:center; gap:6px; padding:5px 10px; background:#eef; border-radius:6px; text-decoration:none; color:#333;">
                            <i class="fa fa-file"></i> ' . basename($file) . '
                          </a>';
            }
        }

        $html .= '</div>';
        return $html;
    }

 
    protected function detectFieldType(string $name, $value): string
    {
        if (is_numeric($value)) return 'number';
        if (filter_var($value, FILTER_VALIDATE_EMAIL)) return 'email';
        if (preg_match('/^\+?\d{6,15}$/', $value)) return 'tel';
        if (is_array($value)) return 'file';
        return 'text';
    }
}
