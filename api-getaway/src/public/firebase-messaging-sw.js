// public/firebase-messaging-sw.js
importScripts("https://www.gstatic.com/firebasejs/12.1.0/firebase-app-compat.js");
importScripts("https://www.gstatic.com/firebasejs/12.1.0/firebase-messaging-compat.js");

firebase.initializeApp({
    apiKey: "{{ env('FIREBASE_WEB_API_KEY') }}",
    authDomain: "{{ env('FIREBASE_WEB_AUTH_DOMAIN') }}",
    projectId: "{{ env('FIREBASE_WEB_PROJECT_ID') }}",
    storageBucket: "{{ env('FIREBASE_WEB_PROJECT_ID') }}.appspot.com",
    messagingSenderId: "{{ env('FIREBASE_WEB_MESSAGING_SENDER_ID') }}",
    appId: "{{ env('FIREBASE_WEB_APP_ID') }}",
});

// Background push
const messaging = firebase.messaging();

messaging.onBackgroundMessage((payload) => {
    console.log("Background message:", payload);
    self.registration.showNotification(payload.notification.title, {
        body: payload.notification.body,
        icon: "/favicon.ico",
    });
});
