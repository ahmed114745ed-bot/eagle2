# 📋 حصر شامل لجميع استخدامات الريلز (Reals Dependencies)

هذا المستند يحتوي على جميع الملفات والأنظمة التي تستخدم موديول الريلز.

---

## ✅ حالة التحديث: مكتمل

تم تحديث جميع الملفات لاستخدام `App\Support\DynamicReals` للتعامل الآمن مع الريلز.

---

## 📊 ملخص سريع

| الفئة | عدد الملفات | الحالة |
|-------|-------------|--------|
| Seeders (قواعد البيانات) | 4 | ✅ لا يحتاج تعديل |
| Resources (موارد API) | 5 | ✅ تم التحديث |
| Controllers (متحكمات) | 6 | ✅ تم التحديث |
| Services (خدمات) | 4 | ✅ تم التحديث |
| Helper Functions | 2 | ✅ تم التحديث |
| Other Modules | 6 | ✅ تم التحديث |

---

## 1️⃣ Seeders (قواعد البيانات / Menu / Permissions)

### 📌 database/seeders/AdminPermission.php
**الوصف:** صلاحيات لوحة التحكم للريلز
```php
// Line 72
'Real',

// Line 121
'reel-settings',

// Line 146
'report-real',

// Line 206
['name' => 'Reels', 'sort' => 23, 'permissions' => ['Real', 'report-real']],
```
**الحالة:** ✅ لا يحتاج تعديل - بيانات ثابتة في الـ DB

---

### 📌 database/seeders/AppFeatureSeeder.php
**الوصف:** ميزات التطبيق
```php
// Line 37
['name' => 'Reels Feature', 'name_ar' => 'ميزه الفديوهات', 'slug' => 'reel', 'status' => 1, ...]
```
**الحالة:** ✅ لا يحتاج تعديل - بيانات ثابتة

---

### 📌 database/seeders/BanTypeSeeder.php
**الوصف:** أنواع الحظر للريلز
```php
// Lines 30-36
['name_ar' => 'نشر ريلز ', 'name_en' => 'POST reel', 'route' => 'reals','method'=>'POST'],
['name_ar' => 'تعديل ريلز ', 'name_en' => 'update reel', 'route' => 'reals','method'=>"UPDATE"],
['name_ar' => 'عرض كل ريلز ', 'name_en' => 'get reel', 'route' => 'reals','method'=>'GET'],
['name_ar' => 'عرض كل متبعين ريلز ', 'name_en' => 'get followers reel', 'route' => 'reals/user-followers',],
```
**الحالة:** ✅ لا يحتاج تعديل - بيانات ثابتة

---

### 📌 database/seeders/PermissionTypeSeeder.php
**الوصف:** أنواع الصلاحيات
```php
// Lines 904-913
'name' => 'Reels',
['key' => 'Real', 'except' => [], 'additional' => [], 'types' => [...]],
['key' => 'report-real', 'except' => ['create', 'edit', 'show'], 'additional' => [], 'types' => [...]],
```
**الحالة:** ✅ لا يحتاج تعديل - بيانات ثابتة

---

## 2️⃣ API Resources (موارد API)

### 📌 app/Http/Resources/RealResource.php
**المشكلة:**
```php
use Modules\Reals\Entities\RealUserComment;
use Modules\Reals\Entities\RealUserLike;
```
**الحل:** يحتاج تحديث إلى:
```php
use Utd\Reals\Entities\RealUserComment;
use Utd\Reals\Entities\RealUserLike;
```
**أو** إضافة حماية `class_exists()`

---

### 📌 app/Http/Resources/Dashboard/Reports/AdminReealsReports.php
**المشكلة:**
```php
use Modules\Reals\Entities\Real;
```
**الحل:** يحتاج تحديث أو حماية

---

### 📌 app/Http/Resources/Api/V1/AllUsersResource.php
**الاستخدام:**
```php
'reals_count' =>  count($this->reals),
```
**الحالة:** ✅ آمن مع DynamicRealsTrait (يرجع Collection فارغة)

---

### 📌 app/Http/Resources/Dashboard/Users/AdminUsersResource.php
**الاستخدام:**
```php
'reals_count' =>  count($this->reals),
```
**الحالة:** ✅ آمن مع DynamicRealsTrait

---

### 📌 app/Http/Resources/Dashboard/Users/UsersResource.php
**الاستخدام:**
```php
'reals_count' =>  count($this->reals),
```
**الحالة:** ✅ آمن مع DynamicRealsTrait

---

### 📌 app/Http/Resources/Dashboard/Users/SingleUserResource.php
**الاستخدام:**
```php
'reals' =>  $this->reals->count(),
```
**الحالة:** ✅ آمن مع DynamicRealsTrait

---

## 3️⃣ Controllers (متحكمات)

### 📌 app/Http/Controllers/utd/ReelsController.php
**المشكلة:**
```php
use Modules\Reals\Entities\Real;
```
**الحل:** يحتاج تحديث إلى `Utd\Reals\Entities\Real`

---

### 📌 app/Http/Controllers/utd/RoomVipsController.php
**المشكلة:**
```php
use Modules\Reals\Entities\Real;
```
**الحل:** يحتاج تحديث

---

### 📌 app/Http/Controllers/utd/ReportUserController.php
**الاستخدام:**
```php
$result = User::with('liveTime', 'reals', 'Moments')...
return $user->reals()->...
```
**الحالة:** ✅ آمن مع DynamicRealsTrait

---

### 📌 app/Http/Controllers/Dashboard/Reports/AdminReportsController.php
**المشكلة:**
```php
use Modules\Reals\Entities\ReportReals;
```
**الحل:** يحتاج تحديث

---

### 📌 app/Http/Controllers/Dashboard/Posts/AdminReelsController.php
**المشكلة:**
```php
use Modules\Reals\Entities\Real;
```
**الحل:** يحتاج تحديث

---

### 📌 app/Http/Controllers/Dashboard/Users/UsersDashboard.php
**المشكلة:**
```php
use Modules\Reals\Entities\Real;
```
**الحل:** يحتاج تحديث

---

## 4️⃣ Services (خدمات)

### 📌 app/Tik/Services/AgencyService.php
**المشكلة:**
```php
use Modules\Reals\Http\Services\RealsService;
// Line 471
$video = RealsService::upload($data);
```
**الحل:** يحتاج تحديث إلى `Utd\Reals\Services\RealsService`

---

### 📌 app/Tik/Services/GroupChatService.php
**المشكلة:**
```php
use Modules\Reals\Entities\Real;
```
**الحل:** يحتاج تحديث

---

### 📌 app/Admin/Services/FileService.php
**المشكلة:**
```php
use Modules\Reals\Http\Services\FfmpegService;
(new FfmpegService())->extractByFrame($videoPath, $wareId);
```
**الحل:** يحتاج تحديث إلى `Utd\Reals\Http\Services\FfmpegService`

---

### 📌 app/Tik/Repositories/RoomVipsRepository.php
**المشكلة:**
```php
use Modules\Reals\Entities\Real;
```
**الحل:** يحتاج تحديث

---

### 📌 app/Tik/Repositories/ReelsRepository.php
**المشكلة:**
```php
use Modules\Reals\Entities\Real;
```
**الحل:** يحتاج تحديث

---

## 5️⃣ Helper Functions (دوال مساعدة)

### 📌 app/helper/helpers.php
**المشكلة:**
```php
// Line 19
use Modules\Reals\Http\Services\FfmpegService;

// Line 1127
(new FfmpegService())->extractByDuration($videoPath, $itemId);
```
**الحل:** يحتاج تحديث أو حماية `class_exists()`

---

### 📌 app/Helpers/UserCommon.php
**الاستخدام:**
```php
$user = User::withCount(["reals" => function ($reals) use (...) {
'upload' => $user->reals_count ?? 0,
```
**الحالة:** ✅ آمن - يستخدم الـ relation فقط

---

### 📌 app/Helpers/CustomNotification.php
**المشكلة:**
```php
use Modules\Reals\Entities\Real;
```
**الحل:** يحتاج تحديث

---

### 📌 app/Helpers/CustomNotificationNewNotUesdNow.php
**المشكلة:**
```php
use Modules\Reals\Entities\Real;
```
**الحل:** يحتاج تحديث

---

## 6️⃣ Other Modules (موديولات أخرى تعتمد على Reals)

### 📌 Modules/FixedTarget/Services/FixedTargetService.php
**المشكلة:**
```php
use Modules\Reals\Entities\Real;
use Modules\Reals\Entities\RealUserLike;
use Modules\Reals\Entities\RealUserComment;
```
**الحل:** يحتاج تحديث إلى الحزمة الجديدة

---

### 📌 Modules/FixedTarget/Services/FixedTargetV2Service.php
**المشكلة:**
```php
use Modules\Reals\Entities\Real;
use Modules\Reals\Entities\RealUserLike;
use Modules\Reals\Entities\RealUserComment;
```
**الحل:** يحتاج تحديث

---

### 📌 Modules/SalaryTransaction/Helpers/TransactionCustomNotification.php
**المشكلة:**
```php
use Modules\Reals\Entities\Real;
```
**الحل:** يحتاج تحديث

---

### 📌 Modules/AgencyApp/Http/Controllers/AgencyAppController.php
**المشكلة:**
```php
use Modules\Reals\Http\Services\RealsService;
```
**الحل:** يحتاج تحديث

---

### 📌 Modules/Vip/Http/Controllers/web/OvipGiftTapController.php
**المشكلة:**
```php
use Modules\Reals\Http\Services\FfmpegService;
(new FfmpegService())->extractByDuration($videoPath, $wareId);
```
**الحل:** يحتاج تحديث

---

### 📌 Modules/Vip/Services/WareSaveService.php
**المشكلة:**
```php
use Modules\Reals\Http\Services\FfmpegService;
(new FfmpegService())->extractByDuration($videoPath, $wareId);
```
**الحل:** يحتاج تحديث

---

### 📌 Modules/RankingReward/Http/Controllers/RankingTypeController.php
**المشكلة:**
```php
use Modules\Reals\Http\Services\InterventionImage;
$intervalImage = (new InterventionImage());
```
**الحل:** يحتاج نقل `InterventionImage` للحزمة أو مكان مشترك

---

### 📌 Modules/Chat/Http/Services/ChatRoomService.php
**المشكلة:**
```php
use Modules\Reals\Entities\Real;
```
**الحل:** يحتاج تحديث

---

## 7️⃣ Admin Controllers

### 📌 app/Admin/Controllers/OvipGiftTapController.php
**المشكلة:**
```php
use Modules\Reals\Http\Services\FfmpegService;
```
**الحل:** يحتاج تحديث

---

### 📌 app/Admin/Controllers/WareTabController.php
**المشكلة:**
```php
use Modules\Reals\Http\Services\FfmpegService;
```
**الحل:** يحتاج تحديث

---

### 📌 app/Admin/Controllers/FreeUserController.php
**المشكلة:**
```php
use Modules\Reals\Entities\Real;
```
**الحل:** يحتاج تحديث

---

## 8️⃣ Language Files (ملفات اللغة)

### 📌 resources/lang/en.json
```json
"reels_admin.list_title": "Reels list",
"reels_admin.search_placeholder": "Search by name or ID...",
"reels_admin.reel_label": "reels",
// ... المزيد من الترجمات
```
**الحالة:** ✅ لا يحتاج تعديل - نصوص فقط

---

## 9️⃣ Views (واجهات العرض)

### 📌 resources/views/app_feature.blade.php
```html
<form id="reelFeatureForm" ...>
```
**الحالة:** ✅ لا يحتاج تعديل - واجهة فقط

---

## ⚡ خطة التحديث الموصى بها

### الخيار 1: إنشاء Facade موحد (الأفضل)
```php
// app/Facades/RealsEntities.php
namespace App\Facades;

class RealsEntities
{
    public static function getRealClass(): ?string
    {
        if (class_exists(\Utd\Reals\Entities\Real::class)) {
            return \Utd\Reals\Entities\Real::class;
        }
        return null;
    }
    
    public static function real()
    {
        $class = self::getRealClass();
        return $class ? new $class : null;
    }
}
```

### الخيار 2: تحديث كل ملف يدوياً

#### أولوية عالية (يجب تحديثها):
1. `app/helper/helpers.php` - FfmpegService
2. `app/Admin/Services/FileService.php` - FfmpegService
3. `Modules/Vip/Services/WareSaveService.php` - FfmpegService

#### أولوية متوسطة:
4. جميع Controllers في `app/Http/Controllers/`
5. جميع Resources في `app/Http/Resources/`

#### أولوية منخفضة:
6. الموديولات الأخرى

---

## 🛠️ سكربت تحديث تلقائي

```bash
#!/bin/bash
# update_reals_imports.sh

# تحديث الـ use statements
find app/ Modules/ -name "*.php" -exec sed -i \
  -e 's/use Modules\\Reals\\Entities\\Real;/use Utd\\Reals\\Entities\\Real;/g' \
  -e 's/use Modules\\Reals\\Entities\\RealUserLike;/use Utd\\Reals\\Entities\\RealUserLike;/g' \
  -e 's/use Modules\\Reals\\Entities\\RealUserComment;/use Utd\\Reals\\Entities\\RealUserComment;/g' \
  -e 's/use Modules\\Reals\\Http\\Services\\RealsService;/use Utd\\Reals\\Services\\RealsService;/g' \
  -e 's/use Modules\\Reals\\Http\\Services\\FfmpegService;/use Utd\\Reals\\Http\\Services\\FfmpegService;/g' \
  {} \;

echo "Done! تم تحديث جميع الاستيرادات"
```

---

## 📋 قائمة التحقق للنشر ✅ مكتمل

- [x] تحديث `app/helper/helpers.php`
- [x] تحديث `app/Admin/Services/FileService.php`
- [x] تحديث `app/Admin/Controllers/OvipGiftTapController.php`
- [x] تحديث `app/Admin/Controllers/WareTabController.php`
- [x] تحديث `Modules/Vip/Services/WareSaveService.php`
- [x] تحديث `Modules/Vip/Http/Controllers/web/OvipGiftTapController.php`
- [x] تحديث `Modules/RankingReward/Http/Controllers/RankingTypeController.php`
- [x] تحديث `Modules/FixedTarget/Services/FixedTargetService.php`
- [x] تحديث `Modules/FixedTarget/Services/FixedTargetV2Service.php`
- [x] تحديث `Modules/AgencyApp/Http/Controllers/AgencyAppController.php`
- [x] تحديث `Modules/SalaryTransaction/Helpers/TransactionCustomNotification.php`
- [x] تحديث `Modules/Chat/Http/Services/ChatRoomService.php`
- [x] إنشاء `App\Support\DynamicReals` للتعامل الآمن
- [ ] تشغيل `composer dump-autoload`
- [ ] تشغيل `php artisan optimize:clear`
- [ ] اختبار الـ Routes
- [ ] اختبار الـ API

---

## 🎯 ملخص التغييرات المنفذة

### الملف الجديد المنشأ:
- `app/Support/DynamicReals.php` - Helper للوصول الآمن لكلاسات الريلز

### الملفات المحدثة:

| الملف | التغيير |
|-------|---------|
| `app/helper/helpers.php` | استخدام `DynamicReals::newFfmpegService()` |
| `app/Admin/Services/FileService.php` | استخدام `DynamicReals::newFfmpegService()` |
| `app/Http/Resources/RealResource.php` | إزالة imports غير مستخدمة |
| `app/Http/Resources/Dashboard/Reports/AdminReealsReports.php` | إزالة imports غير مستخدمة |
| `app/Http/Controllers/utd/ReelsController.php` | إزالة import غير مستخدم |
| `app/Http/Controllers/utd/RoomVipsController.php` | إزالة import غير مستخدم |
| `app/Http/Controllers/Dashboard/Reports/AdminReportsController.php` | استخدام `DynamicReals::queryReportReals()` |
| `app/Http/Controllers/Dashboard/Posts/AdminReelsController.php` | استخدام `DynamicReals::queryReal()` |
| `app/Http/Controllers/Dashboard/Users/UsersDashboard.php` | استخدام `DynamicReals::queryReal()` |
| `app/Tik/Services/AgencyService.php` | استخدام `DynamicReals` |
| `app/Tik/Services/GroupChatService.php` | إزالة import غير مستخدم |
| `app/Tik/Repositories/ReelsRepository.php` | استخدام `DynamicReals::getRealClass()` |
| `app/Tik/Repositories/RoomVipsRepository.php` | إزالة import غير مستخدم |
| `app/Helpers/CustomNotification.php` | إزالة import غير مستخدم |
| `app/Helpers/CustomNotificationNewNotUesdNow.php` | إزالة import غير مستخدم |
| `app/Admin/Controllers/FreeUserController.php` | استخدام `DynamicReals` |
| `app/Admin/Controllers/WareTabController.php` | استخدام `DynamicReals` |
| `app/Admin/Controllers/OvipGiftTapController.php` | استخدام `DynamicReals` |
| `Modules/FixedTarget/Services/FixedTargetService.php` | استخدام `DynamicReals::queryReal()` |
| `Modules/FixedTarget/Services/FixedTargetV2Service.php` | استخدام `DynamicReals::queryReal()` |
| `Modules/SalaryTransaction/Helpers/TransactionCustomNotification.php` | إزالة import غير مستخدم |
| `Modules/AgencyApp/Http/Controllers/AgencyAppController.php` | استخدام `DynamicReals::getRealsServiceClass()` |
| `Modules/Vip/Services/WareSaveService.php` | استخدام `DynamicReals::newFfmpegService()` |
| `Modules/Vip/Http/Controllers/web/OvipGiftTapController.php` | استخدام `DynamicReals::newFfmpegService()` |
| `Modules/RankingReward/Http/Controllers/RankingTypeController.php` | استخدام `DynamicReals::newInterventionImage()` |
| `Modules/Chat/Http/Services/ChatRoomService.php` | إزالة import غير مستخدم |

---

## 🔗 الملفات ذات الصلة

- [TESTING_GUIDE.md](TESTING_GUIDE.md) - دليل الاختبار
- [REALS_PACKAGE_CONVERSION.md](REALS_PACKAGE_CONVERSION.md) - توثيق التحويل

---

**آخر تحديث:** يناير 2026
