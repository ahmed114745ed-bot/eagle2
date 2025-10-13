importScripts("https://www.gstatic.com/firebasejs/10.7.2/firebase-app-compat.js");
importScripts("https://www.gstatic.com/firebasejs/10.7.2/firebase-messaging-compat.js");

firebase.initializeApp({
    apiKey: "6d758cc9885ad14a7b90d0192daa183cd09f773f",
    authDomain: "eagle-24712.firebaseapp.com",
    projectId: "eagle-24712",
    storageBucket: "eagle-24712.appspot.com",
    messagingSenderId: "817000206466",
    appId: "1:817000206466:web:eagle24712appcode"
});

const messaging = firebase.messaging();

messaging.onBackgroundMessage(function (payload) {
    const notificationTitle = payload.notification.title;
    const notificationOptions = {
        body: payload.notification.body,
        icon: "/images/app-icon.png"
    };

    self.registration.showNotification(notificationTitle, notificationOptions);
});



