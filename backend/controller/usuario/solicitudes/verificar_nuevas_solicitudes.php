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

if (!isset($_SESSION['usuario']['id']) || !isset($_SESSION['usuario']['id_equipo'])) {
    echo json_encode(['success' => false, 'error' => 'Sesión no iniciada']);
    exit;
}

$idEquipoUsuario = $_SESSION['usuario']['id_equipo'];
$idUsuario = $_SESSION['usuario']['id'];

try {
    $sql = "
    SELECT s.id_solicitud, COUNT(sm.id_solicitudes_mensaje) AS cantidad_mensajes_nuevos
    FROM solicitudes s
    LEFT JOIN solicitudes_mensajes sm ON s.id_solicitud = sm.id_solicitud
    LEFT JOIN solicitudes_lecturas sl ON s.id_solicitud = sl.id_solicitud AND sl.id_equipo = :id_equipo_1
    WHERE (
        (s.id_equipo_emisor = :id_equipo_2 OR EXISTS (
            SELECT 1 FROM solicitudes_destinatarios sd WHERE sd.id_solicitud = s.id_solicitud AND sd.id_equipo = :id_equipo_3
        ))
        AND sm.creado_en > IFNULL(sl.leido_hasta, '1970-01-01 00:00:00')
        AND sm.id_emisor_equipo != :id_equipo_4
        AND sm.borrado = 0
    )
    GROUP BY s.id_solicitud
    HAVING cantidad_mensajes_nuevos > 0
    LIMIT 50
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':id_equipo_1' => $idEquipoUsuario,
        ':id_equipo_2' => $idEquipoUsuario,
        ':id_equipo_3' => $idEquipoUsuario,
        ':id_equipo_4' => $idEquipoUsuario
    ]);

    $nuevasSolicitudes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'nuevas' => $nuevasSolicitudes]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Error: ' . $e->getMessage()]);
}
