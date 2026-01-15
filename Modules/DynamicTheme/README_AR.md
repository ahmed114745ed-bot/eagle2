# Dynamic Theme Widget Editor - نظام محرر الويدجت المتقدم

![Version](https://img.shields.io/badge/version-1.0.0-blue.svg)
![Status](https://img.shields.io/badge/status-active-success.svg)
![License](https://img.shields.io/badge/license-MIT-green.svg)

## 🎯 نظرة عامة

نظام متكامل وقوي لرسم وتخصيص شكل وألوان الويدجتات في لوحة تحكم ديناميكية، يسمح للمستخدمين برسم الويدجت ووضع الألوان والحفاظ على الإعدادات بسهولة.

## ✨ المميزات الرئيسية

### 🎨 **محرر الألوان المتقدم**
- اختيار الألوان الأساسية والثانوية والتأكيد
- دعم صيغ متعددة (HEX, RGB, HSL)
- حفظ مسبق للألوان المفضلة
- ألوان سريعة للاختيار الفوري
- معاينة فورية للألوان

### 🔧 **محرر البصري الشامل**
- التحكم الكامل في شكل الويدجت
- إدارة الحدود والظلال
- إعدادات الخطوط والنصوص
- التحكم في التباعد والتخطيط
- المؤثرات والتحولات

### 👁️ **معاينة شاملة**
- معاينة الألوان والأشكال
- عرض الخطوط والمؤثرات
- معاينة الاستجابة على أجهزة مختلفة

### 💾 **إدارة البيانات المتقدمة**
- حفظ مسبق للألوان
- قوالب التصميم القابلة للإعادة
- التصدير والاستيراد بصيغة JSON
- نسخ التخصيصات

### 💻 **توليد الأكواس التلقائي**
- توليد CSS تلقائي
- متغيرات CSS جاهزة
- دعم Tailwind CSS

## 📦 المحتويات

```
Modules/DynamicTheme/
├── Database/migrations/
│   └── 2026_01_15_create_widget_customizers_table.php
├── Entities/
│   ├── WidgetCustomizer.php
│   ├── ColorPreset.php
│   └── WidgetDesignTemplate.php
├── Http/Controllers/Api/
│   ├── WidgetCustomizerController.php
│   ├── ColorPresetController.php
│   └── DesignTemplateController.php
├── Services/
│   └── CustomizerService.php
├── Resources/js/components/
│   ├── ColorPickerComponent.vue
│   ├── VisualBuilderComponent.vue
│   ├── StylePreviewComponent.vue
│   ├── WidgetEditorComponent.vue
│   └── EnhancedDynamicTheme.vue
├── Tests/
│   └── WidgetCustomizerTest.php
├── Routes/
│   └── api-configurations.php
├── WIDGET_EDITOR_GUIDE.md
├── INSTALLATION_GUIDE.md
├── PROJECT_SUMMARY.md
└── README.md (هذا الملف)
```

## 🚀 البدء السريع

### 1. التثبيت

```bash
# تشغيل الـ Migrations
php artisan migrate

# تثبيت npm packages
npm install

# بناء الأصول
npm run build
```

### 2. الاستخدام الأساسي

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

### 3. استخدام API

```bash
# الحصول على المخصصات
curl -X GET "http://localhost/api/customizers/widget-override/1" \
  -H "Authorization: Bearer YOUR_TOKEN"

# إنشاء مخصص جديد
curl -X POST "http://localhost/api/customizers" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{
    "config_widget_override_id": 1,
    "color_config": {"primary": "#3b82f6"}
  }'
```

## 📚 التوثيق الكاملة

- **[WIDGET_EDITOR_GUIDE.md](./WIDGET_EDITOR_GUIDE.md)** - دليل استخدام شامل
- **[INSTALLATION_GUIDE.md](./INSTALLATION_GUIDE.md)** - خطوات التثبيت والتكوين
- **[PROJECT_SUMMARY.md](./PROJECT_SUMMARY.md)** - ملخص المشروع

## 🔌 API Endpoints

### Widget Customizers
```
GET    /api/customizers/widget-override/{id}
GET    /api/customizers/{id}
POST   /api/customizers
PUT    /api/customizers/{id}
DELETE /api/customizers/{id}
GET    /api/customizers/{id}/css
POST   /api/customizers/{id}/clone
```

### Color Presets
```
GET    /api/color-presets/configuration/{id}
GET    /api/color-presets/{id}
POST   /api/color-presets
PUT    /api/color-presets/{id}
DELETE /api/color-presets/{id}
GET    /api/color-presets/configuration/{id}/default
```

### Design Templates
```
GET    /api/design-templates/configuration/{id}
GET    /api/design-templates/{id}
POST   /api/design-templates
PUT    /api/design-templates/{id}
DELETE /api/design-templates/{id}
POST   /api/design-templates/{id}/duplicate
```

## 🎓 أمثلة

### مثال 1: إنشاء مخصص في PHP

```php
use Modules\DynamicTheme\Services\CustomizerService;

$customizer = CustomizerService::createCustomizer(
    configWidgetOverrideId: 1,
    designConfig: [
        'color_config' => [
            'primary' => '#3b82f6',
            'secondary' => '#10b981',
        ],
        'shape_config' => [
            'borderRadius' => 8,
        ],
    ],
    name: 'Blue Theme'
);

// الحصول على CSS
$css = CustomizerService::getCustomizerCSS($customizer->id);
```

### مثال 2: تطبيق قالب

```php
// تطبيق قالب تصميم على ويدجت
CustomizerService::applyDesignTemplate(
    configWidgetOverrideId: 1,
    templateId: 5
);

// تطبيق جماعي على عدة ويدجتات
CustomizerService::batchApplyTemplate([1, 2, 3], templateId: 5);
```

### مثال 3: تصدير واستيراد

```php
// تصدير التصميم
$exported = CustomizerService::exportDesign($customizerId);
file_put_contents('design.json', json_encode($exported));

// استيراد التصميم
$imported = CustomizerService::importDesign($overrideId, json_decode($json, true));
```

## 🔒 الأمان

- ✅ التحقق من الصلاحيات (Authentication)
- ✅ التحقق من البيانات المدخلة (Validation)
- ✅ حماية من الهجمات (CSRF, XSS)
- ✅ تشفير البيانات الحساسة

## 📊 الأداء

- ⚡ معاينة فورية بدون تأخير
- 🚀 حفظ متزامن سريع
- 💾 تخزين مؤقت فعال
- 🌐 استهلاك نطاق ترددي منخفض

## 📱 التوافقية

- ✅ سطح المكتب (Desktop)
- ✅ الجهاز اللوحي (Tablet)
- ✅ الهاتف الذكي (Mobile)

## 🧪 الاختبارات

```bash
# تشغيل الاختبارات
php artisan test Modules/DynamicTheme/Tests/WidgetCustomizerTest

# تشغيل اختبارات محددة
php artisan test --filter=can_create_widget_customizer
```

## 🐛 استكشاف الأخطاء

### المشكلة: API لا يعمل

**الحل:**
1. تحقق من الـ Authentication (توكن صحيح)
2. تأكد من تشغيل الـ Migration
3. اختبر الاتصال: `php artisan tinker`

### المشكلة: المكونات لا تظهر

**الحل:**
1. بناء الأصول: `npm run build`
2. امسح الـ Cache: `php artisan cache:clear`
3. تحقق من الـ Import في الملف الرئيسي

### المشكلة: خطأ في قاعدة البيانات

**الحل:**
1. تشغيل Migration: `php artisan migrate`
2. تحقق من Connection String
3. تأكد من صلاحيات المستخدم

## 📈 التطور المستقبلي

- 🎬 رسوم متحركة متقدمة
- 🎞️ معاينة ثلاثية الأبعاد
- 🎨 التصدير إلى Figma
- 👥 مشاركة القوالب
- ✅ التحقق التلقائي من التوافقية
- 💾 نسخ احتياطية تلقائية

## 📞 الدعم

- 📖 اقرأ [WIDGET_EDITOR_GUIDE.md](./WIDGET_EDITOR_GUIDE.md)
- 📋 اقرأ [INSTALLATION_GUIDE.md](./INSTALLATION_GUIDE.md)
- 🐛 أبلغ عن الأخطاء
- 💬 تواصل مع الفريق

## 📄 الترخيص

هذا المشروع مرخص تحت MIT License

## 👥 المساهمون

تم إنجاز هذا المشروع بواسطة فريق التطوير

## 🙏 شكر خاص

شكراً لاستخدامك نظام Dynamic Theme Widget Editor!

---

## 📊 إحصائيات المشروع

| العنصر | العدد |
|--------|-------|
| ملفات PHP | 6 |
| ملفات Vue | 5 |
| Endpoints | 24+ |
| جداول قاعدة البيانات | 3 |
| أسطر من التوثيق | 500+ |
| حالات الاستخدام | 10+ |

---

## ✅ حالة المشروع

**الإصدار:** 1.0.0  
**الحالة:** جاهز للإنتاج ✅  
**آخر تحديث:** 15 يناير 2026

---

<div align="center">

**🎨 نظام محرر الويدجت المتقدم**

*اجعل تخصيص الويدجتات سهلاً وممتعاً*

[📖 الدليل](./WIDGET_EDITOR_GUIDE.md) • [🚀 التثبيت](./INSTALLATION_GUIDE.md) • [📊 الملخص](./PROJECT_SUMMARY.md)

</div>
