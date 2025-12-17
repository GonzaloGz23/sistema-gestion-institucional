<?php
/**
 * Configuración centralizada de sesiones
 * Este archivo debe ser incluido en lugar de session_start() directo
 */

// Verificar si la sesión ya está iniciada
if (session_status() === PHP_SESSION_NONE) {
    // Detectar si estamos en entorno local
    $isLocal = in_array($_SERVER['HTTP_HOST'] ?? '', ['localhost', '127.0.0.1']);
    
    // Configurar directorio personalizado para sesiones
    // $sessionDir = __DIR__ . '/../../sessions';
    // if (!is_dir($sessionDir)) {
    //     mkdir($sessionDir, 0777, true);
    // }
    // ini_set('session.save_path', $sessionDir);
    
    // Intentar establecer gc_maxlifetime antes de iniciar la sesión
    ini_set('session.gc_maxlifetime', 7 * 24 * 60 * 60); // 7 días en segundos
    
    // Configurar parámetros de la sesión (7 días de duración)
    session_set_cookie_params([
        'lifetime' => 7 * 24 * 60 * 60, // 7 días en segundos
        'path' => '/sistemaInstitucional/', // Limitado solo al proyecto sistemaInstitucional
        'domain' => '',
        'secure' => !$isLocal, // false en localhost, true en producción
        'httponly' => true, // Prevenir acceso via JavaScript
        'samesite' => 'Lax' // Para compatibilidad con PWA
    ]);
    
    // Iniciar la sesión
    session_start();
    
    // Regenerar ID de sesión periódicamente para seguridad
    if (!isset($_SESSION['last_regeneration'])) {
        $_SESSION['last_regeneration'] = time();
    } elseif (time() - $_SESSION['last_regeneration'] > 1800) { // 30 minutos
        session_regenerate_id(true);
        $_SESSION['last_regeneration'] = time();
    }
    
    // Mecanismo de renovación de sesión
    if (isset($_SESSION['usuario'])) {
        // Actualizar timestamp de última actividad
        $_SESSION['last_activity'] = time();
        
        // Si la sesión está por expirar (ej: a 1 día de expirar), extenderla
        if (!isset($_SESSION['session_expires_at'])) {
            $_SESSION['session_expires_at'] = time() + 7*24*60*60;
        } elseif ($_SESSION['session_expires_at'] - time() < 24*60*60) {
            // Extender la expiración por 7 días más desde ahora
            $_SESSION['session_expires_at'] = time() + 7*24*60*60;
            
            // Renovar también la cookie
            $params = session_get_cookie_params();
            setcookie(session_name(), session_id(), time() + 7*24*60*60, 
                     $params['path'], $params['domain'], 
                     $params['secure'], $params['httponly']);
        }
    }
}

/**
 * Función helper para verificar si el usuario está autenticado
 */
function verificarUsuarioAutenticado() {
    return isset($_SESSION['usuario']) && isset($_SESSION['usuario']['id']);
}

/**
 * Función helper para obtener datos del usuario actual de forma segura
 */
function obtenerUsuarioActual() {
    if (!verificarUsuarioAutenticado()) {
        return null;
    }
    
    return [
        'id' => $_SESSION['usuario']['id'] ?? null,
        'nombre' => $_SESSION['usuario']['nombre'] ?? '',
        'apellido' => $_SESSION['usuario']['apellido'] ?? '',
        'id_equipo' => $_SESSION['usuario']['id_equipo'] ?? null,
        'id_entidad' => $_SESSION['usuario']['id_entidad'] ?? null,
        'id_rol' => $_SESSION['usuario']['id_rol'] ?? null,
        'institucion' => $_SESSION['usuario']['institucion'] ?? '',
        'modulos_permitidos' => $_SESSION['usuario']['modulos_permitidos'] ?? []
    ];
}

/**
 * Función para redirigir si no está autenticado
 */
function requererAutenticacion($redirigirA = '/sistemaInstitucional/pages/login/login.php') {
    if (!verificarUsuarioAutenticado()) {
        header("Location: $redirigirA");
        exit;
    }
}
?>
