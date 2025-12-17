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

// Obtener ID de la institución desde la sesión
$idInstitucion = $_SESSION['usuario']['id_entidad'] ?? null;

if (!$idInstitucion) {
    echo json_encode(['success' => false, 'message' => 'Institución no identificada.']);
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT id_empleado, nombre, apellido
        FROM empleados
        WHERE id_entidad = :id AND borrado = 0
    ");
    $stmt->execute([':id' => $idInstitucion]);
    $empleados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'empleados' => $empleados]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al obtener empleados.',
        'debug'   => $e->getMessage()
    ]);
}
