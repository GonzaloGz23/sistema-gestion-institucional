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

// Obtener parámetro de equipo
$equipoFiltro = $_GET['equipo'] ?? null;

if (!$equipoFiltro) {
    echo json_encode(['success' => false, 'error' => 'Debe seleccionar un equipo']);
    exit;
}

try {
    $sql = "
    SELECT 
    s.id_solicitud,
    s.id_emisor,
    s.id_equipo_emisor,
    s.asunto,
    s.estado,
    DATE_FORMAT(s.creada_en, '%d/%m/%Y %H:%i') AS fecha_creacion,
    e.alias AS equipo_emisor,
    emp.nombre AS nombre_emisor,
    emp.apellido AS apellido_emisor,
    GROUP_CONCAT(DISTINCT eq.alias) AS equipos_destino,

    (
        SELECT MAX(sm.creado_en)
        FROM solicitudes_mensajes sm
        WHERE sm.id_solicitud = s.id_solicitud
          AND sm.borrado = 0
    ) AS ultima_respuesta,

    (
        SELECT COUNT(*)
        FROM solicitudes_mensajes sm
        WHERE sm.id_solicitud = s.id_solicitud
          AND sm.borrado = 0
    ) AS total_mensajes,

    CASE
        WHEN s.id_equipo_emisor = :equipo_filtro THEN 'emisor'
        ELSE 'receptor'
    END AS rol_equipo,

    CASE
        WHEN s.id_equipo_emisor = :equipo_filtro_2 THEN 'enviadas'
        ELSE 'recibidas'
    END AS tipo
    
    FROM solicitudes s
    LEFT JOIN empleados emp ON s.id_emisor = emp.id_empleado
    LEFT JOIN equipos e ON s.id_equipo_emisor = e.id_equipo
    LEFT JOIN solicitudes_destinatarios sd ON s.id_solicitud = sd.id_solicitud
    LEFT JOIN equipos eq ON sd.id_equipo = eq.id_equipo
    
    WHERE (
        (sd.id_equipo = :equipo_filtro_3 AND (s.borrado_receptor = 0 OR s.borrado_receptor IS NULL))
        OR (s.id_equipo_emisor = :equipo_filtro_4 AND (s.borrado_emisor = 0 OR s.borrado_emisor IS NULL))
    )
    
    GROUP BY s.id_solicitud
    ORDER BY s.creada_en DESC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':equipo_filtro' => $equipoFiltro,
        ':equipo_filtro_2' => $equipoFiltro,
        ':equipo_filtro_3' => $equipoFiltro,
        ':equipo_filtro_4' => $equipoFiltro
    ]);

    $solicitudes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Formatear las solicitudes para el frontend
    foreach ($solicitudes as &$s) {
        // Agregar cantidad de mensajes como indicador visual
        $s['cantidad_mensajes_nuevos'] = 0; // En modo admin no hay mensajes "nuevos"
        
        // Formatear fecha de última respuesta
        if ($s['ultima_respuesta']) {
            $fecha = new DateTime($s['ultima_respuesta']);
            $s['ultima_respuesta_formateada'] = $fecha->format('d/m/Y H:i');
        }
    }

    echo json_encode(['success' => true, 'solicitudes' => $solicitudes]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Error: ' . $e->getMessage()]);
}