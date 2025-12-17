<?php
require_once '../../../config/database.php';
require_once "../../../config/session_config.php";

if (!verificarUsuarioAutenticado()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Usuario no autenticado']);
    exit;
}

header('Content-Type: application/json');

if (!isset($_POST['id_solicitud'])) {
    echo json_encode(['success' => false, 'error' => 'Solicitud inválida.']);
    exit;
}

$id_solicitud = (int) $_POST['id_solicitud'];

try {
    // Obtener datos básicos de la solicitud
    $stmt = $pdo->prepare("
        SELECT s.asunto, s.creada_en, s.estado, s.id_equipo_emisor,
               IFNULL(e.nombre, 'Sin nombre') AS nombre_emisor, 
               IFNULL(e.apellido, '') AS apellido_emisor,
               IFNULL(eq.alias, 'Sin equipo') AS equipo_emisor
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

    // Obtener destinatarios
    $stmt = $pdo->prepare("
        SELECT GROUP_CONCAT(eq.alias) as destinos
        FROM solicitudes_destinatarios sd
        LEFT JOIN equipos eq ON sd.id_equipo = eq.id_equipo
        WHERE sd.id_solicitud = ?
    ");
    $stmt->execute([$id_solicitud]);
    $destinos = $stmt->fetch();

    // Obtener mensajes (simplificado)
    $stmt = $pdo->prepare("
        SELECT 
            sm.id_solicitudes_mensaje,
            sm.mensaje,
            sm.creado_en as fecha,
            IFNULL(CONCAT(e.nombre, ' ', e.apellido), 'Usuario desconocido') AS autor,
            IFNULL(eq.alias, 'Sin equipo') AS equipo_autor
        FROM solicitudes_mensajes sm
        LEFT JOIN empleados e ON sm.id_emisor = e.id_empleado
        LEFT JOIN equipos eq ON sm.id_emisor_equipo = eq.id_equipo
        WHERE sm.id_solicitud = ? AND (sm.borrado = 0 OR sm.borrado IS NULL)
        ORDER BY sm.creado_en ASC
    ");
    $stmt->execute([$id_solicitud]);
    $mensajes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Agregar archivos a cada mensaje (simplificado)
    foreach ($mensajes as &$mensaje) {
        $mensaje['archivos'] = []; // Temporal: sin archivos para debug
        $mensaje['es_mio'] = false;
    }

    $destino = $destinos['destinos'] ? 'Para: ' . $destinos['destinos'] : 'Sin destinatarios';
    
    echo json_encode([
        'success' => true,
        'solicitud' => $solicitud,
        'destino' => $destino,
        'tipo' => 'admin-lectura',
        'chat' => array_map(fn($m) => [
            'id_solicitudes_mensaje' => $m['id_solicitudes_mensaje'],
            'mensaje' => $m['mensaje'],
            'fecha' => $m['fecha'],
            'autor' => $m['autor'] . ' (' . $m['equipo_autor'] . ')',
            'archivos' => $m['archivos'],
            'es_mio' => $m['es_mio']
        ], $mensajes),
        'debug' => [
            'id_solicitud' => $id_solicitud,
            'mensajes_encontrados' => count($mensajes),
            'destinos' => $destinos['destinos'] ?? 'ninguno'
        ]
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'error' => 'Error: ' . $e->getMessage(),
        'debug' => [
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]
    ]);
}