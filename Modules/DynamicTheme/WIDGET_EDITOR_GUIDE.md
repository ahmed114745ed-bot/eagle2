# نظام محرر الويدجت المتقدم - Dynamic Theme Widget Editor

## 📋 نظرة عامة

نظام متكامل لرسم وتخصيص شكل وألوان الويدجتات بشكل تفاعلي مع واجهة سهلة الاستخدام وخيارات متقدمة.

## 🎯 المميزات الأساسية

### 1️⃣ **محرر الألوان (Color Picker)**
- ✅ تحديد الألوان الأساسية والثانوية والتأكيد
- ✅ دعم صيغ متعددة (HEX, RGB, HSL)
- ✅ حفظ الألوان المسبقة (Color Presets)
- ✅ ألوان سريعة للاختيار الفوري
- ✅ معاينة فورية للألوان
- ✅ نسخ الألوان بسهولة

**المكون:** `ColorPickerComponent.vue`

```vue
<ColorPickerComponent
  v-model="colorConfig"
  :presets="presets"
  @apply="applyColors"
  @save-preset="saveColorPreset"
/>
```

### 2️⃣ **محرر البصري (Visual Builder)**
- ✅ التحكم في شكل الويدجت (Border Radius)
- ✅ إدارة الحدود (Border Color, Width, Style)
- ✅ التحكم في الظلال (Shadow)
- ✅ إعدادات الخطوط (Typography)
- ✅ التحكم في التباعد (Layout - Padding, Margin)
- ✅ المؤثرات (Opacity, Scale, Rotation)
- ✅ معاينة مباشرة للتصميم
- ✅ إنشاء CSS تلقائي

**المكون:** `VisualBuilderComponent.vue`

```vue
<VisualBuilderComponent
  v-model="designConfig"
  @apply="applyDesign"
  @save-template="saveTemplate"
/>
```

### 3️⃣ **معاينة الأسلوب (Style Preview)**
- ✅ معاينة الألوان مع التوافق اللوني
- ✅ عرض الأشكال المختلفة
- ✅ معاينة الخطوط والنصوص
- ✅ عرض المؤثرات المختلفة
- ✅ معاينة التباعد
- ✅ معاينة استجابة البصرية

**المكون:** `StylePreviewComponent.vue`

```vue
<StylePreviewComponent
  :color-config="colors"
/>
```

### 4️⃣ **محرر الويدجت المتكامل**
- ✅ واجهة موحدة لجميع الأدوات
- ✅ عروض متعددة (محرر، معاينة، كود)
- ✅ إدارة القوالب والحفظ المسبق
- ✅ تصدير واستيراد التصاميم
- ✅ نسخ الأكواد بسهولة

**المكون:** `WidgetEditorComponent.vue`

```vue
<WidgetEditorComponent
  :config-widget-override-id="widgetId"
  @save="onSave"
  @error="onError"
/>
```

## 🗄️ قاعدة البيانات

### جداول جديدة

#### 1. `widget_customizers`
```sql
- id: int
- config_widget_override_id: int (FK)
- shape_config: json - تكوين الشكل
- color_config: json - تكوين الألوان
- gradient_config: json - تكوين التدرجات
- background_config: json - تكوين الخلفية
- border_config: json - تكوين الحدود
- shadow_config: json - تكوين الظلال
- animation_config: json - تكوين الحركات
- transition_config: json - تكوين الانتقالات
- typography_config: json - تكوين الخطوط
- layout_config: json - تكوين التخطيط
- effects_config: json - تكوين المؤثرات
- name: string
- description: text
- is_active: boolean
- order: int
```

#### 2. `color_presets`
```sql
- id: int
- configuration_id: int (FK)
- name: string - اسم الحفظ المسبق
- description: string
- colors: json - مجموعة الألوان {primary, secondary, accent, text...}
- is_default: boolean
```

#### 3. `widget_design_templates`
```sql
- id: int
- configuration_id: int (FK)
- name: string - اسم القالب
- description: string
- design_config: json - كامل إعدادات التصميم
- preview_data: json - بيانات المعاينة
- is_public: boolean - هل القالب قابل للمشاركة
```

## 🔌 API Endpoints

### Widget Customizers
```
GET    /api/customizers/widget-override/{id}      - الحصول على المخصصات
GET    /api/customizers/{id}                      - تفاصيل مخصص
POST   /api/customizers                           - إنشاء مخصص جديد
PUT    /api/customizers/{id}                      - تحديث مخصص
DELETE /api/customizers/{id}                      - حذف مخصص
GET    /api/customizers/{id}/css                  - توليد CSS
POST   /api/customizers/{id}/clone                - نسخ مخصص
```

### Color Presets
```
GET    /api/color-presets/configuration/{id}      - الحصول على الحفظ المسبق
GET    /api/color-presets/{id}                    - تفاصيل الحفظ
POST   /api/color-presets                         - إنشاء حفظ جديد
PUT    /api/color-presets/{id}                    - تحديث الحفظ
DELETE /api/color-presets/{id}                    - حذف الحفظ
GET    /api/color-presets/configuration/{id}/default - الحفظ الافتراضي
GET    /api/color-presets/configuration/{id}/export  - تصدير
POST   /api/color-presets/import                  - استيراد
```

### Design Templates
```
GET    /api/design-templates/configuration/{id}   - الحصول على القوالب
GET    /api/design-templates/public/list          - القوالب المشتركة
GET    /api/design-templates/{id}                 - تفاصيل قالب
POST   /api/design-templates                      - إنشاء قالب جديد
PUT    /api/design-templates/{id}                 - تحديث قالب
DELETE /api/design-templates/{id}                 - حذف قالب
GET    /api/design-templates/{id}/export          - تصدير قالب
POST   /api/design-templates/import               - استيراد قالب
POST   /api/design-templates/{id}/duplicate       - نسخ قالب
```

## 🛠️ الخدمات (Services)

### CustomizerService
```php
// إنشاء مخصص جديد
CustomizerService::createCustomizer($overrideId, $designConfig, $name);

// تحديث المخصص
CustomizerService::updateCustomizerDesign($customizerId, $designConfig);

// الحصول على CSS
CustomizerService::getCustomizerCSS($customizerId);

// توليد CSS لويدجت كامل
CustomizerService::generateWidgetOverrideCSS($overrideId);

// تطبيق حفظ مسبق
CustomizerService::applyColorPreset($customizerId, $presetId);

// تطبيق قالب
CustomizerService::applyDesignTemplate($overrideId, $templateId);

// تطبيق جماعي
CustomizerService::batchApplyTemplate($widgetIds, $templateId);

// تحويل إلى CSS Variables
CustomizerService::configToCSSVariables($designConfig);

// تصدير التصميم
CustomizerService::exportDesign($customizerId);

// استيراد التصميم
CustomizerService::importDesign($overrideId, $designData);
```

## 💾 التخزين والحفظ

### هيكل البيانات للمخصص
```json
{
  "shape_config": {
    "borderRadius": 8
  },
  "color_config": {
    "primary": "#3b82f6",
    "secondary": "#10b981",
    "accent": "#f59e0b",
    "text": "#111827"
  },
  "border_config": {
    "width": 1,
    "color": "#e5e7eb",
    "style": "solid"
  },
  "shadow_config": {
    "blur": 4,
    "spread": 0,
    "offsetX": 0,
    "offsetY": 2,
    "color": "#000000",
    "opacity": 10
  },
  "typography_config": {
    "fontSize": 16,
    "fontWeight": "400",
    "color": "#111827"
  },
  "layout_config": {
    "padding": 16,
    "margin": 0,
    "minHeight": 80
  },
  "effects_config": {
    "opacity": 100,
    "scale": 1,
    "rotation": 0
  }
}
```

## 🎨 أمثلة الاستخدام

### في Vue Component
```vue
<template>
  <WidgetEditorComponent
    :config-widget-override-id="123"
    @save="handleSave"
  />
</template>

<script>
import WidgetEditorComponent from '@/components/WidgetEditorComponent.vue';

export default {
  components: { WidgetEditorComponent },
  methods: {
    handleSave(customizer) {
      console.log('Customizer saved:', customizer);
    }
  }
}
</script>
```

### في Laravel Controller
```php
use Modules\DynamicTheme\Services\CustomizerService;

// إنشاء مخصص
$customizer = CustomizerService::createCustomizer(
    configWidgetOverrideId: 1,
    designConfig: $request->validated(),
    name: 'Blue Theme'
);

// الحصول على CSS
$css = CustomizerService::getCustomizerCSS($customizer->id);

// تطبيق قالب
CustomizerService::applyDesignTemplate(1, 2);
```

## 🔄 سير العمل الموصى به

1. **اختيار الألوان** - استخدم Color Picker
2. **تخصيص الشكل** - استخدم Visual Builder
3. **المعاينة** - تحقق من النتيجة في Preview
4. **عرض الكود** - انسخ CSS إذا لزم الأمر
5. **حفظ كقالب** - احفظ التصميم المفضل
6. **الحفظ النهائي** - احفظ التخصيص

## 📱 الاستجابة

جميع المكونات مدعومة بالكامل للأجهزة المختلفة:
- ✅ الهاتف (Mobile)
- ✅ الجهاز اللوحي (Tablet)
- ✅ سطح المكتب (Desktop)

## 🚀 الأداء

- ✅ معاينة فورية بدون تأخير
- ✅ حفظ متزامن بدون إعادة تحميل
- ✅ دعم العمليات الكبيرة
- ✅ تخزين مؤقت فعال

## 🔒 الأمان

- ✅ التحقق من صلاحيات المستخدم
- ✅ التحقق من البيانات المدخلة
- ✅ حماية من XSS
- ✅ تشفير البيانات الحساسة

## 📚 المزيد من المعلومات

للمزيد من التفاصيل، راجع:
- [ColorPickerComponent.vue](./Resources/js/components/ColorPickerComponent.vue)
- [VisualBuilderComponent.vue](./Resources/js/components/VisualBuilderComponent.vue)
- [StylePreviewComponent.vue](./Resources/js/components/StylePreviewComponent.vue)
- [WidgetEditorComponent.vue](./Resources/js/components/WidgetEditorComponent.vue)
- [WidgetCustomizerController.php](./Http/Controllers/Api/WidgetCustomizerController.php)
- [CustomizerService.php](./Services/CustomizerService.php)

---

**آخر تحديث:** 15 يناير 2026
**الإصدار:** 1.0.0
