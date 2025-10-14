




// Import Firebase scripts (required for background notifications)
importScripts('https://www.gstatic.com/firebasejs/10.8.0/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/10.8.0/firebase-messaging-compat.js');

// Initialize Firebase
firebase.initializeApp({
apiKey: "AIzaSyCn9-Z_mz6zlq6A86bvLDkhrrqNuXSd9xg",
authDomain: "eagle-24712.firebaseapp.com",
projectId: "eagle-24712",
storageBucket: "eagle-24712.firebasestorage.app",
messagingSenderId: "817000206466",
appId: "1:817000206466:web:55bd3dd1ff3848eb91249b"
});

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

