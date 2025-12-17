<?php
/**
 * Script para testear el estado de las sesiones
 * Este archivo muestra información sobre la sesión actual y el usuario autenticado
 */

// Incluir configuración centralizada de sesiones
require_once './backend/config/session_config.php';

// Configurar cabeceras para mostrar contenido como HTML
header('Content-Type: text/html; charset=utf-8');

// Función para mostrar datos de forma legible
function mostrarDatos($titulo, $datos) {
    echo "<h3>$titulo</h3>";
    echo "<pre>";
    print_r($datos);
    echo "</pre>";
    echo "<hr>";
}

// Obtener información de la cookie de sesión
function obtenerInfoCookieSesion() {
    $cookieName = session_name();
    if (!isset($_COOKIE[$cookieName])) {
        return ['existe' => false];
    }
    
    $params = session_get_cookie_params();
    $expiracion = isset($_COOKIE[$cookieName . '_expiry']) ? $_COOKIE[$cookieName . '_expiry'] : null;
    
    if (!$expiracion && isset($_COOKIE[$cookieName])) {
        // Intentar obtener la expiración desde la cookie directamente
        $expiracion = isset($params['lifetime']) ? (time() + $params['lifetime']) : null;
    }
    
    return [
        'existe' => true,
        'nombre' => $cookieName,
        'valor' => $_COOKIE[$cookieName],
        'expiracion' => $expiracion ? date('Y-m-d H:i:s', $expiracion) : 'No disponible',
        'tiempo_restante' => $expiracion ? round(($expiracion - time()) / (60 * 60 * 24), 1) . ' días' : 'No disponible',
        'path' => $params['path'],
        'domain' => $params['domain'] ?: 'No especificado',
        'secure' => $params['secure'] ? 'Sí' : 'No',
        'httponly' => $params['httponly'] ? 'Sí' : 'No',
        'samesite' => $params['samesite'] ?? 'No especificado'
    ];
}

// Verificar configuración PHP
function verificarConfiguracionPHP() {
    return [
        'session.cookie_lifetime' => ini_get('session.cookie_lifetime'),
        'session.gc_maxlifetime' => ini_get('session.gc_maxlifetime'),
        'session.save_path' => ini_get('session.save_path'),
        'session.name' => ini_get('session.name'),
        'session.cookie_path' => ini_get('session.cookie_path'),
        'session.cookie_domain' => ini_get('session.cookie_domain'),
        'session.cookie_secure' => ini_get('session.cookie_secure'),
        'session.cookie_httponly' => ini_get('session.cookie_httponly'),
        'session.cookie_samesite' => ini_get('session.cookie_samesite'),
        'session.use_strict_mode' => ini_get('session.use_strict_mode'),
        'session.use_cookies' => ini_get('session.use_cookies'),
        'session.use_only_cookies' => ini_get('session.use_only_cookies'),
        'session.cache_limiter' => ini_get('session.cache_limiter'),
        'session.cache_expire' => ini_get('session.cache_expire'),
        'session.use_trans_sid' => ini_get('session.use_trans_sid')
    ];
}

// Verificar posibles problemas
function verificarProblemasSesion() {
    $problemas = [];
    $cookieInfo = obtenerInfoCookieSesion();
    $phpConfig = verificarConfiguracionPHP();
    
    // Verificar si la cookie existe
    if (!$cookieInfo['existe']) {
        $problemas[] = 'La cookie de sesión no existe en el navegador';
    }
    
    // Verificar si la configuración PHP sobrescribe la configuración local
    if ($phpConfig['session.cookie_lifetime'] != '0' && $phpConfig['session.cookie_lifetime'] < 7 * 24 * 60 * 60) {
        $problemas[] = 'La configuración global de PHP (session.cookie_lifetime = ' . $phpConfig['session.cookie_lifetime'] . ') puede estar sobrescribiendo la duración de 7 días';
    }
    
    // Verificar tiempo de recolección de basura
    if ($phpConfig['session.gc_maxlifetime'] < 7 * 24 * 60 * 60) {
        $problemas[] = 'El tiempo de recolección de basura (session.gc_maxlifetime = ' . $phpConfig['session.gc_maxlifetime'] . ') es menor a 7 días, lo que puede eliminar sesiones inactivas antes de tiempo';
    }
    
    // Verificar path de la cookie
    if ($cookieInfo['existe'] && $cookieInfo['path'] != '/sistemaInstitucional/') {
        $problemas[] = 'El path de la cookie (' . $cookieInfo['path'] . ') no coincide con el configurado (/sistemaInstitucional/)';
    }
    
    // Verificar si hay cabeceras que puedan estar afectando
    $headers = headers_list();
    foreach ($headers as $header) {
        // Verificar cabeceras Cache-Control
        if (stripos($header, 'Cache-Control:') !== false) {
            // Si contiene 'private' y 'must-revalidate', está bien configurado
            if (stripos($header, 'private') !== false && stripos($header, 'must-revalidate') !== false) {
                // Configuración correcta, no hacer nada
            } 
            // Si contiene 'no-store', puede afectar la persistencia
            else if (stripos($header, 'no-store') !== false) {
                $problemas[] = 'La cabecera "' . $header . '" puede estar afectando la persistencia de cookies';
            }
        }
        
        // Verificar cabeceras Pragma
        if (stripos($header, 'Pragma:') !== false) {
            // Si contiene 'private', está bien configurado
            if (stripos($header, 'private') !== false) {
                // Configuración correcta, no hacer nada
            } 
            // Si contiene 'no-cache', puede afectar la persistencia
            else if (stripos($header, 'no-cache') !== false) {
                $problemas[] = 'La cabecera "' . $header . '" puede estar afectando la persistencia de cookies';
            }
        }
    }
    
    return $problemas;
}

$cookieInfo = obtenerInfoCookieSesion();
$phpConfig = verificarConfiguracionPHP();
$problemas = verificarProblemasSesion();

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test de Sesiones</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            line-height: 1.6;
        }
        pre {
            background-color: #f5f5f5;
            padding: 10px;
            border-radius: 5px;
            overflow: auto;
        }
        .success {
            color: green;
            font-weight: bold;
        }
        .error {
            color: red;
            font-weight: bold;
        }
        .warning {
            color: orange;
            font-weight: bold;
        }
        .info {
            color: blue;
        }
        .section {
            border: 1px solid #ddd;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <h1>Test de Sesiones - Sistema Institucional</h1>
    
    <div class="section">
        <h2>Estado de la Sesión</h2>
        <?php if(session_status() === PHP_SESSION_ACTIVE): ?>
            <p class="success">✅ La sesión está activa</p>
            <p>ID de Sesión: <span class="info"><?php echo session_id(); ?></span></p>
            <p>Nombre de Sesión: <span class="info"><?php echo session_name(); ?></span></p>
            <p>Última regeneración: <span class="info"><?php echo isset($_SESSION['last_regeneration']) ? date('Y-m-d H:i:s', $_SESSION['last_regeneration']) : 'No disponible'; ?></span></p>
        <?php else: ?>
            <p class="error">❌ La sesión NO está activa</p>
        <?php endif; ?>
    </div>
    
    <div class="section">
        <h2>Información de Cookie de Sesión</h2>
        <?php if($cookieInfo['existe']): ?>
            <p class="success">✅ Cookie de sesión encontrada</p>
            <p>Nombre: <span class="info"><?php echo $cookieInfo['nombre']; ?></span></p>
            <p>Valor: <span class="info"><?php echo substr($cookieInfo['valor'], 0, 10) . '...'; ?></span></p>
            <p>Expiración: <span class="info"><?php echo $cookieInfo['expiracion']; ?></span></p>
            <p>Tiempo restante: <span class="info"><?php echo $cookieInfo['tiempo_restante']; ?></span></p>
            <p>Path: <span class="info"><?php echo $cookieInfo['path']; ?></span></p>
            <p>Domain: <span class="info"><?php echo $cookieInfo['domain']; ?></span></p>
            <p>Secure: <span class="info"><?php echo $cookieInfo['secure']; ?></span></p>
            <p>HttpOnly: <span class="info"><?php echo $cookieInfo['httponly']; ?></span></p>
            <p>SameSite: <span class="info"><?php echo $cookieInfo['samesite']; ?></span></p>
        <?php else: ?>
            <p class="error">❌ No se encontró la cookie de sesión</p>
        <?php endif; ?>
    </div>
    
    <div class="section">
        <h2>Configuración PHP de Sesiones</h2>
        <p>Duración de cookie (session.cookie_lifetime): <span class="info"><?php echo $phpConfig['session.cookie_lifetime']; ?> segundos</span></p>
        <p>Tiempo máximo de vida (session.gc_maxlifetime): <span class="info"><?php echo $phpConfig['session.gc_maxlifetime']; ?> segundos</span></p>
        <p>Ruta de guardado (session.save_path): <span class="info"><?php echo $phpConfig['session.save_path'] ?: 'Por defecto'; ?></span></p>
        <p>Nombre de sesión (session.name): <span class="info"><?php echo $phpConfig['session.name']; ?></span></p>
        <p>Path de cookie (session.cookie_path): <span class="info"><?php echo $phpConfig['session.cookie_path']; ?></span></p>
        <p>Dominio de cookie (session.cookie_domain): <span class="info"><?php echo $phpConfig['session.cookie_domain'] ?: 'No especificado'; ?></span></p>
    </div>
    
    <div class="section">
        <h2>Posibles Problemas Detectados</h2>
        <?php if(empty($problemas)): ?>
            <p class="success">✅ No se detectaron problemas que puedan afectar la duración de 7 días</p>
        <?php else: ?>
            <p class="warning">⚠️ Se detectaron posibles problemas:</p>
            <ul>
                <?php foreach($problemas as $problema): ?>
                    <li class="warning"><?php echo $problema; ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
        
        <h3>Configuración actual para garantizar sesiones de 7 días:</h3>
        <ol>
            <li><span class="success">✅ Configurado</span> - <code>session.cookie_lifetime</code> establecido a 604800 (7 días) en la aplicación</li>
            <li><span class="success">✅ Configurado</span> - <code>session.gc_maxlifetime</code> establecido a 604800 en la aplicación</li>
            <li><span class="success">✅ Configurado</span> - Directorio personalizado para sesiones en <code><?php echo realpath(__DIR__ . '/sessions'); ?></code></li>
            <li><span class="success">✅ Configurado</span> - Cabeceras de caché modificadas para permitir persistencia de cookies</li>
            <li><span class="success">✅ Configurado</span> - Mecanismo de renovación automática de sesión implementado</li>
            <li><span class="info">ℹ️ Verificar</span> - Asegurarse de que el navegador no esté configurado para eliminar cookies al cerrarse</li>
            <li><span class="info">ℹ️ Verificar</span> - Comprobar que no haya extensiones del navegador que limpien cookies</li>
        </ol>
    </div>
    
    <div class="section">
        <h2>Estado de Autenticación</h2>
        <?php if(verificarUsuarioAutenticado()): ?>
            <p class="success">✅ Usuario autenticado</p>
            <?php $usuario = obtenerUsuarioActual(); ?>
            <p>ID de Usuario: <span class="info"><?php echo $usuario['id']; ?></span></p>
            <p>Nombre: <span class="info"><?php echo $usuario['nombre'] . ' ' . $usuario['apellido']; ?></span></p>
            <p>Rol: <span class="info"><?php echo $usuario['id_rol']; ?></span></p>
            <p>Entidad: <span class="info"><?php echo $usuario['id_entidad']; ?></span></p>
        <?php else: ?>
            <p class="error">❌ No hay usuario autenticado</p>
            <p>Para probar con un usuario autenticado, primero debes <a href="/sistemaInstitucional/pages/login/login.php">iniciar sesión</a>.</p>
        <?php endif; ?>
    </div>
    
    <div class="section">
        <h2>Contenido Completo de la Sesión</h2>
        <?php mostrarDatos('$_SESSION', $_SESSION); ?>
    </div>
    
    <div class="section">
        <h2>Todas las Cookies</h2>
        <?php mostrarDatos('$_COOKIE', $_COOKIE); ?>
    </div>
    
    <div class="section">
        <h2>Información del Servidor</h2>
        <p>Host: <span class="info"><?php echo $_SERVER['HTTP_HOST'] ?? 'No disponible'; ?></span></p>
        <p>Ruta: <span class="info"><?php echo $_SERVER['REQUEST_URI'] ?? 'No disponible'; ?></span></p>
        <p>Entorno Local: <span class="info"><?php echo $isLocal ? 'Sí' : 'No'; ?></span></p>
        <p>User Agent: <span class="info"><?php echo $_SERVER['HTTP_USER_AGENT'] ?? 'No disponible'; ?></span></p>
    </div>
    
    <div style="margin-top: 30px;">
        <a href="/sistemaInstitucional/pages/login/login.php">Ir a Login</a> | 
        <a href="/sistemaInstitucional/backend/controller/auth/logout.php">Cerrar Sesión</a> |
        <a href="<?php echo $_SERVER['PHP_SELF']; ?>">Actualizar</a>
    </div>
</body>
</html>