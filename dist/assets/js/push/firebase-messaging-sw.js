// firebase-messaging-sw.js
importScripts('https://www.gstatic.com/firebasejs/11.7.3/firebase-app.js');
importScripts('https://www.gstatic.com/firebasejs/11.7.3/firebase-messaging.js');

// Configuración de Firebase usando placeholders
// Sustituye estos valores por variables de entorno en tu proceso de despliegue
const firebaseConfig = {
  apiKey: "YOUR_API_KEY",
  authDomain: "YOUR_AUTH_DOMAIN",
  projectId: "YOUR_PROJECT_ID",
  storageBucket: "YOUR_STORAGE_BUCKET",
  messagingSenderId: "YOUR_MESSAGING_SENDER_ID",
  appId: "YOUR_APP_ID",
  measurementId: "YOUR_MEASUREMENT_ID"
};

firebase.initializeApp(firebaseConfig);

const messaging = firebase.messaging();

messaging.onBackgroundMessage((payload) => {
  console.log('[firebase-messaging-sw.js] Notificación en segundo plano recibida:', payload);

  const notificationTitle = payload.notification.title;
  const notificationOptions = {
    body: payload.notification.body,
    icon: payload.notification.icon || '/default-icon.png' // Agregué un fallback por seguridad
  };

  self.registration.showNotification(notificationTitle, notificationOptions);
});