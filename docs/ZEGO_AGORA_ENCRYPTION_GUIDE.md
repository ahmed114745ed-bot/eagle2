# دليل فك تشفير Zego/Agora Credentials - Frontend Integration Guide

## نظرة عامة | Overview

الـ endpoint الجديد `/api/update-zego-agora/v2` يرجع البيانات الحساسة مشفرة باستخدام **AES-256-CBC** لحماية المفاتيح.

The new endpoint `/api/update-zego-agora/v2` returns sensitive data encrypted using **AES-256-CBC** to protect API keys.

---

## 🔐 معلومات التشفير | Encryption Details

### خوارزمية التشفير | Algorithm
- **Algorithm**: AES-256-CBC
- **Key**: من ENV variable `ZEGOENCRYPTtkEY` (يجب أن يكون 32 حرف)
- **IV (Initialization Vector)**: أول 16 حرف من المفتاح
- **Output**: Base64 encoded string

### المفتاح الافتراضي | Default Key
```
7b5d61e6f4a8c2d3e9b7a6f8e1c3d2f4
```
> ⚠️ **تحذير**: يجب تغيير هذا المفتاح في الـ production!

---

## 📡 استخدام الـ API | API Usage

### 1. استدعاء الـ Endpoint | Call the Endpoint

```javascript
// يجب إرسال token مع الطلب | Must send auth token
const response = await fetch('/api/update-zego-agora/v2', {
    method: 'GET',
    headers: {
        'Authorization': `Bearer ${userToken}`,
        'Accept': 'application/json'
    }
});

const data = await response.json();
console.log(data);
// Output example:
// {
//   "status": 1,
//   "message": "",
//   "data": "U2FsdGVkX1+ABC123..." // encrypted string
// }
```

---

## 🔓 فك التشفير في Frontend | Decryption in Frontend

### 1️⃣ JavaScript (باستخدام CryptoJS)

#### تثبيت المكتبة | Install Library
```bash
npm install crypto-js
# أو | or
yarn add crypto-js
```

#### الكود | Code
```javascript
import CryptoJS from 'crypto-js';

// المفتاح السري (نفس المفتاح من Laravel)
// The encryption key (same as Laravel)
const ENCRYPTION_KEY = '7b5d61e6f4a8c2d3e9b7a6f8e1c3d2f4';

function decryptZegoCredentials(encryptedData) {
    try {
        // الـ IV هو أول 16 حرف من المفتاح
        // IV is the first 16 chars of the key
        const iv = CryptoJS.enc.Utf8.parse(ENCRYPTION_KEY.substring(0, 16));
        const key = CryptoJS.enc.Utf8.parse(ENCRYPTION_KEY);
        
        // فك التشفير | Decrypt
        const decrypted = CryptoJS.AES.decrypt(encryptedData, key, {
            iv: iv,
            mode: CryptoJS.mode.CBC,
            padding: CryptoJS.pad.Pkcs7
        });
        
        // تحويل النتيجة إلى JSON
        // Convert result to JSON
        const decryptedText = decrypted.toString(CryptoJS.enc.Utf8);
        return JSON.parse(decryptedText);
        
    } catch (error) {
        console.error('فشل فك التشفير | Decryption failed:', error);
        return null;
    }
}

// مثال على الاستخدام | Usage example
async function getZegoCredentials() {
    const response = await fetch('/api/update-zego-agora/v2', {
        headers: {
            'Authorization': `Bearer ${userToken}`,
            'Accept': 'application/json'
        }
    });
    
    const result = await response.json();
    
    if (result.status === 1) {
        const credentials = decryptZegoCredentials(result.data);
        console.log(credentials);
        /*
        {
            "agora_app_id": "your_agora_app_id",
            "zego": {
                "server_secret": "your_zego_secret",
                "app_id": "your_zego_app_id",
                "app_sign": "your_app_sign",
                "filter": true,
                "live_type": "RTC"
            },
            "library": "zego",
            "is_auto_preview": true
        }
        */
        return credentials;
    }
    
    return null;
}
```

---

### 2️⃣ React Native (باستخدام react-native-crypto-js)

#### تثبيت المكتبات | Install Dependencies
```bash
npm install react-native-crypto-js
# أو | or
yarn add react-native-crypto-js
```

#### الكود | Code
```javascript
import CryptoJS from 'react-native-crypto-js';

const ENCRYPTION_KEY = '7b5d61e6f4a8c2d3e9b7a6f8e1c3d2f4';

const decryptCredentials = (encryptedData) => {
    try {
        const iv = CryptoJS.enc.Utf8.parse(ENCRYPTION_KEY.substring(0, 16));
        const key = CryptoJS.enc.Utf8.parse(ENCRYPTION_KEY);
        
        const decrypted = CryptoJS.AES.decrypt(encryptedData, key, {
            iv: iv,
            mode: CryptoJS.mode.CBC,
            padding: CryptoJS.pad.Pkcs7
        });
        
        const decryptedText = decrypted.toString(CryptoJS.enc.Utf8);
        return JSON.parse(decryptedText);
    } catch (error) {
        console.error('Decryption error:', error);
        return null;
    }
};

// استخدام في Component
// Usage in Component
const MyComponent = () => {
    const [credentials, setCredentials] = useState(null);
    
    useEffect(() => {
        const fetchCredentials = async () => {
            const response = await fetch('/api/update-zego-agora/v2', {
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json'
                }
            });
            
            const result = await response.json();
            if (result.status === 1) {
                const decrypted = decryptCredentials(result.data);
                setCredentials(decrypted);
            }
        };
        
        fetchCredentials();
    }, []);
    
    return (
        // your UI
    );
};
```

---

### 3️⃣ Flutter/Dart

#### إضافة المكتبة | Add Dependency
في `pubspec.yaml`:
```yaml
dependencies:
  encrypt: ^5.0.1
```

#### الكود | Code
```dart
import 'package:encrypt/encrypt.dart';
import 'dart:convert';

class ZegoDecryption {
  static const String ENCRYPTION_KEY = '7b5d61e6f4a8c2d3e9b7a6f8e1c3d2f4';
  
  static Map<String, dynamic>? decryptCredentials(String encryptedData) {
    try {
      final key = Key.fromUtf8(ENCRYPTION_KEY);
      final iv = IV.fromUtf8(ENCRYPTION_KEY.substring(0, 16));
      
      final encrypter = Encrypter(AES(key, mode: AESMode.cbc));
      final decrypted = encrypter.decrypt64(encryptedData, iv: iv);
      
      return jsonDecode(decrypted);
    } catch (e) {
      print('Decryption error: $e');
      return null;
    }
  }
}

// استخدام | Usage
Future<Map<String, dynamic>?> getZegoCredentials() async {
  final response = await http.get(
    Uri.parse('/api/update-zego-agora/v2'),
    headers: {
      'Authorization': 'Bearer $token',
      'Accept': 'application/json',
    },
  );
  
  if (response.statusCode == 200) {
    final result = jsonDecode(response.body);
    if (result['status'] == 1) {
      return ZegoDecryption.decryptCredentials(result['data']);
    }
  }
  
  return null;
}
```

---

## 🔒 الحماية | Security

### الـ Endpoint محمي بـ | Endpoint Protected By:
1. ✅ **Authentication** (`auth:sanctum`) - يجب تسجيل الدخول
2. ✅ **Token Validation** (`checkLatestToken`) - التحقق من صلاحية التوكن
3. ✅ **Ban Check** (`generalBan`, `userBan`) - التحقق من الحظر
4. ✅ **Activity Tracking** (`update.last.seen`) - تتبع النشاط
5. ✅ **Localization** - دعم اللغات

---

## 📝 ملاحظات مهمة | Important Notes

### ⚠️ تحذيرات الأمان | Security Warnings
1. **لا تحفظ المفتاح في الكود مباشرة** - استخدم environment variables
   - Don't hardcode the key - use environment variables
   
2. **غيّر المفتاح الافتراضي في Production**
   - Change the default key in production
   
3. **استخدم HTTPS فقط**
   - Use HTTPS only
   
4. **لا تشارك المفتاح في Git**
   - Don't commit the key to Git

### 💡 نصائح | Tips
- احفظ الـ credentials في secure storage (keychain, encrypted storage)
- لا تعرض البيانات الحساسة في الـ console في Production
- استخدم token refresh mechanism

---

## 🧪 اختبار | Testing

### مثال اختبار سريع | Quick Test Example
```javascript
// البيانات المشفرة من الـ API
const encryptedFromAPI = "U2FsdGVkX1+ABC123..."; // من response

// فك التشفير
const decrypted = decryptZegoCredentials(encryptedFromAPI);

// التحقق
console.assert(decrypted !== null, 'فشل فك التشفير');
console.assert(decrypted.library === 'zego', 'بيانات خاطئة');
console.log('✅ التشفير يعمل بنجاح!');
```

---

## 🔄 مقارنة بين V1 و V2 | Comparison V1 vs V2

| Feature | V1 (`/update-zego-agora`) | V2 (`/update-zego-agora/v2`) |
|---------|---------------------------|------------------------------|
| حماية البيانات | ❌ بدون تشفير | ✅ مشفر AES-256 |
| المصادقة | ❌ بدون | ✅ مطلوبة |
| الأمان | منخفض | عالي |
| الاستخدام | مباشر | يحتاج فك تشفير |

---

## 📞 الدعم | Support

إذا واجهت مشاكل في فك التشفير:
1. تحقق من المفتاح (يجب أن يكون 32 حرف بالضبط)
2. تحقق من الـ Authorization token
3. تأكد من استخدام نفس المفتاح في Backend و Frontend

If you face decryption issues:
1. Verify the key (must be exactly 32 chars)
2. Check the Authorization token
3. Ensure the same key is used in both Backend and Frontend

---

**آخر تحديث | Last Updated**: 2026-04-30
