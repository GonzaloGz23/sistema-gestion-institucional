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
$id_equipo = $_SESSION['usuario']['id_equipo'] ?? null;

if (!$id_solicitud || !$id_equipo) {
    echo json_encode(['success' => false, 'error' => 'Parámetros inválidos.']);
    exit;
}

try {
    // Obtener estado y equipo emisor
    $stmt = $pdo->prepare("
        SELECT estado, id_equipo_emisor
        FROM solicitudes
        WHERE id_solicitud = ?
    ");
    $stmt->execute([$id_solicitud]);
    $solicitud = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$solicitud) {
        echo json_encode(['success' => false, 'error' => 'Solicitud no encontrada.']);
        exit;
    }

    // Solo permitir eliminar si no está en estado pendiente
    if ($solicitud['estado'] === 'pendiente') {
        echo json_encode(['success' => false, 'error' => 'Solo se pueden eliminar solicitudes resueltas o rechazadas.']);
        exit;
    }

    // Verificar si el equipo actual es receptor
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM solicitudes_destinatarios
        WHERE id_solicitud = ? AND id_equipo = ?
    ");
    $stmt->execute([$id_solicitud, $id_equipo]);
    $esReceptor = $stmt->fetchColumn() > 0;

    // Determinar campo a actualizar
    if ((int)$solicitud['id_equipo_emisor'] === (int)$id_equipo) {
        $campo = 'borrado_emisor';
    } elseif ($esReceptor) {
        $campo = 'borrado_receptor';
    } else {
        echo json_encode(['success' => false, 'error' => 'No tenés permiso para eliminar esta solicitud.']);
        exit;
    }

    // Actualizar campo correspondiente
    $stmt = $pdo->prepare("UPDATE solicitudes SET $campo = 1 WHERE id_solicitud = ?");
    $ok = $stmt->execute([$id_solicitud]);

    echo json_encode(['success' => $ok]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Error en base de datos.']);
}
