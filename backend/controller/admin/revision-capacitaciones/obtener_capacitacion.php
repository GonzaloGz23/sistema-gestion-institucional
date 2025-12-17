<?php
/**
 * Controlador para obtener detalles completos de una capacitación
 * Incluye datos complejos: categorización, horarios, temas, etc.
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
    
    // Verificar método HTTP
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        http_response_code(405);
        echo json_encode([
            'success' => false,
            'error' => 'Método no permitido'
        ]);
        exit;
    }
    
    // Obtener y validar ID de capacitación
    $capacitacionId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    
    if (!$capacitacionId || $capacitacionId <= 0) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'ID de capacitación inválido'
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
    
    // ==========================================
    // 1. OBTENER DATOS BÁSICOS DE LA CAPACITACIÓN
    // ==========================================
   
    
    $capacitacionQuery = "
        SELECT 
            c.*,
            e.nombre as estado_nombre
        FROM capacitaciones c
        INNER JOIN estados_capacitacion e ON c.estado_id = e.id
        WHERE c.id = :capacitacion_id 
        AND c.esta_eliminada = 0  
    ";
    
    $stmtCapacitacion = $pdoCourses->prepare($capacitacionQuery);
    $stmtCapacitacion->bindParam(':capacitacion_id', $capacitacionId, PDO::PARAM_INT);
    $stmtCapacitacion->execute();
    $capacitacion = $stmtCapacitacion->fetch(PDO::FETCH_ASSOC);
    
    if (!$capacitacion) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'error' => 'Capacitación no encontrada'
        ]);
        exit;
    }
    
    // Obtener información del equipo desde la BD principal
    $equipoQuery = "
        SELECT eq.alias as nombre_equipo
        FROM equipos eq
        WHERE eq.id_equipo = :equipo_id
    ";
    
    $stmtEquipo = $pdo->prepare($equipoQuery);
    $stmtEquipo->bindParam(':equipo_id', $capacitacion['equipo_id'], PDO::PARAM_INT);
    $stmtEquipo->execute();
    $equipo = $stmtEquipo->fetch(PDO::FETCH_ASSOC);
    
    $nombreEquipo = $equipo['nombre_equipo'] ?? 'Equipo desconocido';
    
    // Verificar que el equipo pertenezca a la entidad del usuario
    $verificarEntidadQuery = "
        SELECT COUNT(*) as count
        FROM equipos eq
        INNER JOIN areas a ON eq.id_area = a.id_area
        WHERE eq.id_equipo = :equipo_id 
        AND a.id_entidad = :entidad_id
        AND eq.estado = 'habilitado' 
        AND eq.borrado = 0
    ";
    
    $stmtVerificar = $pdo->prepare($verificarEntidadQuery);
    $stmtVerificar->bindParam(':equipo_id', $capacitacion['equipo_id'], PDO::PARAM_INT);
    $stmtVerificar->bindParam(':entidad_id', $idEntidad, PDO::PARAM_INT);
    $stmtVerificar->execute();
    $verificacion = $stmtVerificar->fetch(PDO::FETCH_ASSOC);
    
    if ($verificacion['count'] == 0) {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'error' => 'No tiene permisos para acceder a esta capacitación'
        ]);
        exit;
    }
    
    // ==========================================
    // 2. OBTENER CATEGORIZACIÓN COMPLETA
    // ==========================================
    
    $categoriaCompleta = obtenerCategoriaCompleta($capacitacion, $pdoCourses);
    
    // ==========================================
    // 3. OBTENER HORARIOS (CRONOGRAMA)
    // ==========================================
    
    $horariosQuery = "
        SELECT 
            cr.id,
            cr.dia_id,
            cr.hora_inicio,
            cr.hora_fin,
            d.nombre as dia_nombre,
            d.nombre_corto as dia_corto
        FROM cronogramas cr
        INNER JOIN dias d ON cr.dia_id = d.id
        WHERE cr.capacitacion_id = :capacitacion_id
        ORDER BY cr.dia_id, cr.hora_inicio
    ";
    
    $stmtHorarios = $pdoCourses->prepare($horariosQuery);
    $stmtHorarios->bindParam(':capacitacion_id', $capacitacionId, PDO::PARAM_INT);
    $stmtHorarios->execute();
    $horarios = $stmtHorarios->fetchAll(PDO::FETCH_ASSOC);
    
    // ==========================================
    // 4. OBTENER TEMAS Y SUBTEMAS
    // ==========================================
    
    $temasQuery = "
        SELECT 
            t.id,
            t.descripcion,
            t.tema_padre_id,
            t.esta_eliminado
        FROM temas t
        WHERE t.capacitacion_id = :capacitacion_id 
        AND t.esta_eliminado = 0
        ORDER BY t.tema_padre_id, t.id
    ";
    
    $stmtTemas = $pdoCourses->prepare($temasQuery);
    $stmtTemas->bindParam(':capacitacion_id', $capacitacionId, PDO::PARAM_INT);
    $stmtTemas->execute();
    $temas = $stmtTemas->fetchAll(PDO::FETCH_ASSOC);
    
    // Organizar temas y subtemas
    $temasOrganizados = organizarTemas($temas);
    
    // ==========================================
    // 5. PREPARAR RESPUESTA COMPLETA
    // ==========================================
    
    // Procesar imagen - usar directamente la ruta de la BD
    $imagenUrl = !empty($capacitacion['ruta_imagen']) 
        ? $capacitacion['ruta_imagen']
        : '/sistemaInstitucional/images/default-course.webp';
    
    // Obtener link de inscripción desde la base de datos
    $linkInscripcion = $capacitacion['link'] ?? '';
    
    // Mapear estado
    $estadoMapeado = mapearEstado($capacitacion['estado_nombre']);
    
    // Respuesta completa
    $respuesta = [
        'success' => true,
        'capacitacion' => [
            // === DATOS BÁSICOS ===
            'id' => (int)$capacitacion['id'],
            'nombre' => $capacitacion['nombre'],
            'slogan' => $capacitacion['slogan'],
            'objetivo' => $capacitacion['objetivo'],
            'que_aprenderas' => $capacitacion['que_aprenderas'],
            'destinatarios' => $capacitacion['destinatarios'],
            'requisitos' => $capacitacion['requisitos'],
            
            // === CATEGORIZACIÓN ===
            'alcance' => $capacitacion['alcance'], // curso/taller
            'tipo_capacitacion' => $capacitacion['tipo_capacitacion'], // interno/estatal
            'tipo_categoria' => $capacitacion['tipo_categoria'], // general/especifica/subcategoria
            'categoria_id' => (int)$capacitacion['categoria_id'],
            'categoria_completa' => $categoriaCompleta,
            'tipo_modalidad' => $capacitacion['tipo_modalidad'] ?? null, // Campo agregado
            'lugar' => $capacitacion['lugar'] ?? null, // Campo agregado
            
            // === FECHAS Y LOGÍSTICA ===
            'fecha_inicio_inscripcion' => $capacitacion['fecha_inicio_inscripcion'],
            'fecha_inicio_cursada' => $capacitacion['fecha_inicio_cursada'],
            'fecha_fin_cursada' => $capacitacion['fecha_fin_cursada'],
            'duracion_clase_minutos' => (int)$capacitacion['duracion_clase_minutos'],
            'total_encuentros' => (int)$capacitacion['total_encuentros'],
            'cupos_maximos' => (int)$capacitacion['cupos_maximos'],
            
            // === DATOS COMPLEJOS ===
            'horarios' => $horarios,
            'temas' => $temasOrganizados,
           "EquipoLogued"=>$usuarioActual["id_equipo"],
            // === GESTIÓN ===
            'equipo_id' => (int)$capacitacion['equipo_id'],
            'equipo_nombre' => $nombreEquipo,
            'estado_id' => (int)$capacitacion['estado_id'],
            'estado_nombre' => $estadoMapeado,
            'imagen_url' => $imagenUrl,
            'link_inscripcion' => $linkInscripcion,
            'es_destacado' => (bool)$capacitacion['es_destacado'],
            'esta_publicada' => (bool)$capacitacion['esta_publicada'],
            'esta_eliminada' => (bool)$capacitacion['esta_eliminada']
        ]
    ];
    
    echo json_encode($respuesta, JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    error_log("Error en obtener_capacitacion.php: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Error interno del servidor',
        'debug' => $e->getMessage() // Solo para desarrollo, quitar en producción
    ]);
}

/**
 * Obtiene la categorización completa reconstruyendo la jerarquía
 */
function obtenerCategoriaCompleta($capacitacion, $pdoCourses) {
    $tipoCat = $capacitacion['tipo_categoria'];
    $categoriaId = $capacitacion['categoria_id'];
    
    try {
        switch ($tipoCat) {
            case 'general':
                $query = "SELECT id, nombre FROM categorias_generales WHERE id = :id";
                $stmt = $pdoCourses->prepare($query);
                $stmt->bindParam(':id', $categoriaId, PDO::PARAM_INT);
                $stmt->execute();
                $categoria = $stmt->fetch(PDO::FETCH_ASSOC);
                
                return [
                    'nivel' => 'general',
                    'general' => $categoria,
                    'especifica' => null,
                    'subcategoria' => null,
                    'path_completo' => $categoria['nombre'] ?? 'Sin categoría'
                ];
                
            case 'especifica':
                $query = "
                    SELECT 
                        ce.id as especifica_id, ce.nombre as especifica_nombre,
                        cg.id as general_id, cg.nombre as general_nombre
                    FROM categorias_especificas ce
                    INNER JOIN categorias_generales cg ON ce.categoria_general_id = cg.id
                    WHERE ce.id = :id
                ";
                $stmt = $pdoCourses->prepare($query);
                $stmt->bindParam(':id', $categoriaId, PDO::PARAM_INT);
                $stmt->execute();
                $categoria = $stmt->fetch(PDO::FETCH_ASSOC);
                
                return [
                    'nivel' => 'especifica',
                    'general' => [
                        'id' => $categoria['general_id'],
                        'nombre' => $categoria['general_nombre']
                    ],
                    'especifica' => [
                        'id' => $categoria['especifica_id'],
                        'nombre' => $categoria['especifica_nombre']
                    ],
                    'subcategoria' => null,
                    'path_completo' => ($categoria['general_nombre'] ?? '') . ' > ' . ($categoria['especifica_nombre'] ?? '')
                ];
                
            case 'subcategoria':
                $query = "
                    SELECT 
                        s.id as sub_id, s.nombre as sub_nombre,
                        ce.id as especifica_id, ce.nombre as especifica_nombre,
                        cg.id as general_id, cg.nombre as general_nombre
                    FROM subcategorias s
                    INNER JOIN categorias_especificas ce ON s.categoria_especifica_id = ce.id
                    INNER JOIN categorias_generales cg ON ce.categoria_general_id = cg.id
                    WHERE s.id = :id
                ";
                $stmt = $pdoCourses->prepare($query);
                $stmt->bindParam(':id', $categoriaId, PDO::PARAM_INT);
                $stmt->execute();
                $categoria = $stmt->fetch(PDO::FETCH_ASSOC);
                
                return [
                    'nivel' => 'subcategoria',
                    'general' => [
                        'id' => $categoria['general_id'],
                        'nombre' => $categoria['general_nombre']
                    ],
                    'especifica' => [
                        'id' => $categoria['especifica_id'],
                        'nombre' => $categoria['especifica_nombre']
                    ],
                    'subcategoria' => [
                        'id' => $categoria['sub_id'],
                        'nombre' => $categoria['sub_nombre']
                    ],
                    'path_completo' => ($categoria['general_nombre'] ?? '') . ' > ' . ($categoria['especifica_nombre'] ?? '') . ' > ' . ($categoria['sub_nombre'] ?? '')
                ];
                
            default:
                return [
                    'nivel' => 'desconocido',
                    'general' => null,
                    'especifica' => null,
                    'subcategoria' => null,
                    'path_completo' => 'Categoría no definida'
                ];
        }
    } catch (Exception $e) {
        error_log("Error al obtener categoría completa: " . $e->getMessage());
        return [
            'nivel' => 'error',
            'general' => null,
            'especifica' => null,
            'subcategoria' => null,
            'path_completo' => 'Error al cargar categoría'
        ];
    }
}

/**
 * Organiza temas principales y subtemas en estructura jerárquica
 */
function organizarTemas($temas) {
    $temasOrganizados = [];
    $temasMap = [];
    
    // Separar temas principales y subtemas
    $temasPrincipales = [];
    $subtemas = [];
    
    foreach ($temas as $tema) {
        if ($tema['tema_padre_id'] == 0) {
            $temasPrincipales[] = $tema;
        } else {
            $subtemas[] = $tema;
        }
    }
    
    // Organizar temas principales con sus subtemas
    foreach ($temasPrincipales as $temaPrincipal) {
        $temaOrganizado = [
            'id' => (int)$temaPrincipal['id'],
            'nombre' => $temaPrincipal['descripcion'],
            'subtemas' => []
        ];
        
        // Buscar subtemas de este tema
        foreach ($subtemas as $subtema) {
            if ($subtema['tema_padre_id'] == $temaPrincipal['id']) {
                $temaOrganizado['subtemas'][] = [
                    'id' => (int)$subtema['id'],
                    'nombre' => $subtema['descripcion']
                ];
            }
        }
        
        $temasOrganizados[] = $temaOrganizado;
    }
    
    return $temasOrganizados;
}

/**
 * Genera link de inscripción automático
 */
function generarLinkInscripcion($capacitacionId) {
    // Por ahora generamos un link genérico, después se puede personalizar
    return "https://inscripciones.instituto.edu.ar/capacitacion/{$capacitacionId}";
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
