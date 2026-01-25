# UTD Reals Package

حزمة الريلز (Reels) المستقلة - يمكن بيعها كمنتج منفصل.

## التثبيت

```bash
composer require utd/reals
```

أو للتطوير المحلي:

```json
{
    "repositories": [
        {
            "type": "path",
            "url": "packages/Utd/Reals",
            "options": {
                "symlink": true
            }
        }
    ],
    "require": {
        "utd/reals": "@dev"
    }
}
```

## الإعداد

### 1. نشر الإعدادات (اختياري)

```bash
php artisan vendor:publish --tag=reals-config
```

### 2. تشغيل الـ Migrations

```bash
php artisan migrate
```

## الاستخدام

### في الـ Controller

```php
use App\Contracts\RealsContract;

class MyController extends Controller
{
    public function index(RealsContract $reals)
    {
        $userReals = $reals->getUserReals($userId, $currentUserId);
        
        $allReals = $reals->getAllReals($userId);
        
        $result = $reals->toggleLike($realId, $userId);
    }
}
```

### في الـ User Model

الـ `DynamicRealsTrait` يوفر:

```php
$user->reals;              // ريلز المستخدم
$user->realLikes;          // إعجابات الريلز
$user->realComments;       // تعليقات الريلز
$user->getDisplayReals(3); // آخر 3 ريلز
$user->reals_count;        // عدد الريلز
$user->hasRealsFeature();  // هل الميزة متوفرة؟
```

## API Endpoints

| Method | Endpoint | الوصف |
|--------|----------|-------|
| GET | `/api/reals` | جلب كل الريلز |
| GET | `/api/reals/user/{id}` | ريلز مستخدم معين |
| GET | `/api/reals/my-reals` | ريلزي |
| GET | `/api/reals/user-followers` | ريلز المتابَعين |
| POST | `/api/reals` | إنشاء ريل جديد |
| GET | `/api/reals/{id}` | عرض ريل |
| PUT | `/api/reals/{id}` | تحديث ريل |
| DELETE | `/api/reals/{id}` | حذف ريل |
| GET | `/api/reals/{id}/like` | إعجابات الريل |
| POST | `/api/reals/{id}/like` | إضافة/إزالة إعجاب |
| GET | `/api/reals/{id}/comment` | تعليقات الريل |
| POST | `/api/reals/{id}/comment` | إضافة تعليق |

## لوحة التحكم (Admin Panel)

الحزمة تتضمن لوحة تحكم متكاملة للـ Admin:

### صفحات لوحة التحكم

| الرابط | الوصف |
|--------|-------|
| `/admin/view/reels` | واجهة إدارة الريلز المتقدمة |
| `/admin/reels` | قائمة الريلز (Grid) |
| `/admin/report-reals` | بلاغات الريلز |
| `/admin/reels-settings` | إعدادات الريلز |

### ميزات لوحة التحكم

- ✅ عرض الريلز بتصميم حديث
- ✅ تشغيل الفيديو مباشرة
- ✅ عرض الإعجابات والتعليقات
- ✅ تعديل العنوان والوصف
- ✅ حذف الريلز
- ✅ البحث والفلترة
- ✅ دعم الموبايل والديسكتوب

### نشر الـ Assets

```bash
# نشر ملفات JavaScript للوحة التحكم
php artisan vendor:publish --tag=reals-assets
```

## الإعدادات

```php
// config/reals.php

return [
    'enabled' => true,
    
    'storage' => [
        'disk' => 'public',
        'path' => 'reals',
    ],
    
    'video' => [
        'max_duration' => 60, // بالثواني
        'max_size' => 50 * 1024 * 1024, // 50MB
    ],
    
    'pagination' => [
        'feed' => 10,
        'user_reals' => 10,
    ],
    
    'reports' => [
        'auto_hide_threshold' => 5,
    ],
];
```

## السلوك عند عدم تثبيت الـ Package

عندما لا تكون الـ Package مثبتة:

- `RealsContract` يستخدم `NullRealsService`
- جميع الدوال ترجع قيم فارغة
- `$user->reals` يرجع collection فارغ
- `$user->hasRealsFeature()` يرجع `false`
- لا توجد أخطاء - التطبيق يعمل بشكل طبيعي

## البنية

```
packages/Utd/Reals/
├── composer.json
├── config/
│   └── reals.php
├── database/
│   └── migrations/
├── routes/
│   ├── api.php
│   └── web.php
└── src/
    ├── RealsServiceProvider.php
    ├── Entities/
    │   ├── Real.php
    │   ├── RealCategory.php
    │   ├── RealUserComment.php
    │   ├── RealUserLike.php
    │   ├── RealUserView.php
    │   ├── ReelsUserSetting.php
    │   └── ReportReals.php
    ├── Http/
    │   ├── Controllers/
    │   ├── Requests/
    │   └── Services/
    ├── Providers/
    │   └── RouteServiceProvider.php
    ├── Services/
    │   └── RealsService.php
    └── Transformers/
```

## الترخيص

Proprietary - UTD Team



# 1. تحديث الـ autoload
composer dump-autoload

# 2. تثبيت الـ Package
composer require utd/reals

# 3. مسح الـ Cache
php artisan cache:clear
php artisan config:clear

# 4. اختبار بدون Package
composer remove utd/reals
# التطبيق يجب أن يعمل - NullRealsService يُرجع قيم فارغة

# 5. اختبار مع Package
composer require utd/reals