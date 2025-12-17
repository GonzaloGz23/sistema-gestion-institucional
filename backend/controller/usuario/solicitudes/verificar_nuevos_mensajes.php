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

if (!isset($_SESSION['usuario']['id']) || !isset($_GET['id_solicitud']) || !isset($_GET['ultimo_id'])) {
    echo json_encode(['success' => false, 'error' => 'Parámetros incompletos']);
    exit;
}

$idSolicitud = (int) $_GET['id_solicitud'];
$ultimoId = (int) $_GET['ultimo_id'];
$idUsuario = $_SESSION['usuario']['id'];
$idEquipoUsuario = $_SESSION['usuario']['id_equipo'];

try {
    $sql = "
    SELECT 
        sm.id_solicitudes_mensaje, 
        sm.mensaje, 
        sm.creado_en, 
        e.nombre, 
        e.apellido, 
        sm.id_emisor
    FROM solicitudes_mensajes sm
    JOIN empleados e ON sm.id_emisor = e.id_empleado
    WHERE sm.id_solicitud = :id_solicitud
      AND sm.id_solicitudes_mensaje > :ultimo_id
      AND sm.borrado = 0
    ORDER BY sm.creado_en ASC
    LIMIT 50
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':id_solicitud' => $idSolicitud,
        ':ultimo_id' => $ultimoId
    ]);

    $nuevosMensajes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($nuevosMensajes as &$msg) {
        $msg['autor'] = $msg['nombre'] . ' ' . $msg['apellido'];
        $msg['es_mio'] = $msg['id_emisor'] == $idUsuario;

        // Obtener archivos asociados al mensaje
        $stmtArchivos = $pdo->prepare("
            SELECT nombre_original, ruta_archivo
            FROM solicitudes_archivos
            WHERE id_solicitudes_mensaje = :id_mensaje
        ");
        $stmtArchivos->execute([':id_mensaje' => $msg['id_solicitudes_mensaje']]);
        $msg['archivos'] = $stmtArchivos->fetchAll(PDO::FETCH_ASSOC);
    }
    unset($msg);

    echo json_encode(['success' => true, 'nuevos' => $nuevosMensajes]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Error: ' . $e->getMessage()]);
}
