<?php
require_once '../../../config/database.php';
// Incluir configuración de sesión y validar usuario
require_once "../../../config/session_config.php";

// Verificar autenticación
if (!verificarUsuarioAutenticado()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Usuario no autenticado', 'debug' => 'No hay sesión activa']);
    exit;
}

header('Content-Type: application/json');

// Obtener datos del usuario
$usuarioActual = obtenerUsuarioActual();

// Validar que sea admin (comentado temporalmente para debug)
// TODO: Descomentar después de verificar que funciona
/*
if (!$usuarioActual || !isset($usuarioActual['rol']) || $usuarioActual['rol'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Acceso no autorizado. Se requieren permisos de administrador.', 'debug' => 'Rol actual: ' . ($usuarioActual['rol'] ?? 'No definido')]);
    exit;
}
*/

try {
    // DEBUG: Primero verificar si hay equipos en total
    $stmtTotal = $pdo->prepare("SELECT COUNT(*) as total FROM equipos");
    $stmtTotal->execute();
    $totalEquipos = $stmtTotal->fetch()['total'];
    
    // Obtener todos los equipos activos del sistema
    $stmt = $pdo->prepare("
        SELECT id_equipo, alias, estado, borrado
        FROM equipos 
        WHERE estado = 'habilitado' AND borrado = 0
        ORDER BY alias ASC
    ");
    $stmt->execute();
    $equipos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // DEBUG: Agregar información adicional
    echo json_encode([
        'success' => true, 
        'equipos' => $equipos,
        'debug' => [
            'total_equipos_db' => $totalEquipos,
            'equipos_habilitados' => count($equipos),
            'usuario_rol' => $usuarioActual['rol'] ?? 'No definido',
            'usuario_nombre' => $usuarioActual['nombre'] ?? 'No definido'
        ]
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Error: ' . $e->getMessage(), 'debug' => $e->getTraceAsString()]);
}