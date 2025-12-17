<?php
// Incluir configuración centralizada de sesiones
require_once '../../config/session_config.php';

// Eliminar todas las variables de sesión
$_SESSION = array();

// Si hay una cookie de sesión, eliminarla
if (isset($_COOKIE[session_name()])) {
    // Obtener los parámetros actuales de la cookie
    $params = session_get_cookie_params();
    // Usar los mismos parámetros que en session_config.php
    setcookie(
        session_name(),
        '',
        time() - 3600,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

// Destruir la sesión
session_destroy();

// Redirigir al login (ajustar la ruta según tu proyecto)
header("Location: /sistemaInstitucional/pages/login/login.php");
exit;
?>