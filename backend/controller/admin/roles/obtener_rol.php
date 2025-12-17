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

// Validar parámetro obligatorio
$idRol = $_GET['id'] ?? null;
if (!$idRol) {
    echo json_encode(['success' => false, 'message' => 'Rol no especificado']);
    exit;
}

try {
    // Obtener información del rol
    $stmt = $pdo->prepare("SELECT * FROM roles WHERE id_rol = :id");
    $stmt->execute([':id' => $idRol]);
    $rol = $stmt->fetch();

    if (!$rol) {
        echo json_encode(['success' => false, 'message' => 'Rol no encontrado']);
        exit;
    }

    // Obtener módulos asignados
    $stmt = $pdo->prepare("SELECT id_modulo FROM roles_modulos WHERE id_rol = :id");
    $stmt->execute([':id' => $idRol]);
    $modulosAsignados = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Obtener todos los módulos activos
    $modulos = $pdo->query("SELECT id_modulo, nombre, perfil,orden FROM modulos WHERE activo = 1 ORDER BY perfil ASC, orden ASC")->fetchAll();

    echo json_encode([
        'success' => true,
        'rol' => array_merge($rol, ['modulos' => $modulosAsignados]),
        'modulos' => $modulos
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al obtener datos del rol.',
        'debug' => $e->getMessage()
    ]);
}
