# 🚀 Quick Start - اختبار سريع

## خطوات الاختبار في 5 دقائق

### 1️⃣ تحقق من الحالة الحالية
```bash
php artisan pusher:test-auto-update
```

يجب أن ترى:
```
✅ Octane is running
✅ All checks passed!
```

---

### 2️⃣ عرض الإعدادات الحالية
```bash
curl https://your-domain.com/debug/pusher-config | jq
```

أو افتح في المتصفح:
```
https://your-domain.com/debug/pusher-config
```

احفظ قيمة `pusher_app_key` الحالية

---

### 3️⃣ عدّل إعدادات Pusher

**خيار 1: عبر Admin Panel**
1. افتح `/admin/settings`
2. غيّر `pusher_app_key`
3. احفظ

**خيار 2: عبر قاعدة البيانات**
```sql
UPDATE configs 
SET value = 'test_key_123456' 
WHERE name = 'pusher_app_key';
```

**خيار 3: عبر Tinker**
```bash
php artisan tinker
```
```php
$config = App\Models\Config::where('name', 'pusher_app_key')->first();
$config->value = 'test_key_123456';
$config->save();
```

---

### 4️⃣ انتظر قليلاً
```bash
# انتظر 2-5 ثوانٍ
sleep 5
```

---

### 5️⃣ تحقق من التحديث
```bash
# الطريقة 1: Command
php artisan pusher:test-auto-update

# الطريقة 2: API
curl https://your-domain.com/debug/pusher-config | jq

# الطريقة 3: Logs
tail -n 50 storage/logs/laravel.log | grep pusher
```

---

## ✅ النتيجة المتوقعة

### قبل التعديل:
```json
{
  "from_database": {
    "pusher_app_key": "old_key_value"
  },
  "from_config_runtime": {
    "key": "old_key_value"
  }
}
```

### بعد التعديل (خلال 1-10 ثوانٍ):
```json
{
  "from_database": {
    "pusher_app_key": "test_key_123456"
  },
  "from_config_runtime": {
    "key": "test_key_123456"
  }
}
```

يجب أن تتطابق القيم! ✅

---

## 📊 مراقبة Logs الحية

```bash
# في terminal منفصل، شغّل:
tail -f storage/logs/laravel.log | grep -i pusher
```

يجب أن ترى رسائل مثل:
```
[2026-01-14 10:00:15] pusher_config_change
[2026-01-14 10:00:16] Broadcaster updated via PusherConfigUpdated event
[2026-01-14 10:00:25] Pusher config refreshed in Octane worker
```

---

## 🔧 استكشاف المشاكل

### المشكلة: الإعدادات لا تتطابق
```bash
# الحل السريع:
php artisan cache:clear
php artisan octane:reload
php artisan pusher:test-auto-update
```

### المشكلة: Octane لا يعمل
```bash
# تحقق من الحالة
php artisan octane:status

# أعد التشغيل
php artisan octane:stop
php artisan octane:start --workers=4
```

### المشكلة: لا توجد logs
```bash
# تأكد من صلاحيات الكتابة
chmod -R 775 storage/logs
chown -R www-data:www-data storage/logs

# تحقق من Log level
grep LOG_LEVEL .env
```

---

## 🎯 اختبار متقدم

### اختبار عدة Workers
```bash
# في 3 terminals منفصلة، شغّل:
while true; do 
  curl -s https://your-domain.com/debug/pusher-config | jq -r '.from_config_runtime.key'
  sleep 1
done
```

ثم عدّل الإعدادات وراقب التحديث في جميع الطلبات

---

## 📈 مقاييس الأداء

| الإجراء | الوقت المتوقع |
|---------|---------------|
| تحديث Worker الحالي | فوري (<1s) |
| Event إلى Workers الأخرى | 1-2s |
| Tick-based update | 10s (max) |
| **المجموع** | **1-10 ثوانٍ** |

---

## ✨ نصائح للنجاح

1. ✅ **استخدم Redis للـ Cache** - أسرع وأفضل
2. ✅ **راقب الـ Logs** - لتتبع التحديثات
3. ✅ **اختبر في Staging أولاً** - قبل Production
4. ✅ **عدّل من Admin Panel** - أسهل وأأمن
5. ✅ **لا تعدل .env** - استخدم DB فقط

---

## 📚 مراجع إضافية

- [PUSHER_UPDATE_SUMMARY.md](PUSHER_UPDATE_SUMMARY.md) - ملخص كامل
- [OCTANE_PUSHER_AUTO_UPDATE.md](OCTANE_PUSHER_AUTO_UPDATE.md) - توثيق تفصيلي
- [PUSHER_AUTO_UPDATE_AR.md](PUSHER_AUTO_UPDATE_AR.md) - دليل عربي

---

**تم! الآن يمكنك تعديل Pusher ومشاهدة التحديث التلقائي! 🎉**
