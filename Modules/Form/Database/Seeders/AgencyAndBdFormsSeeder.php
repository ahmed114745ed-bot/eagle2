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
    'label' => ['en' => 'BD ID', 'ar' => 'رقم BD', 'hi' => 'BD आईडी', 'tr' => 'BD Kimliği'],
    'name' => 'bd_id',
    'type' => 'text',
    'placeholder' => ['en' => 'Enter BD ID', 'ar' => 'أدخل رقم BD', 'hi' => 'BD आईडी दर्ज करें', 'tr' => 'BD Kimliği girin'],
    'order' => 1,
    ],
    [
    'label' => ['en' => 'Agency Name', 'ar' => 'اسم الوكالة', 'hi' => 'एजेंसी नाम', 'tr' => 'Ajans Adı'],
    'name' => 'agency_name',
    'type' => 'text',
    'placeholder' => ['en' => 'Enter Agency Name', 'ar' => 'أدخل اسم الوكالة', 'hi' => 'एजेंसी नाम दर्ज करें', 'tr' => 'Ajans Adı girin'],
    'order' => 2,
    ],
    [
    'label' => ['en' => 'WhatsApp Number', 'ar' => 'رقم الواتساب', 'hi' => 'व्हाट्सएप नंबर', 'tr' => 'WhatsApp Numarası'],
    'name' => 'whatsapp_number',
    'type' => 'text',
    'placeholder' => ['en' => 'Enter WhatsApp number', 'ar' => 'أدخل رقم الواتساب', 'hi' => 'व्हाट्सएप नंबर दर्ज करें', 'tr' => 'WhatsApp Numarası girin'],
    'order' => 3,
    ],
    ];
    
    $shippingAgency = [
        [
            'label' => ['en' => 'Agency Name', 'ar' => 'اسم الوكالة', 'hi' => 'एजेंसी नाम', 'tr' => 'Ajans Adı'],
            'name' => 'agency_name',
            'type' => 'text',
            'placeholder' => ['en' => 'Enter Agency Name', 'ar' => 'أدخل اسم الوكالة', 'hi' => 'एजेंसी नाम दर्ज करें', 'tr' => 'Ajans Adı girin'],
            'order' => 1,
        ],
        [
            'label' => ['en' => 'WhatsApp Number', 'ar' => 'رقم الواتساب', 'hi' => 'व्हाट्सएप नंबर', 'tr' => 'WhatsApp Numarası'],
            'name' => 'whatsapp_number',
            'type' => 'text',
            'placeholder' => ['en' => 'Enter WhatsApp number', 'ar' => 'أدخل رقم الواتساب', 'hi' => 'व्हाट्सएप नंबर दर्ज करें', 'tr' => 'WhatsApp Numarası girin'],
            'order' => 2,
        ],
    ];
    
    $this->createForm(
        ['en' => 'Host Agency Form', 'ar' => 'نموذج وكالة مضيفين', 'hi' => 'होस्ट एजेंसी फ़ॉर्म', 'tr' => 'Ev Sahibi Ajans Formu'],
        'host_agency',
        'agency',
        ['en' => 'Form to register a host agency', 'ar' => 'نموذج لتسجيل وكالة مضيفين', 'hi' => 'होस्ट एजेंसी पंजीकरण फ़ॉर्म', 'tr' => 'Ev sahibi ajans kaydı için form'],
        $sharedFields
    );
    
    $this->createForm(
        ['en' => 'Shipping Agency Form', 'ar' => 'نموذج وكالة شحن', 'hi' => 'शिपिंग एजेंसी फ़ॉर्म', 'tr' => 'Nakliye Ajansı Formu'],
        'shipping_agency',
        'agency',
        ['en' => 'Form to register a shipping agency', 'ar' => 'نموذج لتسجيل وكالة شحن', 'hi' => 'शिपिंग एजेंसी पंजीकरण फ़ॉर्म', 'tr' => 'Nakliye ajansı kaydı için form'],
        $shippingAgency
    );
    
    $this->createForm(
        ['en' => 'BD Registration Form', 'ar' => 'نموذج تسجيل BD', 'hi' => 'BD पंजीकरण फ़ॉर्म', 'tr' => 'BD Kayıt Formu'],
        'bd_form',
        'bd',
        ['en' => 'Form to add BD information', 'ar' => 'نموذج لإضافة معلومات BD', 'hi' => 'BD जानकारी जोड़ने का फ़ॉर्म', 'tr' => 'BD bilgilerini eklemek için form'],
        [
            [
                'label' => ['en' => 'BD Name', 'ar' => 'اسم BD', 'hi' => 'BD नाम', 'tr' => 'BD Adı'],
                'name' => 'bd_name',
                'type' => 'text',
                'placeholder' => ['en' => 'Enter BD name', 'ar' => 'أدخل اسم BD', 'hi' => 'BD नाम दर्ज करें', 'tr' => 'BD Adı girin'],
                'order' => 1,
            ],
            [
                'label' => ['en' => 'Country', 'ar' => 'الدولة', 'hi' => 'देश', 'tr' => 'Ülke'],
                'name' => 'country',
                'type' => 'select',
                'placeholder' => ['en' => 'Select country', 'ar' => 'اختر الدولة', 'hi' => 'देश चुनें', 'tr' => 'Ülke seçin'],
                'options' => [
                    'Egypt' => ['en' => 'Egypt', 'ar' => 'مصر', 'hi' => 'मिस्र', 'tr' => 'Mısır'],
                    'Saudi Arabia' => ['en' => 'Saudi Arabia', 'ar' => 'السعودية', 'hi' => 'सऊदी अरब', 'tr' => 'Suudi Arabistan'],
                    'UAE' => ['en' => 'UAE', 'ar' => 'الإمارات', 'hi' => 'संयुक्त अरब अमीरात', 'tr' => 'BAE'],
                    'Other' => ['en' => 'Other', 'ar' => 'أخرى', 'hi' => 'अन्य', 'tr' => 'Diğer']
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
    'is_active' => true,
    'can_not_delete'=>true
    ]);
    
    $section = FormSection::create([
        'form_template_id' => $template->id,
        'title' => ['en' => 'Main Section', 'ar' => 'القسم الرئيسي', 'hi' => 'मुख्य अनुभाग', 'tr' => 'Ana Bölüm'],
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
