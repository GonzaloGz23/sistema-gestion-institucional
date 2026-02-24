//console.log('🚀 PWA Service Worker - Modo Solo Online');

const CACHE_NAME = 'sistema-institucional-minimal-v1';
const DEBUG = false; // Cambiar a false en producción
// Solo recursos críticos para que la PWA funcione
const CRITICAL_RESOURCES = [
  '/sistema-gestion-institucional/manifest.json',
  '/sistema-gestion-institucional/dist/assets/images/iconos/app-logo-192.png',
  '/sistema-gestion-institucional/dist/assets/images/iconos/app-logo-512.png'
];

// Recursos estáticos básicos que SÍ podemos cachear (carpetas completas)
const ALLOWED_STATIC = [
  /\/dist\//,           // Toda la carpeta dist
  /\/src\//,            // Toda la carpeta src
  // 🆕 Solo archivos CSS y JS estáticos personalizados
  /\/pages\/common\/css\//,             // CSS personalizados (global-custom-stile.css, etc.)
  /\/pages\/admin\/css\//,              // CSS personalizados de admin
  /\/pages\/user\/css\//,               // CSS personalizados de user
  /\/pages\/common\/js\/common\.js$/,   // Solo common.js (estático)

  // CDNs externos (EXCLUIR Google Fonts para evitar conflictos)
  /cdn\.jsdelivr\.net/,
  // ❌ REMOVIDO: /fonts\.googleapis\.com/, - Esto causaba el problema
  // ❌ REMOVIDO: /fonts\.gstatic\.com/, - Esto causaba el problema
  // 🆕 CDNs y recursos externos específicos
  ///firebasejs/,                         // 🆕 Firebase CDN
  /npm\/sortablejs/,                    // 🆕 SortableJS CDN
  // ❌ REMOVIDO: /\/css2/, - Google Fonts CSS causaba problemas
  // 🆕 Librerías adicionales detectadas
  /@popperjs\/core/,                    // PopperJS
  /tippy\.js/,                          // TippyJS (tooltips)
  /jquery.*\.min\.js$/,                 // jQuery (cualquier versión minificada)
  // Otros recursos estáticos específicos si los hay
  /bootstrap-icons/,
  /sweetalert2\.min/,
  /simplebar\.min/,
  /\.woff2?$/,                          // 🆕 Archivos de fuentes (locales)
  /\.ttf$/,                             // 🆕 Archivos de fuentes (locales)
  /\.ico$/                              // 🆕 Favicons
];

// URLs que NO se cachean (contenido dinámico) - EXCLUIR archivos PHP del sistema
/* const NEVER_CACHE_PATTERNS = [
  /\/backend\//,
  /\/pages\/.*\.js$/,  // JS específicos de páginas (dinámicos)
  /\/pages\/.*\/ui\//, // Componentes UI dinámicos
  /\/pages\/.*\/php\//,     // Archivos PHP específicos de módulos
  /\/api\//,
  /\/controllers\//,
  /\/uploads\//,        // Archivos de usuario - siempre dinámicos
  /\.json$/,
  /\?/,
  /action=/,
  // Operaciones CRUD en español
  /agregar_/i,
  /editar_/i,
  /eliminar_/i,
  /listar_/i,
  /guardar_/i,
  /conseguir_/i,
  /renombrar_/i,
  /habilitar_deshabilitar/i,
  /asignar_/i,
  /crear_/i,
  /obtener_/i,
  /modal_/i,
  /actualizar_/i,
  /borrar_/i,
  /buscar_/i,
  /empleados-/i,
  /finalizada_/i,
  // Módulos del sistema
  /empleados/i,
  /areas/i,
  /edificios/i,
  /espacios/i,
  /equipos/i,
  /entidades/i,
  /capacitacion/i,
  /solicitudes/i,
  /administrador-archivo/i,
  /notas/i,
  /reservas/i,
  /calendario/i,
  /formularios/i,
  /notificacion/i,
  /roles/i,
  /activacion/i
]; */

const NEVER_CACHE_PATTERNS = [
  // 🎯 Carpetas completas del backend
  /\/backend\//,                        // Toda la lógica del servidor
  /\/uploads\//,                        // Archivos de usuario

  // 🎯 JavaScript dinámicos en pages
  /\/pages\/admin\/js\//,               // JS dinámicos de admin
  /\/pages\/user\/js\//,                // JS dinámicos de user

  // 🎯 Componentes UI dinámicos
  /\/pages\/admin\/ui\//,               // UI dinámicos de admin
  /\/pages\/user\/ui\//,                // UI dinámicos de user

  // 🎯 PHP dinámicos específicos
  /\/pages\/user\/php\//,               // PHP dinámicos en subcarpetas

  // 🎯 URLs dinámicas (pero NO archivos de fuentes)
  /\.json$/,                            // Respuestas JSON
  /\?.*action=/,                        // 🆕 URLs con action (más específico)
  /\?.*load_/,                          // 🆕 URLs con load_ (más específico)
  /\?.*get_/,                            // 🆕 URLs con get_ (más específico)
  // 🆕 Excluir archivos de traducción específicos
  /\/es-ES\.json$/,                     // Archivos de idioma específicos
  /\/lang\//,                           // Carpetas de idiomas (si existen)
  /\/locales\//                         // Carpetas de locales (si existen)
];

// URLs PHP que SÍ necesitan ejecutarse (páginas del sistema)
const ALLOWED_PHP_PAGES = [
  /\/index\.php$/,                    // Página principal
  /\/pages\/login\/login\.php$/,      // Login
  /\/pages\/index\.php$/,             // Dashboard
  /\/pages\/activacion\//,            // Páginas de activación
  // 🆕 Páginas específicas de admin (solo archivos .php en raíz)
  ///\/pages\/admin\/[^\/]+\.php$/,               // pages/admin/empleados.php, areas.php, etc.

  // 🆕 Páginas específicas de user (solo archivos .php en raíz)  
  ///\/pages\/user\/[^\/]+\.php$/,                // pages/user/administradorArchivos.php, keep.php, etc.

  // 🆕 Archivos comunes críticos
  ///\/pages\/common\/[^\/]+\.php$/               // pages/common/header.php, sidebar.php, etc.
  /\/pages\/(admin|user|common)\/[^\/]+\.php$/, // PHP principales optimizado
  /\/check_session\.php$/,            // Herramienta de diagnóstico de sesiones

];

self.addEventListener('install', event => {
  //console.log('📱 Instalando PWA - Solo recursos críticos');
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => cache.addAll(CRITICAL_RESOURCES))
      .then(() => self.skipWaiting())
      .catch(err => {
        console.warn('⚠️ Error cargando recursos críticos:', err);
        return Promise.resolve();
      })
  );
});

self.addEventListener('activate', event => {
  //console.log('✅ PWA Activada - Limpiando cachés antiguos');
  event.waitUntil(
    caches.keys().then(cacheNames => {
      return Promise.all(
        cacheNames.map(cacheName => {
          if (cacheName !== CACHE_NAME) {
            //console.log('🗑️ Eliminando caché antigua:', cacheName);
            return caches.delete(cacheName);
          }
        })
      );
    }).then(() => self.clients.claim())
  );
});



// Limpiar caché cuando se solicite desde la app
self.addEventListener('message', event => {
  if (event.data && event.data.type === 'FORCE_REFRESH') {
    //console.log('🔄 Forzando actualización completa');
    caches.keys().then(names => {
      return Promise.all(names.map(name => caches.delete(name)));
    }).then(() => {
      self.clients.matchAll().then(clients => {
        clients.forEach(client => {
          if (client.navigate) {
            client.navigate(client.url);
          } else {
            client.postMessage({ type: 'RELOAD' });
          }
        });
      });
    });
  }
});

//escuchando service worker cambiando
self.addEventListener("push", (event) => {
  //console.log(event.data)
  const notif = event.data.json().notification

  // Determinar la URL base según el entorno
  const baseUrl = self.location.hostname === 'localhost'
    ? 'http://localhost'
    : 'https://example.com';

  // Todas las notificaciones van a la página principal del sistema
  const targetUrl = `${baseUrl}/sistema-gestion-institucional/pages/index.php`;

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
          client.url.includes('/sistema-gestion-institucional/')) {

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


self.addEventListener('fetch', event => {
  const url = new URL(event.request.url);

  // No interceptar recursos de Google Fonts - dejar que el navegador los maneje directamente
  if (event.request.url.includes('googleapis.com') || event.request.url.includes('gstatic.com')) {
    return; // No interceptar, dejar pasar completamente
  }

  // Verificar si es un recurso crítico (iconos PWA + manifest)
  const isCriticalResource = CRITICAL_RESOURCES.some(resource =>
    event.request.url.includes(resource)
  );

  if (isCriticalResource) {
    event.respondWith(
      caches.match(event.request).then(response => {
        return response || fetch(event.request);
      })
    );
    return;
  }

  // Verificar si es una página PHP permitida del sistema
  const isAllowedPHPPage = ALLOWED_PHP_PAGES.some(pattern =>
    pattern.test(url.pathname)
  );

  if (isAllowedPHPPage) {
    //console.log('✅ Permitiendo página PHP del sistema:', url.pathname);
    // Network Only - No cachear, pero permitir ejecución
    event.respondWith(
      fetch(event.request).catch(error => {
        //console.log('❌ Error cargando página PHP:', url.pathname);
        return new Response(`
          <!DOCTYPE html>
          <html lang="es"><head>
          <meta charset="UTF-8">
          <title>Error de Conexión - SIGE</title>
          <style>
            body{font-family:Arial;text-align:center;padding:50px;background:#f8f9fa}
            .error-msg{max-width:400px;margin:0 auto;padding:30px;background:white;border-radius:10px;box-shadow:0 2px 10px rgba(0,0,0,0.1)}
            .icon{font-size:48px;margin-bottom:20px}
            h2{color:#dc3545;margin-bottom:15px}
            p{color:#6c757d;margin-bottom:20px;line-height:1.5}
            button{background:#007bff;color:white;border:none;padding:10px 20px;border-radius:5px;cursor:pointer}
            button:hover{background:#0056b3}
          </style>
          </head><body><div class="error-msg">
          <div class="icon">🔌</div>
          <h2>Error de Conexión</h2>
          <p>No se puede conectar con el servidor SIGE.<br>
          Esto puede deberse a:<br>
          • Problemas de conexión a internet<br>
          • Restricciones del firewall de la red<br>
          • El servidor puede estar temporalmente no disponible</p>
          <button onclick="location.reload()">Reintentar Conexión</button>
          </div></body></html>
        `, { headers: { 'Content-Type': 'text/html; charset=utf-8' } });
      })
    );
    return;
  }

  // Verificar si es contenido dinámico que nunca se debe cachear
  const shouldNeverCache = NEVER_CACHE_PATTERNS.some(pattern =>
    pattern.test(url.pathname) || pattern.test(event.request.url)
  );

  if (shouldNeverCache) {
    //console.log('🚫 No cacheando contenido dinámico:', url.pathname);
    // Network Only para contenido dinámico
    event.respondWith(
      fetch(event.request).catch(error => {
        //console.log('🌐 Sin conexión para contenido dinámico:', url.pathname);
        if (event.request.mode === 'navigate') {
          return new Response(`
            <!DOCTYPE html>
            <html lang="es"><head>
            <meta charset="UTF-8">
            <title>Problema de Conexión - SIGE</title>
            <style>
              body{font-family:Arial;text-align:center;padding:50px;background:#f8f9fa}
              .msg{max-width:450px;margin:0 auto;padding:30px;background:white;border-radius:10px;box-shadow:0 2px 10px rgba(0,0,0,0.1)}
              .icon{font-size:48px;margin-bottom:20px}
              h2{color:#dc3545;margin-bottom:15px}
              p{color:#6c757d;margin-bottom:20px;line-height:1.5;text-align:left}
              .causes{background:#f8f9fa;padding:15px;border-radius:5px;margin:15px 0;text-align:left}
              button{background:#007bff;color:white;border:none;padding:12px 24px;border-radius:5px;cursor:pointer;font-size:16px}
              button:hover{background:#0056b3}
            </style>
            </head><body><div class="msg">
            <div class="icon">🌐</div>
            <h2>Problema de Conexión</h2>
            <p style="text-align:center">SIGE no puede conectarse al servidor</p>
            <div class="causes">
              <strong>Posibles causas:</strong><br>
              • Sin conexión a internet<br>
              • Firewall de la red bloqueando el acceso<br>
              • Proxy corporativo requiere configuración<br>
              • Servidor temporalmente no disponible
            </div>
            <p style="text-align:center">Contacta al administrador de red si el problema persiste</p>
            <button onclick="location.reload()">Reintentar Conexión</button>
            </div></body></html>
          `, { headers: { 'Content-Type': 'text/html; charset=utf-8' } });
        }
        return new Response('', { status: 408 });
      })
    );
    return;
  }

  // Verificar si es un recurso estático permitido (carpetas dist/src/assets)
  const isAllowedStatic = ALLOWED_STATIC.some(pattern =>
    pattern.test(event.request.url)
  );

  if (isAllowedStatic) {
    //console.log('📦 Cacheando recurso estático:', url.pathname);
    // Network First para recursos estáticos permitidos
    event.respondWith(
      fetch(event.request)
        .then(response => {
          if (response.ok) {
            const responseClone = response.clone();
            caches.open(CACHE_NAME)
              .then(cache => cache.put(event.request, responseClone))
              .catch(err => console.warn('Error cacheando:', err));
          }
          return response;
        })
        .catch(() => {
          //console.log('🔍 Buscando en caché:', url.pathname);
          return caches.match(event.request);
        })
    );
    return;
  }

  // Para archivos PHP no permitidos, bloquear
  if (url.pathname.endsWith('.php')) {
    //console.log('🚫 Bloqueando PHP no autorizado:', url.pathname);
    event.respondWith(
      new Response('PHP no autorizado', { status: 403 })
    );
    return;
  }

  // Para todo lo demás, dejar pasar sin intercepción
  //console.log('➡️ Pasando sin intercepción:', url.pathname);
  //event.respondWith(fetch(event.request));
});