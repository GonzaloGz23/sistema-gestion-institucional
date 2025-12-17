<!DOCTYPE html>
<html>

<head>
  <title>Notificaciones Push</title>
  <!-- <script src="https://www.gstatic.com/firebasejs/9.23.0/firebase-app-compat.js"></script> -->
  <!-- <script src="https://www.gstatic.com/firebasejs/9.23.0/firebase-messaging-compat.js"></script> -->
</head>

<body>
  <h1>Suscripción a notificaciones push</h1>
  <button id="subscribirse">Suscribirse</button>
  <p id="token"></p>

  <script type="module">
    // Import the functions you need from the SDKs you need
    import {
      initializeApp
    } from "https://www.gstatic.com/firebasejs/10.7.1/firebase-app.js";
    import {
      getMessaging,
      getToken
    } from "https://www.gstatic.com/firebasejs/10.7.1/firebase-messaging.js";

    // TODO: Add SDKs for Firebase products that you want to use
    // https://firebase.google.com/docs/web/setup#available-libraries
    const firebaseConfig = {
      apiKey: "YOUR_FIREBASE_API_KEY",
      authDomain: "your-project.firebaseapp.com",
      projectId: "your-project-id",
      storageBucket: "your-project.firebasestorage.app",
      messagingSenderId: "YOUR_SENDER_ID",
      appId: "YOUR_APP_ID"
    };
    const app = initializeApp(firebaseConfig);
    const messaging = getMessaging(app);


    document.getElementById("subscribirse").addEventListener("click", async () => {
      const permiso = await Notification.requestPermission();

      if (permiso === 'granted') {
        try {
          navigator.serviceWorker.register('firebase-messaging-sw.js').then((registration) => {

            console.log("SW registrado:", registration);
            getToken(messaging, {
              serviceWorkerRegistration: registration,
              vapidKey: 'YOUR_VAPID_KEY_HERE'
            }).then((currentToken) => {
              document.getElementById("token").innerText = currentToken
              console.log(currentToken, "mirar pinchi token")
              fetch('./notificaciones_backend.php?deviceid=' + currentToken, {
                method: 'POST',
                headers: {
                  'Content-Type': 'application/json'
                },
              })
                .then(response => response.json())
                .then(data => {
                  console.log('Respuesta de FCM:', data);
                })
                .catch(error => {
                  console.error('Error enviando notificación:', error);
                });
            })
          });
        } catch (error) {
          console.error("Error al obtener token:", error);
        }
      } else {
        console.warn("Permiso de notificaciones no otorgado.");
      }
    });
  </script>
</body>

</html>