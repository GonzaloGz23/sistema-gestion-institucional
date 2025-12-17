<?php
require_once '../../../config/database.php';
// Incluir configuración de sesión y validar usuario
require_once "../../../config/session_config.php";

// Verificar autenticación
if (!verificarUsuarioAutenticado()) {
    http_response_code(401);
    echo json_encode(['error' => 'Usuario no autenticado']);
    exit;
}
header('Content-Type: application/json');

if (!isset($_SESSION['usuario']['id_equipo'])) {
    echo json_encode(['success' => false, 'error' => 'Equipo no detectado']);
    exit;
}

$id_equipo = $_SESSION['usuario']['id_equipo'];
$id_solicitud = $_POST['id_solicitud'] ?? null;

if (!$id_solicitud) {
    echo json_encode(['success' => false, 'error' => 'ID de solicitud faltante']);
    exit;
}

try {
    $fecha = date('Y-m-d H:i:s');
    $stmt = $pdo->prepare("
    INSERT INTO solicitudes_lecturas (id_solicitud, id_equipo, leido_hasta)
    VALUES (?, ?, ?)
    ON DUPLICATE KEY UPDATE leido_hasta = GREATEST(leido_hasta, VALUES(leido_hasta))
");
    $stmt->execute([$id_solicitud, $id_equipo, $fecha]);

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Error: ' . $e->getMessage()]);
}
