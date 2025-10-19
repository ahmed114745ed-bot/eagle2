importScripts('https://www.gstatic.com/firebasejs/10.7.2/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/10.7.2/firebase-messaging-compat.js');

firebase.initializeApp({
    apiKey: "AIzaSyCiUC9036VhsL7SzfB2yDgC1YYq9xiWkqQ",
    authDomain: "eagle-24712.firebaseapp.com",
    projectId: "eagle-24712",
    storageBucket: "eagle-24712.appspot.com",
    messagingSenderId: "817000206466",
    appId: "1:817000206466:web:eagle24712appcode"
});

const messaging = firebase.messaging();

messaging.onBackgroundMessage(function(payload) {
    console.log('[SW] Received background message ', payload);
    const notificationTitle = payload.notification?.title || 'Background Message';
    const notificationOptions = {
        body: payload.notification?.body,
        icon: '/images/app-icon.png'
    };
    self.registration.showNotification(notificationTitle, notificationOptions);
});
