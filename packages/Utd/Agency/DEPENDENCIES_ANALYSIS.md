# تقرير تحليل الاعتماديات الخارجية - حزمة Agency

## 1. الاعتماديات على Models من App

### Models الأساسية (لا يمكن نقلها - جزء من Core):
- `App\Models\User` - المستخدمين الأساسيين
- `App\Models\Agency` - يجب نقله للحزمة أو Entities
- `App\Models\Admin` - admin users
- `App\Models\Config` - الإعدادات
- `App\Models\Setting` - الإعدادات
- `App\Models\Language` - اللغات
- `App\Models\Country` - الدول

### Models متعلقة بالحزمة (يمكن نقلها):
- `App\Models\AgencyJoinRequest` - يجب نقله إلى Entities
- `App\Models\UsersJoinedAgency` - يجب نقله إلى Entities
- `App\Models\AgencyUserJob` - يجب نقله إلى Entities
- `App\Models\AgencySallary` - يجب نقله إلى Entities
- `App\Models\AgencyMangerPullingOut` - يجب نقله إلى Entities

### Models خارجية (اعتماديات):
- `App\Models\GiftLog` - الهدايا
- `App\Models\UserSallary` - الرواتب
- `App\Models\UserTarget` - الأهداف
- `App\Models\Charge` - الشحن
- `App\Models\SalaryTrx` - معاملات الرواتب
- `App\Models\Follow` - المتابعة
- `App\Models\LiveTime` - وقت البث
- `App\Models\ProfileVisitor` - زوار البروفايل
- `App\Models\Agent` - الوكلاء
- `App\Models\PaymentGateway` - بوابات الدفع
- `App\Models\Room` - الغرف
- `App\Models\Gift` - الهدايا
- `App\Models\Ware` - المستودعات
- `App\Models\Bd` - BD
- `App\Models\CoinLog` - سجل العملات

## 2. الاعتماديات على Services

### Services من App (اعتماد مباشر):
- `App\Tik\Services\AgencyService` - خدمة الوكالة الأساسية
- `App\Tik\Services\ChargeRepoService` - خدمة الشحن
- `App\Tik\Services\AgencyHostInviteService` - خدمة دعوات المستضيفين
- `App\Admin\Services\AgencyService` - خدمة admin للوكالة
- `App\Admin\Services\UserService` - خدمة المستخدمين
- `App\Services\AppFeatureService` - خدمة فحص الميزات

**التوصية**: يجب إنشاء Service Provider Pattern أو Contracts لهذه الخدمات

## 3. الاعتماديات على Helpers

### Helpers من App (اعتماد مباشر):
- `App\Helpers\Common` - دوال مساعدة عامة
- `App\Helpers\UserCommon` - دوال مساعدة للمستخدم
- `App\Helpers\AgencyPackageHelper` - helper خاص بالحزمة
- `App\Helpers\CustomNotification` - الإشعارات
- `App\Helpers\UserHandling` - معالجة المستخدمين

**التوصية**: نقل AgencyPackageHelper للحزمة، والباقي يجب عمل Facades أو Contracts

## 4. الاعتماديات على Facades

- `App\Facades\CustomNotification` - يجب عمل contract
- `App\Facades\UserHandling` - يجب عمل contract

## 5. الاعتماديات على Resources

### Resources (لا يمكن نقلها - تعتمد على structure):
- `App\Http\Resources\Api\V1\*` - جميع resources
- `App\Http\Resources\DollarChargeLogResource`
- `App\Http\Resources\DollarChargeAgencyResource`
- `App\Http\Resources\JoinedAgencyResource`

**التوصية**: يمكن نقلها للحزمة أو عمل Transformers منفصلة

## 6. الاعتماديات على Controllers الأساسية

- `App\Http\Controllers\Controller` - Controller أساسي
- `App\Admin\Controllers\MainController` - Admin controller أساسي

**التوصية**: لا يمكن نقلها - يجب الاعتماد عليها

## 7. الاعتماديات على Traits

- `App\Traits\RequestTrait` - يمكن نقله للحزمة

## 8. الاعتماديات على Actions

- `App\Admin\Actions\*` - جميع الـ Actions
  - DeleteAgencyAction
  - ChangeUsersAgencyAction
  - AcceptAgencyAction
  - RefuseAgencyAction
  - KickOfAgencyAction
  - KickOfFamilyAction
  - ChangeAgencyAction
  - ChargeSwitchAction
  - InviteSwitchAction
  - CanPlaySwitchAction

**التوصية**: يمكن نقل actions الخاصة بالوكالة للحزمة

## 9. الاعتماديات على Notifications

- `App\Notifications\AcceptAgency` - يمكن نقلها
- `App\Notifications\RefuseAgency` - يمكن نقلها

## 10. الاعتماديات على Modules خارجية

### Modules خارجية (اعتماد خطر):
- `Modules\Reals\Http\Services\RealsService` - خدمة الريلز
- `Modules\FixedTarget\Services\FixedTargetService` - خدمة الأهداف الثابتة
- `Modules\SalaryTransaction\Entities\ChargeAgency` - شحن الوكالة
- `Modules\SalaryTransaction\Transformers\*` - محولات الرواتب
- `Modules\SwitchAccount\Entities\UserAccount` - حسابات المستخدمين
- `Modules\Milestones\Helpers\MilestoneHelper` - helper الإنجازات

**مشكلة خطيرة**: اعتماد مباشر على modules قد لا تكون موجودة

## 11. الاعتماديات على Contracts

- `App\Contracts\UserAchievementContract` - عقد الإنجازات

## 12. الاعتماديات على Admin Extensions

- `App\Admin\Customization\Dashboard\CustomDashboard`
- `App\Admin\Extensions\UserExporter`
- `App\Admin\Extensions\AgencyExporter`
- `App\Admin\Extensions\Permission`
- `App\Admin\Selectable\ImageColors`

---

## الحلول المقترحة

### 1. نقل Models الخاصة بالحزمة إلى Entities
```
- AgencyJoinRequest → Entities/
- UsersJoinedAgency → Entities/
- AgencyUserJob → Entities/
- AgencySallary → Entities/
- AgencyMangerPullingOut → Entities/
```

### 2. إنشاء Contracts للخدمات الخارجية
```php
Utd\Agency\Contracts\
├── AgencyServiceInterface.php
├── ChargeServiceInterface.php
├── NotificationServiceInterface.php
├── UserHandlingInterface.php
└── AppFeatureServiceInterface.php
```

### 3. إنشاء Service Locator Pattern
```php
// في ServiceProvider
$this->app->bind(AgencyServiceInterface::class, function ($app) {
    if (class_exists(\App\Tik\Services\AgencyService::class)) {
        return $app->make(\App\Tik\Services\AgencyService::class);
    }
    throw new \Exception('AgencyService not available');
});
```

### 4. إنشاء Wrapper للـ Modules الخارجية
```php
// Utd\Agency\Services\ExternalModuleService.php
class ExternalModuleService {
    public function hasRealsModule(): bool {
        return class_exists(\Modules\Reals\Http\Services\RealsService::class);
    }
    
    public function getRealsService() {
        if ($this->hasRealsModule()) {
            return app(\Modules\Reals\Http\Services\RealsService::class);
        }
        return null;
    }
}
```

### 5. نقل Helpers الخاصة بالحزمة
```
App\Helpers\AgencyPackageHelper → Utd\Agency\Helpers\
```

### 6. نقل Notifications
```
App\Notifications\AcceptAgency → Utd\Agency\Notifications\
App\Notifications\RefuseAgency → Utd\Agency\Notifications\
```

### 7. نقل Actions الخاصة
```
App\Admin\Actions\DeleteAgencyAction → Utd\Agency\Actions\
```

### 8. إنشاء Facade للـ Common Functions
```php
Utd\Agency\Facades\AgencyHelper::class
// wrapper حول App\Helpers\Common بالدوال المستخدمة فقط
```

---

## الأولويات

### عالية الأولوية (يجب):
1. ✅ نقل Models الخاصة بالحزمة إلى Entities
2. ✅ إنشاء Contracts للخدمات الأساسية
3. ✅ عمل wrapper للـ Modules الخارجية
4. ✅ نقل Notifications الخاصة

### متوسطة الأولوية (يفضل):
1. نقل Actions الخاصة
2. نقل Helpers الخاصة
3. إنشاء Transformers بدلاً من Resources

### منخفضة الأولوية (اختياري):
1. عمل facade للـ Common functions
2. نقل Resources للحزمة

---

## ملخص التقييم

- **إجمالي الاعتماديات الخارجية**: ~150+ اعتماد
- **يمكن نقلها**: ~30%
- **تحتاج contracts**: ~40%
- **يجب أن تبقى خارجية**: ~30%

**التوصية النهائية**: 
الحزمة حالياً مترابطة بشدة مع التطبيق الرئيسي. يجب عمل refactoring تدريجي لفصل الاعتماديات المباشرة باستخدام:
1. Dependency Injection
2. Service Contracts/Interfaces
3. Event-Driven Architecture للتواصل مع Modules الخارجية
4. Feature Flags للتحقق من وجود الـ modules الخارجية قبل استخدامها
