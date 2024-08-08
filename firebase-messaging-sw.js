// Give the service worker access to Firebase Messaging.
// Note that you can only use Firebase Messaging here. Other Firebase libraries
// are not available in the service worker.
importScripts('https://www.gstatic.com/firebasejs/8.10.1/firebase-app.js');
importScripts('https://www.gstatic.com/firebasejs/8.10.1/firebase-messaging.js');

// Initialize the Firebase app in the service worker by passing in
// your app's Firebase config object.
// https://firebase.google.com/docs/web/setup#config-object
firebase.initializeApp({
    apiKey: "AIzaSyDuqgaX1O4_YQdYOUGNSbKnkm_ufDOtckw",
    authDomain: "sree3-54527.firebaseapp.com",
    projectId: "sree3-54527",
    storageBucket: "sree3-54527.appspot.com",
    messagingSenderId: "903070301917",
    appId: "1:903070301917:web:cc3e56e795355ebd0d2534",
    measurementId: "G-GFC8PH6N2G"
});

// Retrieve an instance of Firebase Messaging so that it can handle background
// messages.
const messaging = firebase.messaging();

messaging.onBackgroundMessage((payload) => {
    console.log(
        '[firebase-messaging-sw.js] Received background message ',
        payload
    );
    // Customize notification here
    const notificationTitle = payload.notification.title;
    const notificationOptions = {
        body: payload.notification.body,
        icon: 'https://freepngimg.com/thumb/emoji/3-2-love-hearts-eyes-emoji-png.png'
    };

    self.registration.showNotification(notificationTitle, notificationOptions);
});
