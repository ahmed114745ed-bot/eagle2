
importScripts('https://www.gstatic.com/firebasejs/10.8.0/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/10.8.0/firebase-messaging-compat.js');
console.log('Firebase Config:2');

console.log('Firebase Config:2', window );

const firebaseConfig = window.firebaseConfig;

console.log('Firebase Config:', firebaseConfig);

const messaging = firebase.messaging();

messaging.onBackgroundMessage((payload) => {
    console.log(
        '[firebase-messaging-sw.js] Received background message ',
        payload
    );
    const notificationTitle = payload.notification.title;
    const notificationOptions = {
        body: payload.notification.body,
        icon: 'https://freepngimg.com/thumb/emoji/3-2-love-hearts-eyes-emoji-png.png'
    };

    self.registration.showNotification(notificationTitle, notificationOptions);
});
