<?php
// Incluir configuración centralizada de sesiones
require_once __DIR__ . '/session_config.php';

// Verificar que el usuario esté logueado
if (!verificarUsuarioAutenticado()) {
    header("Location: /sistemaInstitucional/pages/login/login.php");
    exit;
}

// Convertir la sesión del usuario en objeto usando función helper
$datosUsuario = obtenerUsuarioActual();
$usuarioActual = (object) $datosUsuario;

// Opcional: exportar también su nombre completo
//$usuarioActual->nombre_completo = $usuarioActual->nombre . ' ' . $usuarioActual->apellido;
