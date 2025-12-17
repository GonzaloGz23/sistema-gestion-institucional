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

// Validar que sea admin
$usuarioActual = obtenerUsuarioActual();
if (!$usuarioActual || !isset($usuarioActual['rol']) || $usuarioActual['rol'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Acceso no autorizado. Se requieren permisos de administrador.']);
    exit;
}

if (!isset($_POST['id_solicitud'])) {
    echo json_encode(['success' => false, 'error' => 'Solicitud inválida.']);
    exit;
}

$id_solicitud = (int) $_POST['id_solicitud'];

try {
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

    // Obtener destinatarios
    $stmt = $pdo->prepare("
        SELECT eq.alias
        FROM solicitudes_destinatarios sd
        LEFT JOIN equipos eq ON sd.id_equipo = eq.id_equipo
        WHERE sd.id_solicitud = ?
    ");
    $stmt->execute([$id_solicitud]);
    $destinatarios = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Obtener mensajes del chat (incluyendo mensaje inicial)
    $stmt = $pdo->prepare("
        SELECT 
            sm.id_solicitudes_mensaje,
            sm.mensaje,
            sm.creado_en as fecha,
            CONCAT(e.nombre, ' ', e.apellido) AS autor,
            eq.alias AS equipo_autor,
            sm.id_emisor_equipo
        FROM solicitudes_mensajes sm
        LEFT JOIN empleados e ON sm.id_emisor = e.id_empleado
        LEFT JOIN equipos eq ON sm.id_emisor_equipo = eq.id_equipo
        WHERE sm.id_solicitud = ? AND sm.borrado = 0
        ORDER BY sm.creado_en ASC
    ");
    $stmt->execute([$id_solicitud]);
    $mensajes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Obtener archivos para cada mensaje
    foreach ($mensajes as &$mensaje) {
        $stmtArchivos = $pdo->prepare("
            SELECT nombre_original, ruta_archivo
            FROM solicitudes_archivos
            WHERE id_solicitudes_mensaje = ?
        ");
        $stmtArchivos->execute([$mensaje['id_solicitudes_mensaje']]);
        $archivos = $stmtArchivos->fetchAll(PDO::FETCH_ASSOC);

        $mensaje['archivos'] = array_map(fn($a) => [
            'nombre' => $a['nombre_original'],
            'ruta' => $a['ruta_archivo']
        ], $archivos);

        $mensaje['es_mio'] = false; // En modo admin, ningún mensaje es "mío"
    }
    unset($mensaje);

    // Formatear datos para el frontend
    $destino = count($destinatarios) > 0 
        ? 'Para: ' . implode(', ', $destinatarios)
        : 'Sin destinatarios';

    $tipo = 'lectura'; // Tipo especial para modo admin

    echo json_encode([
        'success' => true,
        'solicitud' => $solicitud,
        'destino' => $destino,
        'tipo' => $tipo,
        'chat' => array_map(fn($m) => [
            'id_solicitudes_mensaje' => $m['id_solicitudes_mensaje'],
            'mensaje' => $m['mensaje'],
            'fecha' => $m['fecha'],
            'autor' => $m['autor'] . ' (' . ($m['equipo_autor'] ?: 'Sin equipo') . ')',
            'archivos' => $m['archivos'],
            'es_mio' => $m['es_mio']
        ], $mensajes)
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Error: ' . $e->getMessage()]);
}