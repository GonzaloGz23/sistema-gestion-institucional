
self.addEventListener("push", (event) => {
  console.log(event.data)
  const notif = event.data.json().notification
  
  // Determinar la URL base según el entorno
  const baseUrl = self.location.hostname === 'localhost' 
    ? 'http://localhost' 
    : 'https://example.com';
  
  // Todas las notificaciones van a la página principal del sistema
  const targetUrl = `${baseUrl}/sistemaInstitucional/pages/index.php`;
  
  event.waitUntil(self.registration.showNotification(notif.title, {
    body: notif.body,
    icon: '/icono.webp',
    data: {
      url: targetUrl
    }
  }))
})

self.addEventListener("notificationclick", (event) => {
  event.notification.close();
  
  const targetUrl = event.notification.data.url;
  
  event.waitUntil(
    // Buscar si ya hay una ventana/pestaña de la PWA abierta
    clients.matchAll({
      type: 'window',
      includeUncontrolled: true
    }).then(clientList => {
      // Buscar una ventana que ya esté en el dominio del sistema
      for (const client of clientList) {
        const clientUrl = new URL(client.url);
        const targetUrlObj = new URL(targetUrl);
        
        // Si encontramos una ventana del mismo dominio y path base
        if (clientUrl.hostname === targetUrlObj.hostname && 
            client.url.includes('/sistemaInstitucional/')) {
          
          // Si la ventana ya está en la URL objetivo, solo enfocarla
          if (client.url === targetUrl) {
            return client.focus();
          }
          
          // Si es una ventana PWA diferente, navegar a la nueva URL
          if (client.navigate) {
            client.focus();
            return client.navigate(targetUrl);
          } else {
            // Fallback: enviar mensaje para que la ventana navegue
            client.focus();
            client.postMessage({
              type: 'NOTIFICATION_NAVIGATE',
              url: targetUrl
            });
            return client;
          }
        }
      }
      
      // Si no hay ventana PWA abierta, abrir una nueva
      return clients.openWindow(targetUrl);
    }).catch(error => {
      console.warn('Error manejando click de notificación:', error);
      // Fallback: abrir nueva ventana
      return clients.openWindow(targetUrl);
    })
  );
})

 