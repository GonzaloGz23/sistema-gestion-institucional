<?php if (!isset($ocultarMenuMovil) || !$ocultarMenuMovil): ?>
  <div class="d-lg-none" id="barraInferior" style="position: fixed; z-index: 1030; bottom: 0; left: 0; right: 0;">
    <div class="w-100 bg-light py-2 shadow d-flex justify-content-evenly text-center">
      <!-- Columna izquierda con el navbar -->
      <div class="col-6">
        <button class="navbar-toggler icon-shape icon-sm rounded" type="button" data-bs-toggle="collapse"
          data-bs-target="#sidenavNavbarphone" aria-controls="sidenavNavbarphone" aria-expanded="false"
          aria-label="Toggle navigation">
          <svg width="20" height="20" viewBox="0 0 18 12" fill="none" xmlns="http://www.w3.org/2000/svg"
            style="filter: drop-shadow(0 0 1px black);">
            <path
              d="M1 12H17C17.55 12 18 11.55 18 11C18 10.45 17.55 10 17 10H1C0.45 10 0 10.45 0 11C0 11.55 0.45 12 1 12ZM1 7H17C17.55 7 18 6.55 18 6C18 5.45 17.55 5 17 5H1C0.45 5 0 5.45 0 6C0 6.55 0.45 7 1 7ZM0 1C0 1.55 0.45 2 1 2H17C17.55 2 18 1.55 18 1C18 0.45 17.55 0 17 0H1C0.45 0 0 0.45 0 1Z"
              fill="currentColor" />
          </svg>
        </button>
      </div><!-- Columna derecha -->
      <div class="col-6">
        <a class="nav-link fw-bold <?= ($archivoActual === 'index.php') ? 'active' : '' ?>" href="<?= $base ?>index.php">
          <svg width="20" height="20" viewBox="0 0 16 18" fill="none" xmlns="http://www.w3.org/2000/svg"
            style="filter: drop-shadow(0 0 1px black);">
            <path
              d="M0 16V7C0 6.68333 0.0709998 6.38333 0.213 6.1C0.355 5.81667 0.550667 5.58333 0.8 5.4L6.8 0.9C7.15 0.633333 7.55 0.5 8 0.5C8.45 0.5 8.85 0.633333 9.2 0.9L15.2 5.4C15.45 5.58333 15.646 5.81667 15.788 6.1C15.93 6.38333 16.0007 6.68333 16 7V16C16 16.55 15.804 17.021 15.412 17.413C15.02 17.805 14.5493 18.0007 14 18H11C10.7167 18 10.4793 17.904 10.288 17.712C10.0967 17.52 10.0007 17.2827 10 17V12C10 11.7167 9.904 11.4793 9.712 11.288C9.52 11.0967 9.28267 11.0007 9 11H7C6.71667 11 6.47933 11.096 6.288 11.288C6.09667 11.48 6.00067 11.7173 6 12V17C6 17.2833 5.904 17.521 5.712 17.713C5.52 17.905 5.28267 18.0007 5 18H2C1.45 18 0.979333 17.8043 0.588 17.413C0.196666 17.0217 0.000666667 16.5507 0 16Z"
              fill="currentColor" />
          </svg>
        </a>
      </div>
    </div>
  </div>

  <!-- Menú desplegable optimizado con scroll -->
  <div class="collapse position-fixed start-0 w-100 bg-white d-lg-none" id="sidenavNavbarphone"
    style="top: 56px; bottom: 60px; z-index: 1020; overflow-y: auto;">
    <div class="container px-3 py-3">
      <?php
      $modulosUnificados = array_merge($modulosAdmin ?? [], $modulosUser ?? []);
      ?>

      <?php if (!empty($modulosUnificados)): ?>
        <div class="d-flex flex-wrap justify-content-evenly gap-3">
          <?php foreach ($modulosUnificados as $mod): ?>
            <?php
            $ruta = ($mod['perfil'] === 'admin')
              ? $base . 'admin/' . $mod['ruta']
              : $base . 'user/' . $mod['ruta'];

            $icono = $mod['icono_svg']
              ? ajustarTamanioSvg($mod['icono_svg'], 30)
              : ($mod['perfil'] === 'admin'
                ? '<i class="fe fe-grid nav-icon fs-3 text-dark"></i>'
                : '<i class="fe fe-tablet nav-icon fs-3 text-dark"></i>');
            ?>
            <a class="d-flex flex-column align-items-center justify-content-center text-decoration-none <?= ($archivoActual === $mod['ruta']) ? 'text-primary' : 'text-dark' ?>"
              href="<?= $ruta ?>" style="width: 80px; min-height: 80px;">
              <div class="icon-image mb-1 position-relative">
                <?= $icono ?>
              </div>
              <span class="small text-center line-clamp-2"><?= htmlspecialchars($mod['nombre']) ?></span>
            </a>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>
<?php endif; ?>
<style>
  /* Limitar texto a 2 líneas y agregar puntos suspensivos */
  .line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  /* Garantizar que el menú no bloquee el botón de cierre */
  @media (max-width: 991.98px) {
    #sidenavNavbarphone.show {
      padding-bottom: 70px;
      /* Espacio extra al final para evitar que el último elemento se oculte */
    }
  }
</style>

<script src="<?= BASE_URL ?>assets/libs/@popperjs/core/dist/umd/popper.min.js"></script>
<script src="<?= BASE_URL ?>assets/libs/bootstrap/dist/js/bootstrap.min.js"></script>
<script src="<?= BASE_URL ?>assets/libs/simplebar/dist/simplebar.min.js"></script>
<!-- notificacion -->
<!-- Theme JS -->
<script src="<?= BASE_URL ?>assets/js/theme.min.js"></script>

<!-- Sweet Alert -->
<script src="<?= BASE_URL ?>assets/libs/sweetAlert/sweetalert2.all.min.js"></script>

<script src="<?= BASE_URL ?>assets/libs/apexcharts/dist/apexcharts.min.js"></script>
<script src="<?= BASE_URL ?>assets/js/vendors/chart.js"></script>

<script src="<?= BASE_URL ?>assets/js/vendors/navbar-nav.js"></script>

<!-- Script personalizado global -->
<script src="<?= ROOT_PATH ?>pages/common/js/common.js"></script>

<script>
  /* anticaché */
  // Anti-caché para todas las peticiones AJAX
  /*   $.ajaxSetup({
      cache: false,
      headers: {
        'Cache-Control': 'no-cache, no-store, must-revalidate',
        'Pragma': 'no-cache',
        'Expires': '0'
      }
    }); */

  // Interceptar todas las peticiones fetch
  const originalFetch = window.fetch;
  window.fetch = function () {
    let [resource, config] = arguments;

    //Añadir parámetro nocache a URLs
    //console.log(resource,config,"mirar esto")
    if (resource.includes('googleapis.com')) {
      return originalFetch.apply(this, [resource, config]);; //no interceptar
    }
    if (typeof resource === 'string') {
      const url = new URL(resource, window.location.href);

      url.searchParams.append('nocache', Date.now());
      resource = url.toString();
    }

    //Añadir headers anti-caché exceptusnfo cuando se intenta notificar
    if (!config) {
      config = {};
    }
    if (!config.headers) {
      config.headers = {};
    }
    config.headers['Cache-Control'] = 'no-cache, no-store, must-revalidate';
    config.headers['Pragma'] = 'no-cache';
    config.headers['Expires'] = '0';

    return originalFetch.apply(this, [resource, config]);
  };



  document.addEventListener('DOMContentLoaded', () => {
    // Verificar permisos de página
    if (typeof window.permisoPagina !== 'undefined' && !window.permisoPagina) {
      Swal.fire({
        icon: 'error',
        title: 'Acceso denegado',
        text: 'No tenés permiso para acceder a esta sección.',
        confirmButtonText: 'Entendido'
      }).then(() => {
        window.location.href = '/sistemainstitucional/pages/admin/';
      });
    }

    // Asegurar que el menú se pueda cerrar siempre
    const menuCollapse = document.getElementById('sidenavNavbarphone');
    const toggleButton = document.querySelector('[data-bs-target="#sidenavNavbarphone"]');

    // Cerrar el menú al hacer clic en cualquier enlace dentro del menú
    const menuLinks = menuCollapse.querySelectorAll('a');
    menuLinks.forEach(link => {
      link.addEventListener('click', () => {
        bootstrap.Collapse.getInstance(menuCollapse).hide();
      });
    });
  });
</script>
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

  async function getServiceWorkerByUrl(urlParcial) {
    const registrations = await navigator.serviceWorker.getRegistrations();
    console.log(registrations.find(r => r.active?.scriptURL.includes(urlParcial)), "se encotro el sw")
    return registrations.find(r => r.active?.scriptURL.includes(urlParcial));
  }
  if (document.getElementById("notificame") != null) {
    document.getElementById("notificame").addEventListener("change", async (e) => {
      if (!e.target.checked) {
        console.log("Notificaciones deshabilitadas");
        return;
      }

      const permiso = await Notification.requestPermission();

      if (permiso !== 'granted') {
        console.warn("Permiso de notificaciones no otorgado.");
        return;
      }

      try {

        // Registrar SW antes de cualquier cosa
        const registration = await getServiceWorkerByUrl(`${window.location.origin}/sistemaInstitucional/sw.js`);
        console.log("SW registrado:", registration);
        if (!registration) {
          console.warn("❌ No se encontró el Service Worker esperado");
          return;
        }

        const currentToken = await getToken(messaging, {
          serviceWorkerRegistration: registration,
          vapidKey: 'YOUR_VAPID_KEY_HERE'
        });

        console.log("Token actual:", currentToken);
        Swal.fire({
          title: 'Registrando dispositivo...',
          text: 'Por favor, espere',
          allowOutsideClick: false,
          didOpen: () => {
            Swal.showLoading();
          }
        });
        // Enviar token a backend
        const response = await fetch(`${window.location.origin}/sistemaInstitucional/pages/common/addDevice.php?deviceid=` + currentToken, {
          headers: {
            'Content-Type': 'application/json'
          },
        });

        const data = await response.json();

        // Mostrar mensaje de éxito
        Swal.fire({
          icon: 'success',
          title: 'Dispositivo registrado',
          text: data.msg ?? 'Se agregó correctamente.',
          confirmButtonText: 'Entendido'
        });
      } catch (error) {
        console.error("Error en el proceso de notificaciones:", error);
      }
    });
  }

  // Escuchar mensajes del Service Worker para navegación desde notificaciones
  if ('serviceWorker' in navigator) {
    navigator.serviceWorker.addEventListener('message', event => {
      if (event.data && event.data.type === 'NOTIFICATION_NAVIGATE') {
        const targetUrl = event.data.url;
        console.log('📱 PWA: Navegando desde notificación a:', targetUrl);

        // Navegar a la URL objetivo
        window.location.href = targetUrl;
      }
    });
  }
</script>