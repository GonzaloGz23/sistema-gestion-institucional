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

$id_mensaje = $_POST['id_mensaje'] ?? null;
$id_usuario = $_SESSION['usuario']['id'] ?? null;

if (!$id_mensaje || !$id_usuario) {
    echo json_encode([
        'success' => false,
        'error' => 'Parámetros incompletos.',
        'debug' => [
            'id_usuario' => $id_usuario,
            'id_mensaje' => $id_mensaje
        ]
    ]);
    exit;
}

// Validar que el mensaje le pertenezca al usuario
$stmt = $pdo->prepare("SELECT id_emisor FROM solicitudes_mensajes WHERE id_solicitudes_mensaje = ? AND borrado = 0");
$stmt->execute([$id_mensaje]);
$row = $stmt->fetch();

if (!$row) {
    echo json_encode([
        'success' => false,
        'error' => 'No se encontró el mensaje.',
        'debug' => [
            'id_usuario' => $id_usuario,
            'id_mensaje' => $id_mensaje,
            'row' => null
        ]
    ]);
    exit;
}

if ($row['id_emisor'] != $id_usuario) {
    echo json_encode([
        'success' => false,
        'error' => 'No autorizado para eliminar este mensaje.',
        'debug' => [
            'id_usuario' => $id_usuario,
            'id_mensaje' => $id_mensaje,
            'id_emisor' => $row['id_emisor']
        ]
    ]);
    exit;
}

// Soft delete
$stmt = $pdo->prepare("UPDATE solicitudes_mensajes SET borrado = 1 WHERE id_solicitudes_mensaje = ?");
$ok = $stmt->execute([$id_mensaje]);

echo json_encode([
    'success' => $ok,
    'id_mensaje' => $id_mensaje
]);
