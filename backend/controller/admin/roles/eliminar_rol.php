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

$idRol = $_POST['id_rol'] ?? null;

if (!$idRol) {
    echo json_encode(['success' => false, 'message' => 'ID de rol no proporcionado.']);
    exit;
}

try {
    // Soft delete: marcamos el rol como "borrado"
    $stmt = $pdo->prepare("UPDATE roles SET borrado = 1 WHERE id_rol = :idRol");
    $stmt->execute([':idRol' => $idRol]);

    echo json_encode(['success' => true, 'message' => 'Rol eliminado correctamente.']);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al eliminar el rol.',
        'debug'   => $e->getMessage()
    ]);
}
