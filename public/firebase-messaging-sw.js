importScripts('https://www.gstatic.com/firebasejs/10.7.2/firebase-app.js');
importScripts('https://www.gstatic.com/firebasejs/10.7.2/firebase-messaging.js');

firebase.initializeApp({
    apiKey: "AIzaSyCiUC9036VhsL7SzfB2yDgC1YYq9xiWkqQ",
    authDomain: "eagle-24712.firebaseapp.com",
    projectId: "eagle-24712",
    storageBucket: "eagle-24712.appspot.com",
    messagingSenderId: "817000206466",
    appId: "1:817000206466:web:eagle24712appcode"
});

const messaging = firebase.messaging();

