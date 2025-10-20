//Import Firebase scripts (required for background notifications)
importScripts('https://www.gstatic.com/firebasejs/10.8.0/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/10.8.0/firebase-messaging-compat.js');
console.log('Firebase Config:');
console.log('Firebase Config:', window.firebaseConfig);

const firebaseConfig = window.firebaseConfig;


const messaging = firebase.messaging();

messaging.onBackgroundMessage((payload) => {
  console.log('[firebase-messaging-sw.js] Received background message:', payload);
  const { title, body } = payload.notification;

  if (localStorage.getItem("notificationReceived") === "true") {
      return;  
  }

  self.registration.showNotification(title, { body });

  localStorage.setItem("notificationReceived", "true");

  setTimeout(() => {
      localStorage.removeItem("notificationReceived");
  }, 5 * 60 * 1000); 
});

