<?php
// Incluir configuración centralizada de sesiones
require_once __DIR__ . '/session_config.php';

// Rutas que no requieren validación de acceso
$rutasPublicas = ['login.php', 'index.php', 'activacion.php', 'creacionEntidad.php'];

// Obtener nombre del archivo actual
$archivoActual = basename($_SERVER['PHP_SELF']);

// Obtener módulos permitidos por sesión usando función helper
$datosUsuario = obtenerUsuarioActual();
$modulosPermitidos = $datosUsuario['modulos_permitidos'] ?? [];

$permitido = false;

// Validar si es ruta pública o si tiene permiso por rol
if (in_array($archivoActual, $rutasPublicas)) {
    $permitido = true;
} else {
    foreach ($modulosPermitidos as $modulo) {
        if ($archivoActual === basename($modulo)) {
            $permitido = true;
            break;
        }
    }
}

// Si no tiene permiso, mostrar alerta
if (!$permitido) {
    echo "
    <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            Swal.fire({
                icon: 'error',
                title: 'Acceso denegado',
                text: 'No tenés permiso para acceder a esta página.',
                confirmButtonText: 'Entendido'
            }).then(() => {
                window.location.href = '/sistemaInstitucional/pages/index.php';
            });
        });
    </script>
    ";
    exit;
}
