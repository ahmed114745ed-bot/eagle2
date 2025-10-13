import { initializeApp } from "https://www.gstatic.com/firebasejs/10.7.2/firebase-app.js";
import { getMessaging, getToken, onMessage } from "https://www.gstatic.com/firebasejs/10.7.2/firebase-messaging.js";

const firebaseConfig = {
    apiKey: "6d758cc9885ad14a7b90d0192daa183cd09f773f",
    authDomain: "eagle-24712.firebaseapp.com",
    projectId: "eagle-24712",
    storageBucket: "eagle-24712.appspot.com",
    messagingSenderId: "817000206466",
    appId: "1:817000206466:web:eagle24712appcode",
    measurementId: "G-108333221643033030817"
};

const firebaseApp = initializeApp(firebaseConfig);
const messaging = getMessaging(firebaseApp);

async function requestPermission() {
    try {
        const permission = await Notification.requestPermission();
        if (permission === "granted") {
            console.log("✅ Notification permission granted.");

            const token = await getToken(messaging, {
                vapidKey: "BAbk-_zwaOviMhva90NorW5kOwtFuNyT8S7soK8BFkJoo_1LdG646fWW8UYFICvDE6yot9f7vYxb029CM6G-0W8"
            });

            console.log("📱 FCM Token:", token);

            // إرسال الـ token إلى السيرفر لحفظه
            await fetch("/admin/save-fcm-token", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ token })
            });
        } else {
            console.warn("⚠️ User denied notification permission.");
            showNotificationAlert();
        }
    } catch (error) {
        console.error("❌ Error getting permission:", error);
    }
}

// عرض تنبيه لتفعيل الإشعارات إن لم تكن مفعلة
function showNotificationAlert() {
    if (document.getElementById("notification-alert")) return;
    const alert = document.createElement("div");
    alert.id = "notification-alert";
    alert.style.cssText = `
        position:fixed;top:10px;left:50%;transform:translateX(-50%);
        background:#ffcc00;padding:15px;border-radius:10px;
        box-shadow:0 4px 10px rgba(0,0,0,0.2);z-index:99999;
    `;
    alert.innerHTML = `
        <b>⚠️ يرجى تفعيل الإشعارات للحصول على التنبيهات!</b><br>
        <button id="enable-notifications" style="margin-top:8px;background:#007bff;color:white;border:none;padding:8px 12px;border-radius:5px;cursor:pointer;">تفعيل</button>
    `;
    document.body.appendChild(alert);

    document.getElementById("enable-notifications").addEventListener("click", requestPermission);
}

// استقبال الرسائل داخل الصفحة (Foreground)
onMessage(messaging, (payload) => {
    console.log("📬 إشعار جديد:", payload);

    const title = payload.notification?.title || "إشعار جديد";
    const body = payload.notification?.body || "";
    const click_action = payload.data?.click_action || window.location.origin;

    const notification = new Notification(title, {
        body,
        icon: "/images/app-icon.png",
        data: { url: click_action }
    });

    notification.onclick = (event) => {
        event.preventDefault();
        window.open(notification.data.url, "_blank");
    };

    // تشغيل الصوت (اختياري)
    const audio = document.getElementById("notificationSound");
    if (audio) audio.play().catch(() => {});
});

// عند تحميل الصفحة
document.addEventListener("DOMContentLoaded", requestPermission);

