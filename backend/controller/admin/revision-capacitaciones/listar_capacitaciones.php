<?php
/**
 * Controlador para listar capacitaciones del módulo de revisión
 * Obtiene capacitaciones de la BD de cursos con información del equipo de la BD principal
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

try {
    // Incluir configuraciones
    require_once '../../../config/database.php';
    require_once '../../../config/database_courses.php';
    require_once '../../../config/session_config.php';
    
    // Verificar autenticación
    if (!verificarUsuarioAutenticado()) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'error' => 'Usuario no autenticado'
        ]);
        exit;
    }
    
    // Obtener usuario actual
    $usuarioActual = obtenerUsuarioActual();
    $idEntidad = $usuarioActual['id_entidad'];
    
    if (!$idEntidad) {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'error' => 'Usuario sin entidad asignada'
        ]);
        exit;
    }
    
    // Usar conexiones directas (ya configuradas en los archivos)
    // $pdo viene de database.php (BD principal)
    // $pdoCourses viene de database_courses.php (BD cursos)
    
    // Query para obtener capacitaciones con información del equipo
    // Primero obtenemos los equipos de la entidad del usuario desde BD principal
    $equiposQuery = "
        SELECT e.id_equipo, e.alias as nombre_equipo, a.alias as nombre_area
        FROM equipos e
        INNER JOIN areas a ON e.id_area = a.id_area  
        WHERE a.id_entidad = :id_entidad 
        AND e.estado = 'habilitado' 
        AND e.borrado = 0
    ";
    
    $stmtEquipos = $pdo->prepare($equiposQuery);
    $stmtEquipos->bindParam(':id_entidad', $idEntidad, PDO::PARAM_INT);
    $stmtEquipos->execute();
    $equipos = $stmtEquipos->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($equipos)) {
        echo json_encode([
            'success' => true,
            'capacitaciones' => [],
            'message' => 'No hay equipos disponibles para esta entidad'
        ]);
        exit;
    }
    
    // Extraer IDs de equipos
    $equipoIds = array_column($equipos, 'id_equipo');
    $equiposMap = array_column($equipos, 'nombre_equipo', 'id_equipo');
    
    // Query para obtener capacitaciones desde BD de cursos
    $placeholders = str_repeat('?,', count($equipoIds) - 1) . '?';
    $estados_str="";
     $condicionEquipo='';
     $equipoidActual=$usuarioActual["id_equipo"];
   
     
    if($usuarioActual["id_equipo"]==10 || $usuarioActual["id_equipo"]==11){
        $estados_str="2,3,4,5";

    }else{
        $estados_str="1,2,3,4,5";
         $condicionEquipo='and  c.equipo_id = '. $equipoidActual;
        
    }
    
   
    $capacitacionesQuery = "
        SELECT 
            c.id,
            c.equipo_id,
            c.nombre,
            c.fecha_inicio_cursada as fecha_inicio,
            c.fecha_inicio_inscripcion as fecha_creacion,
            e.nombre as estado,
            c.alcance,
            c.tipo_capacitacion,
            c.slogan,
            c.objetivo,
            c.que_aprenderas as descripcion,
            c.destinatarios,
            c.requisitos,
            c.fecha_fin_cursada as fecha_fin,
            c.duracion_clase_minutos as duracion_clase,
            c.total_encuentros as cantidad_encuentros,
            c.cupos_maximos as cupos,
            c.ruta_imagen as imagen,
            c.link,
            c.esta_publicada,
            ".$usuarioActual['id_equipo']." as EquipoLogued
        FROM capacitaciones c
        INNER JOIN estados_capacitacion e ON c.estado_id = e.id
        WHERE c.equipo_id IN ($placeholders)
        AND c.esta_eliminada = 0
        AND c.estado_id in($estados_str) $condicionEquipo
        ORDER BY c.fecha_inicio_inscripcion DESC
    ";
    
    $stmtCapacitaciones = $pdoCourses->prepare($capacitacionesQuery);
    $stmtCapacitaciones->execute($equipoIds);
    $capacitaciones = $stmtCapacitaciones->fetchAll(PDO::FETCH_ASSOC);
    
    // Procesar resultados y agregar información del equipo
    $capacitacionesProcesadas = [];
    
    foreach ($capacitaciones as $capacitacion) {
        // Mapear estados de BD a formato del frontend
        $estadoMapeado = mapearEstado($capacitacion['estado']);
        
        // Agregar nombre del equipo
        $nombreEquipo = $equiposMap[$capacitacion['equipo_id']] ?? 'Equipo desconocido';
        
        // Procesar imagen
        $imagenUrl = !empty($capacitacion['imagen']) 
            ? '/sistemaInstitucional/assets/images/cursos/' . $capacitacion['imagen']
            : 'https://via.placeholder.com/300x200?text=Sin+Imagen';
        
        $capacitacionesProcesadas[] = [
            'id' => (int)$capacitacion['id'],
            'nombre' => $capacitacion['nombre'],
            'equipo' => $nombreEquipo,
            'fecha_inicio' => $capacitacion['fecha_inicio'],
            'estado' => $estadoMapeado,
            'fecha_creacion' => $capacitacion['fecha_creacion'],
            // Datos adicionales para el modal (lo completaremos después)
            'alcance' => $capacitacion['alcance'],
            'slogan' => $capacitacion['slogan'],
            'objetivo' => $capacitacion['objetivo'], 
            'descripcion' => $capacitacion['descripcion'],
            'destinatarios' => $capacitacion['destinatarios'],
            'requisitos' => $capacitacion['requisitos'],
            'fecha_fin' => $capacitacion['fecha_fin'],
            'duracion_clase' => (int)$capacitacion['duracion_clase'],
            'cantidad_encuentros' => (int)$capacitacion['cantidad_encuentros'],
            'cupos' => (int)$capacitacion['cupos'],
            'link' => $capacitacion['link'],
            'imagen' => $imagenUrl,
            "EquipoLogued"=>$capacitacion["EquipoLogued"],
            "esta_publicada"=>$capacitacion["esta_publicada"]
        ];
    }
    
    // Respuesta exitosa
    echo json_encode([
        'success' => true,
        'capacitaciones' => $capacitacionesProcesadas,
        'total' => count($capacitacionesProcesadas),
        'entidad_id' => $idEntidad
    ]);
    
} catch (Exception $e) {
    error_log("Error en listar_capacitaciones.php: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Error interno del servidor',
        'debug' => $e->getMessage() // Solo para desarrollo, quitar en producción
    ]);
}

/**
 * Mapea estados de la BD al formato del frontend
 */
function mapearEstado($estadoBD) {
    $mapeo = [
        'borrador' => 'borrador',
        'en espera' => 'en_espera', 
        'en revisión' => 'en_revision',
        'aprobado' => 'aprobado',
        'cerrado' => 'cerrado'
    ];
    
    return $mapeo[$estadoBD] ?? 'desconocido';
}
?>
