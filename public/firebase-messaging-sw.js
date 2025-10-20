




// Import Firebase scripts (required for background notifications)
importScripts('https://www.gstatic.com/firebasejs/10.8.0/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/10.8.0/firebase-messaging-compat.js');

// Initialize Firebase
const firebaseConfig = window.firebaseConfig;

console.log('Firebase Config:', firebaseConfig);

const messaging = firebase.messaging();

// // Optional: Handle background notifications
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

