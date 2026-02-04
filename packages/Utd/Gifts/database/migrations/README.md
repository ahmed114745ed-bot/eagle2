# Gifts Package Migrations

تم نقل جميع الـ migrations الخاصة بنظام الهدايا إلى هذا المجلد.

## عدد الـ Migrations: 42

### الجداول الرئيسية (Core Tables):

1. **gifts** - جدول الهدايا الأساسي
2. **gift_categories** - فئات الهدايا
3. **gift_logs** - سجل إرسال الهدايا
4. **user_gifts** - الهدايا المملوكة للمستخدمين
5. **lucky_gifts** - إعدادات الهدايا المحظوظة
6. **gift_rankings** - ترتيب الهدايا

### جداول إضافية:

- **user_lucky_gifts** - هدايا المستخدمين المحظوظة
- **user_lucky_gift_counts** - عدادات الهدايا المحظوظة
- **user_lucky_gift_reports** - تقارير الهدايا المحظوظة
- **user_box_gifts** - هدايا الصناديق
- **room_gift_targets** - أهداف هدايا الغرف

## الاستخدام

### تشغيل الـ Migrations:

```bash
php artisan migrate
```

الـ ServiceProvider سيقوم بتحميل هذه الـ migrations تلقائياً.

### نشر الـ Migrations (اختياري):

```bash
php artisan vendor:publish --tag=gifts-migrations
```

## ترتيب التنفيذ

الـ Migrations مرتبة حسب التاريخ (timestamp):
1. إنشاء الجداول الأساسية (2022-2023)
2. إضافة الأعمدة والتعديلات (2024-2025)
3. الفهارس والتحسينات (2025)

## ملاحظات

- ⚠️ لا تحذف أي migration بعد تنفيذه على production
- ✅ جميع الـ migrations لها rollback methods
- 🔄 يمكن إعادة تشغيل الـ migrations في بيئة التطوير

---

تم النسخ في: 2026-02-04
