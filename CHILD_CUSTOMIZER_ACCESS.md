# الوصول إلى Child Customizer

## الخطوات:

### 1. بدء التطبيق
```bash
php artisan serve
```

### 2. الوصول إلى الـ Dashboard
انتقل إلى:
```
http://localhost:8000/admin/theme-dashboard
```

### 3. النقر على زر Child Customizer
ستجد زر أزرق جديد في الأعلى بعنوان **"🎨 Child Customizer"**

### 4. أو الوصول المباشر
```
http://localhost:8000/admin/child-customizers
```

## الملفات المُنشأة:

### Backend:
- **Route**: `/Modules/DynamicTheme/Routes/web.php` - Route للوصول للصفحة
- **Blade Template**: `/Modules/DynamicTheme/Resources/views/child-customizer.blade.php`
- **API Endpoints**: `/Modules/DynamicTheme/Routes/api-configurations.php`

### Frontend:
- **Vue Page Component**: `/Modules/DynamicTheme/Resources/js/pages/ChildCustomizerPage.vue`
- **Vue Main Component**: `/Modules/DynamicTheme/Resources/js/components/ChildCustomizerComponent.vue`

### Dashboard Integration:
- **Link Added to**: `/Modules/DynamicTheme/Resources/js/components/DashboardApp.vue`
- Location: أزرار الـ Header

## الميزات المتوفرة:

✅ عرض قائمة العناصر الفرعية (Children)
✅ إضافة عناصر جديدة
✅ تعديل خصائص العناصر:
   - الألوان (Colors)
   - الحدود (Borders)
   - الظلال (Shadows)
   - الرسوميات (Shapes)
   - الحركات (Animations)
✅ معاينة مباشرة
✅ توليد CSS تلقائي
✅ استنساخ العناصر
✅ حذف العناصر
✅ إعادة ترتيب السحب والإفلات

## Build والتجميع:

```bash
# Build with Vite
npx vite build

# أو للتطوير:
npx vite
```

## التحقق من الأخطاء:

```bash
# التحقق من بناء الـ PHP
php artisan tinker
# ثم: class_exists('Modules\DynamicTheme\Entities\ChildCustomizer')
```

## الملاحظات:

- التطبيق يتطلب تسجيل الدخول كـ Admin
- جميع الـ routes محمية بـ middleware (admin, adminIp)
- الـ API Endpoints متوفرة في `/api/child-customizers`

---

**تم إنشاء النظام بنجاح!** 🎉
