# دليل اختبار حزمة Reals - Testing Guide

هذا الدليل يشرح كيفية اختبار تحويل موديول الريلز إلى حزمة مستقلة من خلال 4 سيناريوهات:

1. **🗑️ إزالة الموديول القديم** - والتأكد من عمل التطبيق
2. **✅ إضافة الحزمة الجديدة** - يدوياً والتأكد من عملها
3. **🔄 إزالة الحزمة الجديدة** - والتأكد من عدم انهيار التطبيق
4. **♻️ إعادة إضافة الحزمة** - للتأكد من سهولة التفعيل/التعطيل

---

## 📋 قبل البدء - المتطلبات الأساسية

### ✅ تأكد من وجود هذه الملفات في المشروع الأساسي:

```
app/
├── Contracts/
│   └── RealsContract.php          ← واجهة الريلز (Interface)
├── Services/
│   └── Null/
│       └── NullRealsService.php   ← الخدمة الفارغة (Fallback)
├── Traits/
│   └── DynamicRealsTrait.php      ← Trait ديناميكي للـ User Model
└── Providers/
    └── FeatureServiceProvider.php ← مسجل في config/app.php
```

### ✅ تأكد من تحديث User Model:
```php
// app/Models/User.php
use App\Traits\DynamicRealsTrait;

class User extends Authenticatable
{
    use DynamicRealsTrait;  // ← بدلاً من RealRelationshipTrait القديم
    // ...
}
```

### ✅ تأكد من وجود الحزمة الجديدة:
```
packages/Utd/Reals/   ← مجلد الحزمة الجديدة
```

---

# 🗑️ السيناريو الأول: إزالة الموديول القديم

## الهدف: حذف `Modules/Reals` والتأكد من عمل التطبيق

### الخطوة 1: تعطيل الموديول القديم

```bash
# افتح ملف modules_statuses.json
nano modules_statuses.json

# غيّر حالة Reals إلى false:
{
    "Reals": false
}
```

### الخطوة 2: مسح Cache

```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan optimize:clear
```

### الخطوة 3: اختبار التطبيق

```bash
php artisan serve
```

### ✅ قائمة التحقق - السيناريو الأول:

| الاختبار | النتيجة المتوقعة |
|---------|-----------------|
| فتح الصفحة الرئيسية | ✅ يعمل |
| تسجيل الدخول | ✅ يعمل |
| فتح ملف المستخدم (Profile) | ✅ يعمل |
| استدعاء `$user->reals` | ✅ Collection فارغة (بدون Error) |

### الخطوة 4: اختبار في Tinker

```bash
php artisan tinker
```

```php
$user = \App\Models\User::first();
$user->reals;           // => Collection فارغة ✅
$user->realsLikes;      // => Collection فارغة ✅
$user->reals()->count(); // => 0 ✅
```

### الخطوة 5: (اختياري) حذف مجلد الموديول القديم

```bash
# بعد التأكد من نجاح الاختبارات
rm -rf Modules/Reals
```

---

# ✅ السيناريو الثاني: إضافة الحزمة الجديدة (يدوياً)

## الهدف: تفعيل الحزمة الجديدة من `packages/Utd/Reals`

> ⚠️ **ملاحظة:** الخطوات 1 و 2 تُنفذ مرة واحدة فقط عند أول تثبيت. بعدها يكفي تفعيل/تعطيل ServiceProvider فقط.

### الخطوة 1: إضافة Autoload في composer.json (مرة واحدة فقط)

افتح `composer.json` وأضف في قسم `autoload.psr-4`:

```json
{
    "autoload": {
        "psr-4": {
            "App\\": "app/",
            "Database\\Factories\\": "database/factories/",
            "Database\\Seeders\\": "database/seeders/",
            "Utd\\Reals\\": "packages/Utd/Reals/src/"
        }
    }
}
```

### الخطوة 2: تحديث Autoload (مرة واحدة فقط)

```bash
composer dump-autoload
```

### الخطوة 3: تسجيل ServiceProvider ✨ (هذه الخطوة الأساسية)

افتح `config/app.php` وأضف في نهاية `providers`:

```php
'providers' => [
    // ... باقي الـ Providers
    
    /*
     * Package Service Providers - الحزم المستقلة
     */
    Utd\Reals\RealsServiceProvider::class,
],
```

### الخطوة 4: مسح Cache

```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

### الخطوة 5: تشغيل Migrations (إذا لم تكن الجداول موجودة)

```bash
# عرض حالة الـ Migrations
php artisan migrate:status

# تشغيل migrations الحزمة
php artisan migrate
```

### الخطوة 6: نشر Assets

```bash
# نشر ملفات JavaScript للوحة التحكم
php artisan vendor:publish --tag=reals-assets --force
```

### الخطوة 7: اختبار التطبيق

```bash
php artisan serve
```

### ✅ قائمة التحقق - السيناريو الثاني:

| الاختبار | النتيجة المتوقعة |
|---------|-----------------|
| فتح الموقع | ✅ يعمل |
| `/admin/view/reels` | ✅ لوحة التحكم تعمل |
| `/api/reals` | ✅ API يعمل |
| `$user->reals` | ✅ يرجع ريلز المستخدم |

### الخطوة 8: اختبار في Tinker

```bash
php artisan tinker
```

```php
// التحقق من تسجيل الخدمة
app(\App\Contracts\RealsContract::class);
// => Utd\Reals\Services\RealsService ✅

// التحقق من العلاقات
$user = \App\Models\User::first();
$user->reals;  // => Collection (قد تكون فارغة أو بها بيانات) ✅

// التحقق من Entity
class_exists(\Utd\Reals\Entities\Real::class);  // => true ✅
```

---

# 🔄 السيناريو الثالث: إزالة الحزمة الجديدة

## الهدف: تعطيل الحزمة والتأكد من عدم انهيار التطبيق

### الخطوة 1: تعليق ServiceProvider من config/app.php ✨ (هذا كل ما تحتاجه)

افتح `config/app.php` وعلّق السطر:

```php
'providers' => [
    // ...
    
    // Utd\Reals\RealsServiceProvider::class,  ← علّق هذا السطر فقط
],
```

### الخطوة 2: مسح Cache

```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan optimize:clear
```

> 💡 **ملاحظة:** لا تحتاج حذف Autoload من composer.json. يمكنك تركه لتسهيل إعادة التفعيل لاحقاً.

### الخطوة 3: اختبار التطبيق

```bash
php artisan serve
```

### ✅ قائمة التحقق - السيناريو الثالث:

| الاختبار | النتيجة المتوقعة |
|---------|-----------------|
| فتح الموقع | ✅ يعمل بدون أخطاء |
| تسجيل الدخول | ✅ يعمل |
| `$user->reals` | ✅ Collection فارغة |
| `/admin/reels` | ⚠️ 404 (غير متاح) |
| `/api/reals` | ✅ استجابة فارغة |

### الخطوة 4: اختبار في Tinker

```bash
php artisan tinker
```

```php
// يجب أن يرجع NullRealsService
app(\App\Contracts\RealsContract::class);
// => App\Services\Null\NullRealsService ✅

$user = \App\Models\User::first();
$user->reals;           // => Collection فارغة ✅
$user->realsLikes;      // => Collection فارغة ✅
$user->hasRealsFeature(); // => false ✅
```

### ❌ ما يجب أن لا يحدث:

- لا يجب ظهور `Class not found` errors
- لا يجب ظهور `Target class [Utd\Reals\...] does not exist`
- لا يجب انهيار أي صفحة

---

# ♻️ السيناريو الرابع: إعادة إضافة الحزمة

## الهدف: التأكد من سهولة إعادة التفعيل

> 💡 بما أن Autoload موجود بالفعل، فقط تحتاج تفعيل ServiceProvider!

### الخطوة 1: إعادة تفعيل ServiceProvider ✨ (هذا كل ما تحتاجه)

في `config/app.php` أزل التعليق:

```php
'providers' => [
    // ...
    Utd\Reals\RealsServiceProvider::class,  // ← أزل التعليق
],
```

### الخطوة 2: مسح Cache

```bash
php artisan config:clear
php artisan route:clear
php artisan optimize:clear
```

### الخطوة 3: اختبار التطبيق

```bash
php artisan serve
```

### ✅ قائمة التحقق - السيناريو الرابع:

| الاختبار | النتيجة المتوقعة |
|---------|-----------------|
| الموقع يعمل | ✅ |
| لوحة التحكم `/admin/reels` | ✅ |
| API `/api/reals` | ✅ |
| العلاقات تعمل | ✅ |

---

## 📊 جدول ملخص السيناريوهات

| السيناريو | الحالة | `/admin/reels` | `$user->reals` | API |
|-----------|--------|----------------|----------------|-----|
| 1️⃣ بعد إزالة الموديول القديم | ✅ يعمل | ⚠️ غير متاح | Collection فارغة | فارغ |
| 2️⃣ بعد إضافة الحزمة الجديدة | ✅ يعمل | ✅ يعمل | ✅ بيانات كاملة | ✅ يعمل |
| 3️⃣ بعد تعطيل الحزمة | ✅ يعمل | ⚠️ غير متاح | Collection فارغة | فارغ |
| 4️⃣ بعد إعادة التفعيل | ✅ يعمل | ✅ يعمل | ✅ بيانات كاملة | ✅ يعمل |

---

## 🔧 استكشاف الأخطاء

### خطأ: Class not found

```
Target class [Utd\Reals\Entities\Real] does not exist.
```

**الحل:** 
1. تأكد من إضافة Autoload في composer.json
2. شغّل `composer dump-autoload`
3. تأكد من أن `DynamicRealsTrait` يستخدم `class_exists()`:

```php
public function reals()
{
    if (!class_exists(\Utd\Reals\Entities\Real::class)) {
        return $this->hasMany(\App\Models\NullModel::class)->whereRaw('1 = 0');
    }
    return $this->hasMany(\Utd\Reals\Entities\Real::class, 'user_id');
}
```

### خطأ: ServiceProvider not found

```
Class "Utd\Reals\RealsServiceProvider" not found
```

**الحل:**
1. تأكد من إضافة `"Utd\\Reals\\": "packages/Utd/Reals/src/"` في composer.json
2. شغّل `composer dump-autoload`

### خطأ: Table doesn't exist

```
SQLSTATE[42S02]: Base table or view not found: 1146 Table 'database.reals' doesn't exist
```

**الحل:** شغّل الـ migrations:

```bash
php artisan migrate
```

### خطأ: Route not found

```
Route [admin.reels.index] not defined.
```

**الحل:** تأكد من تسجيل ServiceProvider وشغّل:

```bash
php artisan route:clear
php artisan route:list | grep reals
```

---

## 📁 هيكل الحزمة الكامل

```
packages/Utd/Reals/
├── composer.json
├── config/
│   └── reals.php
├── database/
│   └── migrations/
│       ├── 2024_01_01_000001_create_reals_table.php
│       ├── 2024_01_01_000002_create_real_user_likes_table.php
│       ├── 2024_01_01_000003_create_real_user_comments_table.php
│       ├── 2024_01_01_000004_create_real_user_views_table.php
│       ├── 2024_01_01_000005_create_real_categories_table.php
│       ├── 2024_01_01_000006_create_reels_user_settings_table.php
│       └── 2024_01_01_000007_create_report_reals_table.php
├── public/
│   └── js/
│       └── reels-manager.js
├── resources/
│   ├── lang/
│   └── views/
│       ├── admin/
│       │   └── reels/
│       │       ├── index.blade.php
│       │       └── partials/
│       │           └── styles.blade.php
│       ├── layouts/
│       │   ├── admin.blade.php
│       │   └── master.blade.php
│       └── index.blade.php
├── routes/
│   ├── api.php
│   └── web.php
├── src/
│   ├── RealsServiceProvider.php
│   ├── Providers/
│   │   └── RouteServiceProvider.php
│   ├── Entities/
│   │   ├── Real.php
│   │   ├── RealUserLike.php
│   │   ├── RealUserComment.php
│   │   ├── RealUserView.php
│   │   ├── RealCategory.php
│   │   ├── ReelsUserSetting.php
│   │   └── ReportReals.php
│   ├── Services/
│   │   └── RealsService.php
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── RealsController.php
│   │   │   ├── RealsUserLikesController.php
│   │   │   ├── RealsUserCommentController.php
│   │   │   ├── ReportController.php
│   │   │   └── Web/
│   │   │       ├── AdminReelController.php
│   │   │       ├── ReelController.php
│   │   │       ├── ReelSettingsController.php
│   │   │       └── ReportRealsController.php
│   │   ├── Requests/
│   │   │   ├── RealStore.php
│   │   │   └── StoreRealComment.php
│   │   └── Services/
│   │       ├── BaseModelService.php
│   │       ├── RealLikesService.php
│   │       └── RealCommentsService.php
│   └── Transformers/
│       ├── RealsResource.php
│       ├── LikesResource.php
│       ├── RealCommentsResource.php
│       ├── UserResource.php
│       └── ProfileResource.php
└── README.md
```

---

## ✅ قائمة التحقق النهائية

### عند إزالة الموديول القديم (السيناريو 1):
- [ ] تعطيل الموديول في modules_statuses.json
- [ ] مسح Cache
- [ ] الموقع يعمل بدون أخطاء
- [ ] User Model يعمل بدون أخطاء

### عند إضافة الحزمة الجديدة (السيناريو 2):
- [ ] إضافة Autoload في composer.json
- [ ] تشغيل `composer dump-autoload`
- [ ] تسجيل ServiceProvider في config/app.php
- [ ] مسح Cache
- [ ] تشغيل Migrations (إذا لزم)
- [ ] نشر Assets
- [ ] لوحة التحكم تعمل
- [ ] API يعمل
- [ ] العلاقات تعمل

### عند تعطيل الحزمة (السيناريو 3):
- [ ] تعليق ServiceProvider في config/app.php
- [ ] مسح Cache: `php artisan optimize:clear`
- [ ] الموقع يعمل بدون أخطاء
- [ ] لا توجد Class not found errors

### عند إعادة تفعيل الحزمة (السيناريو 4):
- [ ] إزالة التعليق عن ServiceProvider
- [ ] مسح Cache: `php artisan optimize:clear`
- [ ] كل شيء يعمل كما كان

---

## 🎯 ملخص الأوامر السريعة

### التثبيت الأولي (مرة واحدة فقط):
```bash
# 1. أضف في composer.json → autoload.psr-4:
#    "Utd\\Reals\\": "packages/Utd/Reals/src/"

# 2. أضف في config/app.php → providers:
#    Utd\Reals\RealsServiceProvider::class,

# 3. شغّل:
composer dump-autoload
php artisan migrate
php artisan vendor:publish --tag=reals-assets --force
php artisan optimize:clear
```

### لتفعيل الحزمة (بعد التثبيت):
```bash
# 1. أزل التعليق عن ServiceProvider في config/app.php
# 2. شغّل:
php artisan optimize:clear
```

### لتعطيل الحزمة:
```bash
# 1. علّق ServiceProvider في config/app.php
# 2. شغّل:
php artisan optimize:clear
```

---

**ملاحظة:** هذا الدليل للتثبيت اليدوي بدون Composer require. مناسب لبيع الحزمة للعملاء كملفات منفصلة.
