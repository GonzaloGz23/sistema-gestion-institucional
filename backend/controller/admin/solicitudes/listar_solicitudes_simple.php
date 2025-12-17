<?php
require_once '../../../config/database.php';
require_once "../../../config/session_config.php";

if (!verificarUsuarioAutenticado()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Usuario no autenticado']);
    exit;
}

header('Content-Type: application/json');

$equipoFiltro = $_GET['equipo'] ?? null;

if (!$equipoFiltro) {
    echo json_encode(['success' => false, 'error' => 'Debe seleccionar un equipo']);
    exit;
}

try {
    // Query SIMPLIFICADA para debug
    $sql = "
    SELECT 
        s.id_solicitud,
        s.asunto,
        s.estado,
        s.id_equipo_emisor,
        DATE_FORMAT(s.creada_en, '%d/%m/%Y %H:%i') AS fecha_creacion,
        IFNULL(emp.nombre, 'Sin nombre') AS nombre_emisor,
        IFNULL(emp.apellido, '') AS apellido_emisor,
        IFNULL(e.alias, 'Sin equipo') AS equipo_emisor,
        
        CASE
            WHEN s.id_equipo_emisor = :equipo_filtro THEN 'enviadas'
            ELSE 'recibidas'
        END AS tipo
    
    FROM solicitudes s
    LEFT JOIN empleados emp ON s.id_emisor = emp.id_empleado
    LEFT JOIN equipos e ON s.id_equipo_emisor = e.id_equipo
    LEFT JOIN solicitudes_destinatarios sd ON s.id_solicitud = sd.id_solicitud
    
    WHERE (
        sd.id_equipo = :equipo_filtro_2
        OR s.id_equipo_emisor = :equipo_filtro_3
    )
    
    GROUP BY s.id_solicitud
    ORDER BY s.creada_en DESC
    LIMIT 20
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':equipo_filtro' => $equipoFiltro,
        ':equipo_filtro_2' => $equipoFiltro,
        ':equipo_filtro_3' => $equipoFiltro
    ]);

    $solicitudes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Agregar campos adicionales para compatibilidad
    foreach ($solicitudes as &$s) {
        $s['cantidad_mensajes_nuevos'] = 0;
        $s['total_mensajes'] = 0;
        $s['equipos_destino'] = 'Cargando...';
        
        // Obtener destinatarios
        $stmtDest = $pdo->prepare("
            SELECT GROUP_CONCAT(eq.alias) as destinos
            FROM solicitudes_destinatarios sd
            LEFT JOIN equipos eq ON sd.id_equipo = eq.id_equipo
            WHERE sd.id_solicitud = ?
        ");
        $stmtDest->execute([$s['id_solicitud']]);
        $destinos = $stmtDest->fetch();
        $s['equipos_destino'] = $destinos['destinos'] ?? 'Sin destinatarios';
    }

    echo json_encode([
        'success' => true, 
        'solicitudes' => $solicitudes,
        'debug' => [
            'equipo_filtro' => $equipoFiltro,
            'total_encontradas' => count($solicitudes),
            'query_usado' => 'simplificado'
        ]
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'error' => 'Error: ' . $e->getMessage(),
        'debug' => [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]
    ]);
}