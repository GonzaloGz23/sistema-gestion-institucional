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
SELECT 
    s.id_solicitud,
    s.id_emisor,
    s.id_equipo_emisor,
    s.asunto,
    s.estado,
    s.privado,  -- ⭐ NUEVO: Incluir el campo privado
    DATE_FORMAT(s.creada_en, '%d/%m/%Y %H:%i') AS fecha_creacion,
    e.alias AS equipo_emisor,
    emp.nombre AS nombre_emisor,
    emp.apellido AS apellido_emisor,
    GROUP_CONCAT(DISTINCT eq.alias) AS equipos_destino,

    (
        SELECT MAX(sm.creado_en)
        FROM solicitudes_mensajes sm
        WHERE sm.id_solicitud = s.id_solicitud
          AND sm.id_emisor_equipo != :id_equipo_1
          AND sm.borrado = 0
    ) AS ultima_respuesta_ajena,

    (
        SELECT sl.leido_hasta
        FROM solicitudes_lecturas sl
        WHERE sl.id_solicitud = s.id_solicitud
          AND sl.id_equipo = :id_equipo_2
    ) AS ultima_lectura,

    (
        SELECT COUNT(*)
        FROM solicitudes_mensajes sm
        WHERE sm.id_solicitud = s.id_solicitud
          AND sm.id_emisor_equipo != :id_equipo_7
          AND sm.borrado = 0
          AND sm.creado_en > IFNULL((
              SELECT sl.leido_hasta
              FROM solicitudes_lecturas sl
              WHERE sl.id_solicitud = s.id_solicitud AND sl.id_equipo = :id_equipo_8
          ), '1970-01-01 00:00:00')
    ) AS cantidad_mensajes_nuevos,

    CASE
        WHEN s.id_equipo_emisor = :id_equipo_3 THEN 'emisor'
        ELSE 'receptor'
    END AS rol_equipo,

    CASE
        WHEN s.id_equipo_emisor = :id_equipo_4 THEN 'enviadas'
        ELSE 'recibidas'
    END AS tipo
    
FROM solicitudes s
LEFT JOIN empleados emp ON s.id_emisor = emp.id_empleado
LEFT JOIN equipos e ON s.id_equipo_emisor = e.id_equipo
LEFT JOIN solicitudes_destinatarios sd ON s.id_solicitud = sd.id_solicitud
LEFT JOIN equipos eq ON sd.id_equipo = eq.id_equipo

-- ⭐ ZONA MODIFICADA: WHERE con lógica de privacidad
WHERE (
    -- CASO 1: Solicitudes RECIBIDAS por el equipo del usuario
    -- (siempre visibles, sean privadas o no)
    (sd.id_equipo = :id_equipo_5 AND s.borrado_receptor = 0)
    
    OR
    
    -- CASO 2: Solicitudes ENVIADAS desde el equipo del usuario
    (
        s.id_equipo_emisor = :id_equipo_6 
        AND s.borrado_emisor = 0
        AND (
            -- Subcaso 2a: Solicitud NO privada → visible para todo el equipo
            s.privado = 0
            
            OR
            
            -- Subcaso 2b: Solicitud privada → solo visible para el emisor original
            (s.privado = 1 AND s.id_emisor = :id_empleado)
        )
    )
)

GROUP BY s.id_solicitud
ORDER BY
  
    (cantidad_mensajes_nuevos > 0) DESC,

    s.creada_en DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute([
    ':id_equipo_1' => $idEquipoUsuario,
    ':id_equipo_2' => $idEquipoUsuario,
    ':id_equipo_3' => $idEquipoUsuario,
    ':id_equipo_4' => $idEquipoUsuario,
    ':id_equipo_5' => $idEquipoUsuario,
    ':id_equipo_6' => $idEquipoUsuario,
    ':id_equipo_7' => $idEquipoUsuario,
    ':id_equipo_8' => $idEquipoUsuario,
    ':id_empleado' => $idUsuario  // ⭐ NUEVO PARÁMETRO: ID del empleado actual
]);


    $solicitudes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($solicitudes as &$s) {
        unset($s['ultima_lectura'], $s['ultima_respuesta_ajena']);
    }

    echo json_encode(['success' => true, 'solicitudes' => $solicitudes]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Error: ' . $e->getMessage()]);
}
