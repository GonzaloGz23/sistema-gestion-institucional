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

if (!isset($_POST['id_solicitud'])) {
    echo json_encode(['success' => false, 'error' => 'Solicitud inválida.']);
    exit;
}

$id_solicitud = (int) $_POST['id_solicitud'];
$idEquipoUsuario = $_SESSION['usuario']['id_equipo'] ?? null;
$idUsuario = $_SESSION['usuario']['id'] ?? null;

// Obtener datos principales de la solicitud
$stmt = $pdo->prepare("
    SELECT s.asunto, s.creada_en, s.estado,
           s.id_equipo_emisor,
           e.nombre AS nombre_emisor, e.apellido AS apellido_emisor,
           eq.alias AS equipo_emisor
    FROM solicitudes s
    LEFT JOIN empleados e ON s.id_emisor = e.id_empleado
    LEFT JOIN equipos eq ON s.id_equipo_emisor = eq.id_equipo
    WHERE s.id_solicitud = ?
");
$stmt->execute([$id_solicitud]);
$solicitud = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$solicitud) {
    echo json_encode(['success' => false, 'error' => 'Solicitud no encontrada.']);
    exit;
}

$tipo = ($solicitud['id_equipo_emisor'] == $idEquipoUsuario) ? 'enviadas' : 'recibidas';
$nombreOrigen = $solicitud['nombre_emisor'] . ' ' . $solicitud['apellido_emisor'];

// Equipos destino
$stmtDestinos = $pdo->prepare("
    SELECT eq.alias
    FROM solicitudes_destinatarios sd
    JOIN equipos eq ON sd.id_equipo = eq.id_equipo
    WHERE sd.id_solicitud = ?
");
$stmtDestinos->execute([$id_solicitud]);
$equiposDestino = $stmtDestinos->fetchAll(PDO::FETCH_COLUMN);

$destino = ($tipo === 'recibidas')
    ? "De: $nombreOrigen (" . ($solicitud['equipo_emisor'] ?? 'Sin equipo') . ")"
    : "A: " . implode(', ', $equiposDestino ?: ['Sin destinatario']);

// Mensajes
$stmtChat = $pdo->prepare("
    SELECT sm.id_solicitudes_mensaje, sm.mensaje, sm.creado_en, e.nombre, e.apellido, sm.id_emisor
    FROM solicitudes_mensajes sm
    JOIN empleados e ON sm.id_emisor = e.id_empleado
    WHERE sm.id_solicitud = ? AND sm.borrado = 0
    ORDER BY sm.creado_en ASC
");
$stmtChat->execute([$id_solicitud]);
$mensajes = $stmtChat->fetchAll(PDO::FETCH_ASSOC);

foreach ($mensajes as &$m) {
    // Archivos asociados al mensaje
    $stmtArch = $pdo->prepare("
        SELECT nombre_original, ruta_archivo
        FROM solicitudes_archivos
        WHERE id_solicitudes_mensaje = ?
    ");
    $stmtArch->execute([$m['id_solicitudes_mensaje']]);
    $archivosMensaje = $stmtArch->fetchAll(PDO::FETCH_ASSOC);

    $m['archivos'] = array_map(fn($a) => [
        'nombre' => $a['nombre_original'],
        'ruta' => $a['ruta_archivo']
    ], $archivosMensaje);

    $m['autor'] = $m['nombre'] . ' ' . $m['apellido'];
    $m['fecha'] = $m['creado_en'];
    $m['es_mio'] = $m['id_emisor'] == $idUsuario;
}
unset($m);

// Respuesta final
echo json_encode([
    'success' => true,
    'fecha' => $solicitud['creada_en'],
    'asunto' => $solicitud['asunto'],
    'estado' => $solicitud['estado'],
    'autor' => $nombreOrigen,
    'destino' => $destino,
    'tipo' => $tipo,
    'chat' => array_map(fn($m) => [
        'id_mensaje' => $m['id_solicitudes_mensaje'],
        'mensaje' => $m['mensaje'],
        'fecha' => $m['fecha'],
        'autor' => $m['autor'],
        'archivos' => $m['archivos'],
        'es_mio' => $m['es_mio']
    ], $mensajes)
]);
