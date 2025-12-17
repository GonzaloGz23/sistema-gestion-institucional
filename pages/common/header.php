<?php
// Incluir configuración centralizada de sesiones
require_once __DIR__ . '/../../backend/config/session_config.php';
require_once 'config.php';
require_once ROOT_DIR . '/backend/config/database.php';

/* 
// Forzar no caché para todas las páginas
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: -1");

*/

// Modificar cabeceras para permitir almacenamiento de cookies
// pero seguir evitando caché de contenido dinámico
header("Cache-Control: private, must-revalidate, max-age=0");
header("Pragma: private"); // Permite cachear en navegadores antiguos
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Fecha en el pasado

if (!isset($evitarValidacionUsuario)) {
    require_once ROOT_DIR . '/backend/config/usuario_actual.php';
}

require_once ROOT_DIR . '/backend/config/verificar_acceso.php';
?>
<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <!-- Modificar Anti-caché meta tags para permitir cookies -->
    <meta http-equiv="Cache-Control" content="private, must-revalidate">
    <meta http-equiv="Pragma" content="private">
    <meta http-equiv="Expires" content="Sat, 26 Jul 1997 05:00:00 GMT">
    
    <link rel="shortcut icon" type="image/x-icon" href="<?= BASE_URL ?>assets/images/logos/se.ico?v=<?= time() ?>" />


    <!-- PWA Meta Tags -->
    <link rel="manifest" href="<?= ROOT_PATH ?>manifest.json">
    <meta name="theme-color" content="#2A5CAA">
    <!-- Remover meta tag deprecated -->
    <!-- <meta name="apple-mobile-web-app-capable" content="yes"> -->
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="SIGE">
    
    <!-- CSS -->
    <link href="<?= BASE_URL ?>assets/fonts/feather/feather.css" rel="stylesheet" />
    <link href="<?= BASE_URL ?>assets/libs/bootstrap-icons/font/bootstrap-icons.min.css" rel="stylesheet" />
    <link href="<?= BASE_URL ?>assets/libs/simplebar/dist/simplebar.min.css" rel="stylesheet" />
    
    <!-- CORREGIR: Usar dist para theme.min.css -->
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/theme.min.css">
    
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/libs/sweetAlert/sweetalert2.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/libs/tiny-slider/dist/tiny-slider.css" />
    <link rel="stylesheet" href="<?= ROOT_PATH ?>pages/common/css/global-custom-stile.css" />
    <link rel="stylesheet" href="<?= ROOT_PATH ?>pages/common/css/notificaciones.css" />
    
    <!-- JavaScript que debe ir en head -->
    <!-- CORREGIR: Usar dist para darkMode.js -->
    <script src="<?= BASE_URL ?>assets/js/vendors/darkMode.js"></script>
    
    <!-- Cambiar a defer para evitar error de módulo -->
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js" defer></script>
    
    <!-- PWA Service Worker Registration -->
    <script>
        // Registro del Service Worker para PWA
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('<?= ROOT_PATH ?>sw.js')
                    .then(registration => {
                       // console.log('✅ PWA registrada correctamente');
                    })
                    .catch(error => {
                        console.warn('❌ Error registrando PWA:', error);
                    });
            });
        }
        
        // Detectar cuando se usa en modo standalone (PWA instalada)
        if (window.matchMedia('(display-mode: standalone)').matches) {
           // console.log('📱 Ejecutándose como PWA instalada');
            document.addEventListener('DOMContentLoaded', () => {
                document.body.classList.add('pwa-mode');
            });
        }
    </script>
    
    <title>SIGE</title>
</head>

<body>
    <!-- Indicador de conexión -->
    <div id="offline-indicator" class="d-none alert alert-warning position-fixed" style="top: 10px; right: 10px; z-index: 9999;">
        <i class="bi bi-wifi-off"></i> Sin conexión
    </div>

    <!-- Script para detectar conexión -->
    <script>
        // Detector de conexión
        window.addEventListener('online', () => {
            document.getElementById('offline-indicator').classList.add('d-none');
            console.log('✅ Conexión restaurada');
        });

        window.addEventListener('offline', () => {
            document.getElementById('offline-indicator').classList.remove('d-none');
            console.log('❌ Sin conexión');
        });

        // Verificar estado inicial
        if (!navigator.onLine) {
            document.getElementById('offline-indicator').classList.remove('d-none');
        }
    </script>