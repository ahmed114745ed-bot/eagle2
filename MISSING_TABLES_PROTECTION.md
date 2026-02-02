# حماية التطبيق من أخطاء الجداول المفقودة (Missing Tables Protection)

## المشكلة
عند استخدام packages/modules اختيارية، قد لا تكون جداول معينة موجودة في قاعدة البيانات، مما يؤدي إلى أخطاء:
```
SQLSTATE[42S02]: Base table or view not found: 1146 Table 'database.table_name' doesn't exist
```

## الحلول المطبقة

### 1. SafeRelationLoading Trait
**الملف**: `app/Traits/SafeRelationLoading.php`

يوفر methods آمنة لتحميل العلاقات:
- `tableExists(string $tableName)`: للتحقق من وجود جدول
- `safeLoadMissing($relations)`: لتحميل العلاقات المفقودة بشكل آمن
- `safeLoad($relations)`: لتحميل العلاقات بشكل آمن
- `safeRelation(string $relationName, $default = null)`: للحصول على قيمة العلاقة بشكل آمن
- `nullRelation()`: لإنشاء علاقة فارغة

**الاستخدام**:
```php
use App\Traits\SafeRelationLoading;

class YourModel extends Model
{
    use SafeRelationLoading;
    
    public function someRelation()
    {
        if (!$this->tableExists('related_table')) {
            return $this->nullRelation();
        }
        return $this->hasMany(RelatedModel::class);
    }
    
    public function someMethod()
    {
        $this->safeLoadMissing('someRelation');
        // أو
        $value = $this->safeRelation('someRelation', 'default_value');
    }
}
```

### 2. MissingTableHandler
**الملف**: `app/Exceptions/MissingTableHandler.php`

يتعامل مع أخطاء الجداول المفقودة:
- `handle(\Exception $exception)`: للتعامل مع الخطأ وتسجيله
- `isMissingTableException(\Exception $exception)`: للتحقق من نوع الخطأ

### 3. CatchMissingTableExceptions Middleware
**الملف**: `app/Http/Middleware/CatchMissingTableExceptions.php`

يمسك أخطاء الجداول المفقودة ويمنع تعطل التطبيق.
- تم إضافته في `app/Http/Kernel.php` كـ global middleware

### 4. Exception Handler Updates
**الملف**: `app/Exceptions/Handler.php`

تم تحديث `render()` method لاستخدام `MissingTableHandler`.

### 5. User Model Updates
**الملف**: `app/Models/User.php`

تم تحديث جميع العلاقات المتعلقة بـ Agency:
- `agency()`
- `agencies()`
- `managedAgencies()`
- `shippingAgency()`
- `hostAgency()`
- `hasHostAgency()`
- `hasShippingAgencyV2()`
- `getUserTypesAttribute()`

كل هذه العلاقات الآن تتحقق من وجود الجدول قبل محاولة الاستعلام.

## كيفية تطبيق الحماية على Models أخرى

### الطريقة 1: استخدام SafeRelationLoading Trait
```php
use App\Traits\SafeRelationLoading;

class YourModel extends Model
{
    use SafeRelationLoading;
    
    public function someRelation()
    {
        // التحقق من وجود الجدول
        if (!$this->tableExists('table_name')) {
            return $this->nullRelation();
        }
        
        // التحقق من وجود الـ Class
        if (!class_exists(RelatedModel::class)) {
            return $this->nullRelation();
        }
        
        return $this->hasMany(RelatedModel::class);
    }
}
```

### الطريقة 2: استخدام Try-Catch في Methods
```php
public function getData()
{
    try {
        return $this->relationWithMissingTable;
    } catch (\Illuminate\Database\QueryException $e) {
        if (str_contains($e->getMessage(), 'Base table or view not found')) {
            \Log::debug('Table not found: ' . $e->getMessage());
            return null;
        }
        throw $e;
    }
}
```

### الطريقة 3: استخدام Safe Methods
```php
public function loadData()
{
    // بدلاً من
    // $this->loadMissing('relation');
    
    // استخدم
    $this->safeLoadMissing('relation');
    
    // أو
    $data = $this->safeRelation('relation', []);
}
```

## ملاحظات مهمة

1. **Cache**: الـ `tableExists()` method تستخدم cache لتحسين الأداء
2. **Logging**: جميع الأخطاء يتم تسجيلها في الـ logs للمراجعة
3. **API Responses**: عند حدوث خطأ، يتم إرجاع 503 Service Unavailable بدلاً من 500 Server Error
4. **Global Protection**: الـ middleware يوفر حماية شاملة لكل الـ requests

## التطبيق على Repositories

في حالة Repositories (مثل RoomRepository)، يمكن إضافة try-catch:

```php
public function all()
{
    try {
        return Room::with(['user', 'owner'])
            ->paginate(10);
    } catch (\Illuminate\Database\QueryException $e) {
        if (str_contains($e->getMessage(), 'Base table or view not found')) {
            \Log::warning('Missing table in RoomRepository: ' . $e->getMessage());
            return collect([]); // أو paginator فارغ
        }
        throw $e;
    }
}
```

## اختبار الحماية

لاختبار الحماية:
1. تأكد من أن الـ middleware مفعل في Kernel.php
2. حاول الوصول إلى endpoint يستخدم جدول مفقود
3. يجب أن تحصل على response 503 بدلاً من خطأ 500
4. تحقق من الـ logs للتأكد من تسجيل الخطأ

## المزايا

✅ منع تعطل التطبيق بسبب جداول مفقودة
✅ تسجيل تلقائي للأخطاء
✅ استجابة واضحة للمستخدم
✅ سهولة التطبيق على models جديدة
✅ تحسين الأداء بواسطة cache
