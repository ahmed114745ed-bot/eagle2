# Agency Package - Safe Model Access

## استخدام Models الحزمة بشكل آمن

جميع models الحزمة موجودة الآن في:
```
packages/Utd/Agency/src/Entities/
```

## الطريقة الآمنة للوصول

### 1. استخدام Helper مباشرة

```php
use Utd\Agency\Helpers\AgencyModelsHelper;

// التحقق من توفر الحزمة
if (AgencyModelsHelper::isPackageAvailable()) {
    $agencyClass = AgencyModelsHelper::getAgencyClass();
    $agencies = $agencyClass::all();
}

// الحصول على كلاس معين
$agencyJoinRequestClass = AgencyModelsHelper::getAgencyJoinRequestClass();
if ($agencyJoinRequestClass) {
    $requests = $agencyJoinRequestClass::where('status', 0)->get();
}
```

### 2. استخدام AgencyPackageHelper

```php
use App\Helpers\AgencyPackageHelper;

// التحقق من التثبيت
if (AgencyPackageHelper::isAgencyInstalled()) {
    $agencyClass = AgencyPackageHelper::getAgencyClass();
    // استخدام الكلاس
}

// الحصول على Models مختلفة
$joinRequestClass = AgencyPackageHelper::getAgencyJoinRequestClass();
$salaryClass = AgencyPackageHelper::getAgencySalaryClass();
$userJobClass = AgencyPackageHelper::getAgencyUserJobClass();
```

### 3. استخدام App\Models (Backward Compatibility)

```php
use App\Models\Agency;
use App\Models\AgencyJoinRequest;

// سيعمل فقط إذا كانت الحزمة متوفرة
// الـ aliases موجودة في app/Models لكن تشير للحزمة
$agencies = Agency::all();
$requests = AgencyJoinRequest::pending()->get();
```

## Models المتاحة

| Model | Helper Method | App\Models Alias |
|-------|--------------|------------------|
| Agency | `getAgencyClass()` | `App\Models\Agency` |
| AgencyJoinRequest | `getAgencyJoinRequestClass()` | `App\Models\AgencyJoinRequest` |
| AgencySalary | `getAgencySalaryClass()` | `App\Models\AgencySallary` |
| AgencyUserJob | `getAgencyUserJobClass()` | `App\Models\AgencyUserJob` |
| AgencyMangerDeleted | `getAgencyMangerDeletedClass()` | `App\Models\AgencyMangerDeleted` |
| AgencymAngerLink | `getAgencymAngerLinkClass()` | `App\Models\AgencymAngerLink` |
| AgencyMangerPullingOut | `getAgencyMangerPullingOutClass()` | `App\Models\AgencyMangerPullingOut` |
| AgencyMangLink | `getAgencyMangLinkClass()` | `App\Models\AgencyMangLink` |
| AgencyPack | `getAgencyPackClass()` | `App\Models\AgencyPack` |

## أمثلة عملية

### في Controllers

```php
use Utd\Agency\Helpers\AgencyModelsHelper;

class SomeController extends Controller
{
    public function index()
    {
        // تحقق من توفر الحزمة أولاً
        if (!AgencyModelsHelper::isPackageAvailable()) {
            return response()->json(['error' => 'Agency package not available'], 503);
        }
        
        $agencyClass = AgencyModelsHelper::getAgencyClass();
        $agencies = $agencyClass::with('owner')->paginate(20);
        
        return response()->json($agencies);
    }
}
```

### في Repositories

```php
use Utd\Agency\Helpers\AgencyModelsHelper;

class SomeRepository
{
    protected $agencyClass;
    
    public function __construct()
    {
        $this->agencyClass = AgencyModelsHelper::getAgencyClass();
    }
    
    public function findAgency($id)
    {
        if (!$this->agencyClass) {
            throw new \Exception('Agency model not available');
        }
        
        return $this->agencyClass::find($id);
    }
}
```

### في Resources

```php
use Utd\Agency\Helpers\AgencyModelsHelper;

class UserResource extends JsonResource
{
    public function toArray($request)
    {
        $data = [
            'id' => $this->id,
            'name' => $this->name,
        ];
        
        // إضافة بيانات الوكالة فقط إذا كانت متوفرة
        if (AgencyModelsHelper::isPackageAvailable()) {
            $agencyJoinRequestClass = AgencyModelsHelper::getAgencyJoinRequestClass();
            if ($agencyJoinRequestClass) {
                $data['agency_requests'] = $agencyJoinRequestClass::where('user_id', $this->id)
                    ->where('status', 0)
                    ->count();
            }
        }
        
        return $data;
    }
}
```

### في Exports

```php
use Utd\Agency\Helpers\AgencyModelsHelper;

class AgenciesExport implements FromQuery
{
    public function query()
    {
        $agencyClass = AgencyModelsHelper::getAgencyClass();
        
        if (!$agencyClass) {
            throw new \Exception('Agency package is not available');
        }
        
        return $agencyClass::query()->with('owner');
    }
}
```

## Facades (اختياري)

```php
use Utd\Agency\Facades\AgencyPackage;

// استخدام الـ Facade
if (AgencyPackage::isPackageAvailable()) {
    $agencyClass = AgencyPackage::getAgencyClass();
    $allModels = AgencyPackage::getAllModels();
}
```

## ملاحظات مهمة

1. **جميع Models الآن داخل الحزمة فقط** - لا توجد models في `app/Models` عدا الـ aliases
2. **التحقق من التوفر ضروري** - دائماً تحقق من توفر الحزمة قبل الاستخدام
3. **Backward Compatibility** - الـ aliases في `app/Models` تضمن عمل الكود القديم
4. **استخدم Helper للأمان** - استخدم `AgencyModelsHelper` أو `AgencyPackageHelper` للوصول الآمن

## الفرق بين الـ Helpers

### AgencyModelsHelper (داخل الحزمة)
- موجود في: `packages/Utd/Agency/src/Helpers/AgencyModelsHelper.php`
- يوفر الوصول المباشر لـ models الحزمة
- يعمل فقط مع models الحزمة

### AgencyPackageHelper (في التطبيق)
- موجود في: `app/Helpers/AgencyPackageHelper.php`
- يوفر وظائف إضافية (التحقق من الجداول، Modules، إلخ)
- يستخدم `AgencyModelsHelper` داخلياً
- الخيار الأفضل للاستخدام في التطبيق
