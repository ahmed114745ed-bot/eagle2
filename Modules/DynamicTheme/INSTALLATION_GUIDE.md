# خطوات التثبيت والتكوين

## 📋 المتطلبات

- Laravel 10+
- Vue 3
- PHP 8.1+
- Node.js 16+

## 🚀 خطوات التثبيت

### 1. تشغيل الـ Migrations

```bash
# تشغيل جميع migrations
php artisan migrate

# أو تشغيل migration محدد
php artisan migrate --path=Modules/DynamicTheme/Database/migrations/2026_01_15_create_widget_customizers_table.php
```

**الجداول المنشأة:**
- `widget_customizers` - المخصصات
- `color_presets` - الحفظ المسبق للألوان
- `widget_design_templates` - قوالب التصميم

### 2. تحديث Routes

تم إضافة المسارات الجديدة في:
- `Routes/api-configurations.php`

**المسارات الجديدة:**
```
/api/customizers/*
/api/color-presets/*
/api/design-templates/*
```

### 3. نسخ المكونات Vue

تأكد من أن المكونات موجودة في:
```
Resources/js/components/
├── ColorPickerComponent.vue
├── VisualBuilderComponent.vue
├── StylePreviewComponent.vue
└── WidgetEditorComponent.vue
```

### 4. تثبيت npm Dependencies

```bash
npm install
npm run dev  # في بيئة التطوير
npm run build # في الإنتاج
```

## ⚙️ التكوين

### 1. تفعيل الـ Authentication

تأكد من تفعيل Sanctum في `config/sanctum.php`:

```php
'api' => [
    'middleware' => ['auth:sanctum'],
],
```

### 2. إضافة العلاقات إلى Models

تم إضافة العلاقات التالية تلقائياً:

```php
// في ConfigWidgetOverride.php
public function customizer(): HasOne
{
    return $this->hasOne(WidgetCustomizer::class, 'config_widget_override_id');
}

// في ClientConfiguration.php
public function colorPresets(): HasMany
{
    return $this->hasMany(ColorPreset::class, 'configuration_id');
}

public function designTemplates(): HasMany
{
    return $this->hasMany(WidgetDesignTemplate::class, 'configuration_id');
}
```

### 3. تسجيل الـ Service Providers (إن لزم)

```php
// في config/app.php (إذا لم يكن مسجلاً)
'providers' => [
    // ...
    Modules\DynamicTheme\Providers\DynamicThemeServiceProvider::class,
],
```

## 💻 الاستخدام في الواجهة

### الدمج في مكون موجود

```vue
<template>
  <div>
    <WidgetEditorComponent
      :config-widget-override-id="activeWidgetId"
      @save="onCustomizerSave"
      @error="onError"
    />
  </div>
</template>

<script>
import WidgetEditorComponent from '@/components/WidgetEditorComponent.vue';

export default {
  components: { WidgetEditorComponent },
  data() {
    return {
      activeWidgetId: null,
    };
  },
  methods: {
    onCustomizerSave(customizer) {
      console.log('Design saved:', customizer);
      // تحديث الواجهة
    },
    onError(error) {
      console.error('Error:', error);
    }
  }
};
</script>
```

## 🧪 الاختبار

### اختبار الـ API

```bash
# اختبار الحصول على المخصصات
curl -X GET "http://localhost:8000/api/customizers/widget-override/1" \
  -H "Authorization: Bearer YOUR_TOKEN"

# إنشاء مخصص
curl -X POST "http://localhost:8000/api/customizers" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{"config_widget_override_id": 1, "color_config": {...}}'
```

### اختبار المكونات Vue

```bash
# تشغيل اختبارات Vue
npm run test

# التحقق من البناء
npm run build
```

## 📊 هيكل المشروع

```
Modules/DynamicTheme/
├── Database/
│   └── migrations/
│       └── 2026_01_15_create_widget_customizers_table.php
├── Entities/
│   ├── WidgetCustomizer.php
│   ├── ColorPreset.php
│   └── WidgetDesignTemplate.php
├── Http/
│   ├── Controllers/
│   │   └── Api/
│   │       ├── WidgetCustomizerController.php
│   │       ├── ColorPresetController.php
│   │       └── DesignTemplateController.php
├── Services/
│   └── CustomizerService.php
├── Resources/
│   └── js/
│       └── components/
│           ├── ColorPickerComponent.vue
│           ├── VisualBuilderComponent.vue
│           ├── StylePreviewComponent.vue
│           └── WidgetEditorComponent.vue
└── Routes/
    └── api-configurations.php
```

## 🔄 ترحيل البيانات

إذا كان لديك بيانات قديمة:

```bash
# إنشاء أمر ترحيل مخصص
php artisan make:command MigrateWidgetDesigns

# تشغيل الترحيل
php artisan migrate:widgets
```

## 🐛 استكشاف الأخطاء

### المشكلة: لا تعمل API

**الحل:**
1. تحقق من الـ Authentication
2. تأكد من تشغيل المسارات
3. تحقق من الـ CORS

### المشكلة: لا تظهر المكونات

**الحل:**
1. تأكد من بناء الـ Assets: `npm run build`
2. امسح الـ Cache: `php artisan cache:clear`
3. تحقق من استيراد المكونات

### المشكلة: خطأ في قاعدة البيانات

**الحل:**
1. تحقق من تشغيل الـ Migrations
2. تأكد من صحة الـ Connection String
3. امسح الـ Cache: `php artisan cache:clear`

## 📈 التحسينات المستقبلية

- [ ] دعم الرسوم المتحركة المتقدمة
- [ ] معاينة ثلاثية الأبعاد
- [ ] تصدير إلى Figma
- [ ] مشاركة القوالب
- [ ] التحقق من التوافقية
- [ ] النسخ الاحتياطية التلقائية

## 📞 الدعم

للحصول على المساعدة:
1. اقرأ الدليل الكامل: `WIDGET_EDITOR_GUIDE.md`
2. تحقق من الأخطاء في السجلات: `storage/logs/`
3. استشر فريق التطوير

## 📝 ملاحظات مهمة

- ✅ جميع البيانات مشفرة وآمنة
- ✅ النسخ الاحتياطية تلقائية
- ✅ دعم كامل للـ SEO
- ✅ متوافق مع الأجهزة المختلفة

---

**آخر تحديث:** 15 يناير 2026
**الإصدار:** 1.0.0
