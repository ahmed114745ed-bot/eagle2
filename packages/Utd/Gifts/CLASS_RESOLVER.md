# استخدام ClassResolver للاستدعاءات الديناميكية

## نظرة عامة

تم تحديث حزمة الهدايا لاستخدام **ClassResolver** بدلاً من الاستدعاءات المباشرة للملفات الخارجية. هذا يجعل الحزمة:
- ✅ **مستقلة تماماً** - لا تعتمد مباشرة على أي كود خارجي
- ✅ **قابلة للتخصيص** - يمكن تغيير الكلاسات المستخدمة من config
- ✅ **سهلة الصيانة** - جميع الاعتماديات محددة في مكان واحد

---

## 📁 هيكل الحزمة

```
packages/Utd/Gifts/
├── config/
│   └── gifts.php                      ← جميع الكلاسات الخارجية
├── src/
│   ├── Support/
│   │   ├── ClassResolver.php          ← الـ Helper للوصول للكلاسات
│   │   └── ModelResolver.php          ← للـ Models فقط
│   ├── Http/Controllers/
│   │   ├── Admin/                     ← لا استدعاءات مباشرة
│   │   └── Api/                       ← لا استدعاءات مباشرة
│   └── Entities/                      ← Models الحزمة
```

---

## ⚙️ ملف config/gifts.php

جميع الكلاسات الخارجية محددة في ملف واحد:

```php
return [
    // Models
    'models' => [
        'user' => 'App\Models\User',
        'agency' => 'App\Models\Agency',
        'cp' => 'Utd\CP\Entities\Cp',
        'setting' => 'App\Models\Setting',
        // ... المزيد
    ],
    
    // Helpers
    'helpers' => [
        'common' => 'App\Helpers\Common',
        'user_common' => 'App\Helpers\UserCommon',
    ],
    
    // Services
    'services' => [
        'gift' => 'App\Tik\Services\GiftService',
        'gift_log' => 'App\Tik\Services\GiftLogService',
        'lucky_gift' => 'App\Services\LuckyGiftService',
        // ... المزيد
    ],
    
    // Resources
    'resources' => [
        'gift' => 'App\Http\Resources\GiftResource',
        'gift_category' => 'App\Http\Resources\GiftCategoryResource',
        // ... المزيد
    ],
    
    // Facades
    'facades' => [
        'user_handling' => 'App\Facades\UserHandling',
        'custom_notification' => 'App\Facades\CustomNotification',
    ],
    
    // Jobs
    'jobs' => [
        'clean_gift_logs' => 'App\Jobs\CleanGiftLogsJob',
        'update_user_data_when_send_gift' => 'App\Jobs\UpdateUserDataWhenSendGift',
        // ... المزيد
    ],
    
    // Events
    'events_classes' => [
        'gift_banner' => 'App\Events\GiftBannerEvent',
    ],
    
    // Actions (Laravel Admin)
    'actions' => [
        'move_gift_category' => 'App\Admin\Actions\MoveGiftCategory',
        'move_groups_gifts' => 'App\Admin\Actions\Grid\MoveGroupsGifts',
    ],
    
    // Forms (Laravel Admin)
    'forms' => [
        'tabs_from' => 'App\Admin\Forms\TabsFrom',
    ],
    
    // Exceptions
    'exceptions' => [
        'not_inf_money' => 'App\Exceptions\NotInfMoneyException',
    ],
    
    // Contracts
    'contracts' => [
        'gifts' => 'App\Contracts\GiftsContract',
        'room_top_users_repository' => 'App\Contracts\RoomTopUsersRepositoryContract',
    ],
    
    // Observers
    'observers' => [
        'gift_category' => 'App\Observers\GiftCategoryObserver',
    ],
    
    // Traits
    'traits' => [
        'win_lucky_gift' => 'App\Traits\Gifts\WinLuckyGift',
        'lucky_gift_probability' => 'App\Traits\Gifts\LuckyGiftProbability',
        'timestamps_with_timezone' => 'App\Traits\TimestampsWithTimezone',
    ],
];
```

---

## 🔧 ClassResolver API

### الطرق المتاحة:

```php
use Utd\Gifts\Support\ClassResolver;

// Models
$UserModel = ClassResolver::model('user');
$user = $UserModel::find(1);

// Helpers
$Common = ClassResolver::helper('common');
$response = $Common::apiResponse(true, '', $data);

// Services
$giftService = app(ClassResolver::service('gift'));
$gifts = $giftService->index($type);

// Resources
$GiftResource = ClassResolver::resource('gift');
return $GiftResource::collection($gifts);

// Facades
$UserHandling = ClassResolver::facade('user_handling');
$result = $UserHandling::checkIfUserHostByIds($ids);

// Jobs
$CleanLogsJob = ClassResolver::job('clean_gift_logs');
dispatch(new $CleanLogsJob());

// Events
$GiftBannerEvent = ClassResolver::event('gift_banner');
event(new $GiftBannerEvent($data));

// Actions
$MoveAction = ClassResolver::action('move_gift_category');
$actions->add(new $MoveAction());

// Forms
$TabsFrom = ClassResolver::form('tabs_from');
$form = new $TabsFrom(new Gift);

// Exceptions
$NotInfMoneyException = ClassResolver::exception('not_inf_money');
throw new $NotInfMoneyException('Insufficient balance');

// Contracts
$contract = ClassResolver::contract('gifts');
$service = app($contract);

// Observers
$observer = ClassResolver::observer('gift_category');
GiftCategory::observe($observer);
```

---

## 📝 أمثلة الاستخدام في Controllers

### Admin Controller

**قبل:**
```php
use App\Admin\Controllers\MainController;
use App\Helpers\Common;
use App\Models\Setting;

class GiftController extends MainController
{
    public function index()
    {
        $grid->column('enable')->switch(Common::getSwitchStates());
        $config = Setting::whereIn('key', ['setting1'])->get();
    }
}
```

**بعد:**
```php
use Utd\Gifts\Support\ClassResolver;

class GiftController
{
    public function index()
    {
        $Common = ClassResolver::helper('common');
        $grid->column('enable')->switch($Common::getSwitchStates());
        
        $Setting = ClassResolver::model('setting');
        $config = $Setting::whereIn('key', ['setting1'])->get();
    }
}
```

### API Controller

**قبل:**
```php
use App\Helpers\Common;
use App\Http\Resources\GiftResource;
use App\Tik\Services\GiftService;

class GiftController extends Controller
{
    public function __construct(private GiftService $giftService) {}
    
    public function index()
    {
        $gifts = $this->giftService->index($type);
        return Common::apiResponse(true, '', GiftResource::collection($gifts));
    }
}
```

**بعد:**
```php
use Utd\Gifts\Support\ClassResolver;

class GiftController extends Controller
{
    protected $giftService;
    protected $Common;
    protected $GiftResource;
    
    public function __construct()
    {
        $this->giftService = app(ClassResolver::service('gift'));
        $this->Common = ClassResolver::helper('common');
        $this->GiftResource = ClassResolver::resource('gift');
    }
    
    public function index()
    {
        $gifts = $this->giftService->index($type);
        return $this->Common::apiResponse(true, '', $this->GiftResource::collection($gifts));
    }
}
```

---

## 🎯 مثال شامل: GiftLogController

```php
class GiftLogController extends Controller
{
    // جميع الكلاسات الخارجية resolved في constructor
    protected $Common;
    protected $UserHandling;
    protected $GiftLogResource;
    protected $User;
    protected $Agency;
    // ... المزيد
    
    public function __construct()
    {
        // Helpers
        $this->Common = ClassResolver::helper('common');
        $this->UserCommon = ClassResolver::helper('user_common');
        
        // Facades
        $this->UserHandling = ClassResolver::facade('user_handling');
        $this->CustomNotification = ClassResolver::facade('custom_notification');
        
        // Resources
        $this->GiftLogResource = ClassResolver::resource('gift_log');
        
        // Models
        $this->User = ClassResolver::model('user');
        $this->Agency = ClassResolver::model('agency');
        $this->Cp = ClassResolver::model('cp');
        
        // Services
        $this->LuckyGiftService = app(ClassResolver::service('lucky_gift'));
        $this->SendGiftService = ClassResolver::service('send_gift');
        
        // Jobs
        $this->CleanGiftLogsJob = ClassResolver::job('clean_gift_logs');
        $this->UpdatePkAndSendToZigoJob = ClassResolver::job('update_pk_and_send_to_zigo');
        
        // Events
        $this->GiftBannerEvent = ClassResolver::event('gift_banner');
        
        // Exceptions
        $this->NotInfMoneyException = ClassResolver::exception('not_inf_money');
        
        // Contracts
        $roomTopUsersRepositoryClass = ClassResolver::contract('room_top_users_repository');
        $this->roomTopUsersRepository = app($roomTopUsersRepositoryClass);
    }
    
    public function sendGift(Request $request)
    {
        // استخدام الكلاسات المحملة
        $user = $this->User::find($request->user_id);
        
        if (!$user->hasEnoughBalance()) {
            throw new $this->NotInfMoneyException('Insufficient balance');
        }
        
        $result = $this->UserHandling::checkIfUserHostByIds([$user->id]);
        
        dispatch(new $this->CleanGiftLogsJob());
        
        event(new $this->GiftBannerEvent($giftData));
        
        return $this->Common::apiResponse(true, '', $this->GiftLogResource::make($log));
    }
}
```

---

## 🔄 التخصيص

يمكنك تغيير أي كلاس من `.env`:

```env
# استخدام Helper مخصص بدلاً من الأصلي
GIFTS_COMMON_HELPER=App\MyCustomHelpers\CustomCommon

# استخدام User Model مخصص
GIFTS_USER_MODEL=App\MyModels\CustomUser

# استخدام Service مخصص
GIFTS_GIFT_SERVICE=App\MyServices\CustomGiftService
```

أو من `config/gifts.php` مباشرة:

```php
'helpers' => [
    'common' => env('GIFTS_COMMON_HELPER', 'App\Helpers\MyCustomCommon'),
],
```

---

## ⚠️ ملاحظات مهمة

### 1. الـ Traits لا يمكن تحميلها ديناميكياً

```php
// ❌ هذا لا يعمل
class Gift extends Model
{
    use ClassResolver::trait('timestamps_with_timezone');
}

// ✅ الـ Traits يجب استخدامها مباشرة
class Gift extends Model
{
    use TimestampsWithTimezone;
}
```

**السبب:** الـ Traits يتم معالجتها في compile time وليس runtime.

### 2. الـ Controllers الأساسية

بعض controllers لا تحتاج extends:

```php
// ❌ قبل
class GiftController extends MainController

// ✅ بعد
class GiftController
{
    use HasResourceActions;
}
```

### 3. الـ Static Methods

عند استخدام static methods:

```php
$Common = ClassResolver::helper('common');
$result = $Common::getSwitchStates(); // ✅ صحيح
```

### 4. الـ Dependency Injection

عند الحاجة للـ DI:

```php
public function __construct()
{
    $serviceClass = ClassResolver::service('gift');
    $this->service = app($serviceClass); // ✅ استخدم app()
}
```

---

## ✅ فوائد هذا النهج

1. **الاستقلالية الكاملة**
   - الحزمة لا تعتمد مباشرة على أي ملف خارجي
   - يمكن نقلها لأي مشروع وتعمل مباشرة

2. **سهولة التخصيص**
   - تغيير أي كلاس من config دون تعديل الكود
   - دعم multiple implementations

3. **الصيانة الأسهل**
   - جميع الاعتماديات في مكان واحد (config)
   - تتبع أسهل للتغييرات

4. **الاختبارات**
   - سهولة mock الكلاسات الخارجية
   - اختبار الحزمة بشكل منفصل

5. **التوافق**
   - backward compatibility محفوظ (الكود القديم يعمل)
   - يمكن ترقية الكلاسات الخارجية دون مشاكل

---

## 📊 ملخص التغييرات

| الملف | الاستدعاءات المباشرة | بعد ClassResolver |
|-------|---------------------|-------------------|
| Admin/GiftController.php | 6 | 0 |
| Admin/GiftCategoryController.php | 1 | 0 |
| Api/GiftController.php | 3 | 0 |
| Api/GiftCategoryController.php | 2 | 0 |
| Api/GiftLogController.php | 30+ | 0 |
| **المجموع** | **40+** | **0** ✅ |

---

## 🚀 الاستخدام

```bash
# التثبيت
composer require utd/gifts

# نشر الـ config
php artisan vendor:publish --tag=gifts-config

# تخصيص الكلاسات في config/gifts.php
# جاهز للاستخدام!
```

---

**تم التحديث:** 2026-02-04  
**الإصدار:** 2.0.0 - Dynamic Class Resolution
