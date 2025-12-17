<?php
// Incluir configuración de sesión y validar usuario
require_once '../../../config/session_config.php';
require_once '../../../config/database.php';

// Verificar autenticación
if (!verificarUsuarioAutenticado()) {
    http_response_code(401);
    echo json_encode(['error' => 'Usuario no autenticado']);
    exit;
}

header('Content-Type: application/json');

// Validar entidad desde función helper
$usuarioActual = obtenerUsuarioActual();
$idInstitucion = $usuarioActual['id_entidad'];

if (!$idInstitucion) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Sesión inválida']);
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT
            r.id_rol,
            r.alias,
            r.descripcion,
            GROUP_CONCAT(m.nombre SEPARATOR ', ') AS modulos
        FROM roles r
        LEFT JOIN roles_modulos rm ON r.id_rol = rm.id_rol
        LEFT JOIN modulos m ON rm.id_modulo = m.id_modulo
        WHERE r.id_entidad = :id
         AND r.borrado = 0
        GROUP BY r.id_rol
    ");
    $stmt->execute([':id' => $idInstitucion]);
    $roles = $stmt->fetchAll();

    echo json_encode(['success' => true, 'roles' => $roles]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al obtener los roles',
        'debug' => $e->getMessage()
    ]);
}
