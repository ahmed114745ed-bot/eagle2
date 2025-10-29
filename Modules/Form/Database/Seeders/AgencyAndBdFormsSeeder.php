<?php

namespace  Modules\Form\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Form\Entities\FormField;
use Modules\Form\Entities\FormSection;
use Modules\Form\Entities\FormTemplate;


class AgencyAndBdFormsSeeder extends Seeder
{
    public function run(): void
    {
        $sharedFields = [
            [
                'label' => ['en' => 'BD ID', 'ar' => 'رقم BD'],
                'name' => 'bd_id',
                'type' => 'text',
                'placeholder' => ['en' => 'Enter BD ID', 'ar' => 'أدخل رقم BD'],
                'order' => 1,
            ],
            [
                'label' => ['en' => 'Agency Name', 'ar' => 'اسم الوكالة'],
                'name' => 'agency_name',
                'type' => 'text',
                'placeholder' => ['en' => 'Enter Agency Name', 'ar' => 'أدخل اسم الوكالة'],
                'order' => 2,
            ],
            [
                'label' => ['en' => 'WhatsApp Number', 'ar' => 'رقم الواتساب'],
                'name' => 'whatsapp_number',
                'type' => 'text',
                'placeholder' => ['en' => 'Enter WhatsApp number', 'ar' => 'أدخل رقم الواتساب'],
                'order' => 3,
            ],
        ];

        $this->createForm(
            ['en' => 'Host Agency Form', 'ar' => 'نموذج وكالة مضيفة'],
            'host_agency',
            'agency',
            ['en' => 'Form to register a host agency', 'ar' => 'نموذج لتسجيل وكالة مضيفة'],
            $sharedFields
        );

        $this->createForm(
            ['en' => 'Shipping Agency Form', 'ar' => 'نموذج وكالة شحن'],
            'shipping_agency',
            'agency',
            ['en' => 'Form to register a shipping agency', 'ar' => 'نموذج لتسجيل وكالة شحن'],
            $sharedFields
        );

        $this->createForm(
            ['en' => 'BD Registration Form', 'ar' => 'نموذج تسجيل BD'],
            'bd_form',
            'bd',
            ['en' => 'Form to add BD information', 'ar' => 'نموذج لإضافة معلومات BD'],
            [
                [
                    'label' => ['en' => 'BD Name', 'ar' => 'اسم BD'],
                    'name' => 'bd_name',
                    'type' => 'text',
                    'placeholder' => ['en' => 'Enter BD name', 'ar' => 'أدخل اسم BD'],
                    'order' => 1,
                ],
                [
                    'label' => ['en' => 'Country', 'ar' => 'الدولة'],
                    'name' => 'country',
                    'type' => 'select',
                    'placeholder' => ['en' => 'Select country', 'ar' => 'اختر الدولة'],
                    'options' => [
                        'Egypt' => ['en' => 'Egypt', 'ar' => 'مصر'],
                        'Saudi Arabia' => ['en' => 'Saudi Arabia', 'ar' => 'السعودية'],
                        'UAE' => ['en' => 'UAE', 'ar' => 'الإمارات'],
                        'Other' => ['en' => 'Other', 'ar' => 'أخرى']
                    ],
                    'order' => 2,
                ],
            ]
        );
    }

    private function createForm(array $title, string $formType, string $category, array $desc, array $fields)
    {
        $template = FormTemplate::create([
            'title' => $title,
            'form_type' => $formType,
            'description' => $desc,
            'created_by' => 1,
            'is_active' => true,
            'can_not_delete'=>true
        ]);

        $section = FormSection::create([
            'form_template_id' => $template->id,
            'title' => ['en' => 'Main Section', 'ar' => 'القسم الرئيسي'],
            'section_order' => 1,
            'is_visible' => true,
            'can_not_delete' =>true
        ]);

        foreach ($fields as $field) {
            FormField::create([
                'section_id' => $section->id,
                'field_label' => $field['label'],
                'field_name' => $field['name'],
                'field_type' => $field['type'],
                'placeholder' => $field['placeholder'] ?? null,
                'options' => $field['options'] ?? null,
                'is_required' => true,
                'is_enabled' => true,
                'can_not_delete' => true,
                'field_order' => $field['order'],
            ]);
        }
    }
}
