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

$id_solicitud = $_POST['id_solicitud'] ?? null;
$estado = $_POST['estado'] ?? null;

$estadosValidos = ['pendiente', 'resuelta', 'rechazada'];

if (!$id_solicitud || !in_array($estado, $estadosValidos)) {
    echo json_encode(['success' => false, 'error' => 'Parámetros inválidos']);
    exit;
}

$stmt = $pdo->prepare("UPDATE solicitudes SET estado = ? WHERE id_solicitud = ?");
$ok = $stmt->execute([$estado, $id_solicitud]);

echo json_encode(['success' => $ok]);
