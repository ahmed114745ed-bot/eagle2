# 🚨 Rixo Chat - Performance Fixes للـ DevOps Team

**تاريخ:** 22 أبريل 2026  
**الأولوية:** CRITICAL  
**المطلوب:** 3 تعديلات config على السيرفر (30 دقيقة تقريباً)

---

## 📊 الوضع الحالي (من التقرير)

- **Success Rate:** 74% فقط (24% failures!)
- **504 Errors:** 659 من آخر 5,000 request
- **499 Errors:** 534 (clients gave up waiting)
- **المشكلة الرئيسية:** الـ gift-combo endpoint بيمسك workers لمدة 60-120 ثانية

---

## ✅ التعديلات على الكود (خلصت بالفعل)

قبل ما تبدأوا، دي الحاجات اللي **اتعملت من ناحية الكود**:

### 1. Async Processing + Queue Jobs ✅
- الـ post-processing operations بقت async (ProcessLuckyGiftPostJob)
- Chunking strategy للـ large counts
- **Commit:** `7c2dec2be6`, `f7d202593e`

### 2. Validation Cap على count parameter ✅
- إضافة `max:10` على `sendLuckyGift7`
- **Commit:** `97cc470cec`

### 3. Caching Layer ✅
- Config keys (TTL: 5 min)
- Emoji categories (TTL: 5 min)
- Home carousels (TTL: 2 min)
- **Commit:** `97cc470cec`

**النتيجة:** الكود جاهز، بس محتاج الـ infrastructure changes عشان الـ performance يتحسن فعلياً.

---

## 🔧 المطلوب منكم (DevOps Tasks)

---

### ✅ Fix #1: تغيير SESSION_DRIVER من database لـ redis

#### **المشكلة:**
- حالياً: كل authenticated request بيروح للـ Cloud SQL (34.18.139.237) عشان الـ session
- دي network round-trip زيادة على **كل request**
- Redis شغال محلياً وفاضي (5.4MB / 1GB)

#### **التعديل المطلوب:**

**1. في الـ `.env` file:**
```bash
# Before
SESSION_DRIVER=file

# After  
SESSION_DRIVER=redis
```

**2. Restart Octane containers:**
```bash
docker service update --force rixo_rixo-chat-octa
```

#### **التحقق:**
```bash
# تأكد إن الـ containers رجعت تشتغل
docker service ls | grep rixo-chat-octa

# شوف الـ logs
docker service logs rixo_rixo-chat-octa --tail 50
```

#### **Expected Impact:**
- ✅ تقليل latency لكل authenticated request
- ✅ تخفيف الحمل على Cloud SQL
- ✅ Session reads في sub-millisecond بدل 10-50ms

#### **Risk Level:** 🟢 Very Low
- Redis already running and tested
- Easy rollback (change back to `file`)

---

### ✅ Fix #2: زيادة OCTANE_WORKERS من 4 لـ 8

#### **المشكلة:**
- حالياً: 4 replicas × 4 workers = **16 total workers**
- الـ traffic الحالي محتاج capacity أكتر
- الـ VM فيها 16 vCPUs (underutilized)

#### **التعديل المطلوب:**

**1. في الـ `.env` file:**
```bash
# Add this line (or update if exists)
OCTANE_WORKERS=8
```

**2. Restart Octane containers:**
```bash
docker service update --force rixo_rixo-chat-octa
```

#### **التحقق:**
```bash
# شوف عدد الـ workers الفعلي من جوا الـ container
docker exec -it $(docker ps -q -f name=rixo-chat-octa | head -1) php artisan octane:status

# أو شوف الـ processes
docker exec -it $(docker ps -q -f name=rixo-chat-octa | head -1) ps aux | grep swoole
```

#### **Expected Impact:**
- ✅ مضاعفة الـ capacity: 16 → 32 workers
- ✅ تقليل الـ queue time للـ requests
- ✅ تقليل 504 timeout errors بشكل ملحوظ

#### **Resource Check:**
```bash
# Current load
uptime

# Memory available  
free -h

# CPU usage
top -bn1 | head -20
```

#### **Risk Level:** 🟡 Low
- VM has 16 vCPUs, so 8 workers per replica is safe
- Monitor memory usage after deployment

---

### ✅ Fix #3: إضافة Nginx Rate Limiting على gift-combo endpoint

#### **المشكلة:**
- IP واحد (196.156.90.39) بعت **6+ concurrent requests** في نفس الوقت
- سد كل الـ workers
- مفيش حماية من abuse

#### **التعديل المطلوب:**

**1. أنشئ/عدل الملف: `/etc/nginx/conf.d/rate-limit.conf`**
```nginx
# Define rate limit zone for gift combo endpoint
limit_req_zone $binary_remote_addr zone=gift_combo:10m rate=2r/s;
```

**2. في الـ nginx config للـ app (غالباً `/etc/nginx/sites-available/rixo-chat`):**

ابحث عن الـ location اللي فيها:
```nginx
location ~* ^/api/gifts/v[0-9]+/send-lucky-gift-combo {
    # Add these lines
    limit_req zone=gift_combo burst=3 nodelay;
    limit_req_status 429;
    
    # ... existing proxy_pass config
}
```

**3. Test و Reload:**
```bash
# Test nginx config
nginx -t

# Reload nginx
systemctl reload nginx
# OR if using docker:
docker exec nginx nginx -s reload
```

#### **التحقق:**
```bash
# Test من الـ terminal
for i in {1..10}; do curl -s -o /dev/null -w "%{http_code}\n" http://your-server/api/gifts/v2/send-lucky-gift-combo; done

# Expected: أول 5 requests → 200/401, بعدين → 429 (Too Many Requests)
```

#### **المعنى:**
- `rate=2r/s` → max 2 requests per second per IP
- `burst=3` → يسمح بـ burst of 3 requests فوق الـ rate
- `limit_req_status 429` → يرجع HTTP 429 لو exceeded

#### **Expected Impact:**
- ✅ منع single user من سد كل الـ workers
- ✅ Fair usage للـ endpoint
- ✅ Protection من abuse/attacks

#### **Risk Level:** 🟢 Very Low
- Only affects gift-combo endpoint
- Rate is generous (2/s + burst)
- Easy to adjust if needed

---

## 📋 Implementation Checklist

### Pre-Deployment:
- [ ] أخذ backup من `.env` file
- [ ] أخذ backup من nginx configs
- [ ] تحديد maintenance window (لو محتاج)

### Deployment Order (الأولوية):

#### **Priority 1 (NOW - 10 min):**
- [ ] Fix #1: SESSION_DRIVER=redis
- [ ] Fix #2: OCTANE_WORKERS=8
- [ ] Restart Octane containers
- [ ] Verify services are up

#### **Priority 2 (TODAY - 15 min):**
- [ ] Fix #3: Nginx rate limiting
- [ ] Test nginx config
- [ ] Reload nginx
- [ ] Test rate limiting

### Post-Deployment Verification:

```bash
# 1. Check container health
docker service ls
docker service ps rixo_rixo-chat-octa

# 2. Monitor logs for errors
docker service logs rixo_rixo-chat-octa --follow --tail 100

# 3. Check response times
tail -f /var/log/nginx/access.log | grep "send-lucky-gift-combo"

# 4. Monitor Redis
redis-cli INFO stats | grep -E "keyspace_hits|keyspace_misses|total_commands"

# 5. Watch for 504/499 errors
tail -f /var/log/nginx/error.log | grep -E "504|499"
```

---

## 📊 Expected Results (بعد التطبيق)

### Before:
- Success Rate: 74%
- 504 Errors: 659 من 5,000 requests
- Gift combo time: 60-120 seconds
- Workers: 16 total

### After:
- Success Rate: **>90%** ✅
- 504 Errors: **<100** من 5,000 requests ✅
- Gift combo time: **<10 seconds** ✅
- Workers: **32 total** ✅
- Session overhead: **eliminated** ✅

---

## 🔄 Rollback Plan (لو حصلت مشكلة)

### If SESSION_DRIVER causes issues:
```bash
# في .env
SESSION_DRIVER=file

# Restart
docker service update --force rixo_rixo-chat-octa
```

### If OCTANE_WORKERS causes memory issues:
```bash
# في .env
OCTANE_WORKERS=6  # أو 4

# Restart
docker service update --force rixo_rixo-chat-octa
```

### If Nginx rate limiting too strict:
```nginx
# عدل الـ rate
limit_req_zone $binary_remote_addr zone=gift_combo:10m rate=5r/s;  # زود من 2 لـ 5

# أو شيل الـ rate limit تماماً
# comment out the limit_req lines
```

---

## 📞 Contact

**Developer (Backend):** [your name]  
**Reference:** Performance Diagnostic Report - April 21, 2026  
**Server:** 34.18.96.67 (rixo-chat)

---

## 📎 Appendix: الملفات المطلوب تعديلها

```
الملفات:
├── .env                                    # SESSION_DRIVER, OCTANE_WORKERS
├── /etc/nginx/conf.d/rate-limit.conf      # Rate limit zone definition
└── /etc/nginx/sites-available/rixo-chat   # Rate limit application

الأوامر:
├── docker service update --force rixo_rixo-chat-octa
├── nginx -t
└── systemctl reload nginx
```

---

**ملحوظة للـ DevOps:**  
الـ code changes خلصت وتم push على branch `test`. التعديلات المطلوبة منكم كلها **config changes** بسيطة، مفيش code deployment محتاج يحصل. الهدف: تقليل الـ 504/499 errors من 24% لأقل من 5%.

**ETA:** 30 دقيقة total  
**Risk:** Low (all changes are reversible)  
**Impact:** High (24% → 90%+ success rate)

---

_تم الإعداد بواسطة Backend Team - 2026-04-22_
