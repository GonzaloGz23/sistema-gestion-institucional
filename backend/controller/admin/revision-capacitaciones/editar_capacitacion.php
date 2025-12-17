<?php

/**
 * ==========================================
 * CONTROLADOR: EDITAR CAPACITACIÓN
 * ==========================================
 * 
 * Actualiza una capacitación completa con enfoque FULL SYNC
 * - Actualiza datos principales de la capacitación
 * - Reemplaza completamente horarios (DELETE + INSERT)
 * - Reemplaza completamente temas (DELETE + INSERT)
 * - Manejo transaccional para consistencia de datos
 * 
 * MAPEO CRÍTICO - ALCANCE Y TIPO CAPACITACIÓN:
 * - Campo 'alcance': interno | estatal (desde frontend #alcance)
 * - Campo 'tipo_capacitacion': curso | taller (desde frontend #tipoCapacitacion)
 * 
 * Método: POST
 * Autenticación: Requerida
 * Permisos: Usuario debe pertenecer a entidad con acceso a la capacitación
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

try {
    // ==========================================
    // CONFIGURACIÓN E INCLUDES
    // ==========================================

    require_once '../../../config/database.php';
    require_once '../../../config/database_courses.php';
    require_once '../../../config/session_config.php';

    // ==========================================
    // VALIDACIONES DE AUTENTICACIÓN Y MÉTODO
    // ==========================================

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
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode([
            'success' => false,
            'error' => 'Método no permitido. Use POST'
        ]);
        exit;
    }

    // Obtener usuario actual
    $usuarioActual = obtenerUsuarioActual();
    $idEntidad = $usuarioActual['id_entidad'];
    $idEquipo = $usuarioActual['id_equipo'];
    // ==========================================
    // OBTENER Y VALIDAR DATOS DE ENTRADA
    // ==========================================

    // Leer datos del cuerpo de la petición
    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Datos JSON inválidos o vacíos'
        ]);
        exit;
    }

    // Validar ID de capacitación
    $capacitacionId = filter_var($input['id'] ?? 0, FILTER_VALIDATE_INT);

    if (!$capacitacionId || $capacitacionId <= 0) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'ID de capacitación inválido'
        ]);
        exit;
    }

    // ==========================================
    // VERIFICAR PERMISOS DE ACCESO
    // ==========================================

    // Primero verificar que la capacitación existe en sistema_cursos
    $verificarCapacitacionQuery = "SELECT id, equipo_id FROM capacitaciones WHERE id = :capacitacion_id";
    $stmtCapacitacion = $pdoCourses->prepare($verificarCapacitacionQuery);
    $stmtCapacitacion->execute(['capacitacion_id' => $capacitacionId]);
    $capacitacion = $stmtCapacitacion->fetch(PDO::FETCH_ASSOC);

    if (!$capacitacion) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'error' => 'Capacitación no encontrada'
        ]);
        exit;
    }

    // Luego verificar permisos usando la conexión de sistema_institucional
    $verificarPermisosQuery = "
        SELECT e.id_equipo, e.alias
        FROM equipos e
        INNER JOIN areas a ON e.id_area = a.id_area
        WHERE e.id_equipo = :equipo_id 
        AND a.id_entidad = :id_entidad
        AND e.estado = 'habilitado'
        AND e.borrado = 0
    ";

    $stmtPermisos = $pdo->prepare($verificarPermisosQuery);
    $stmtPermisos->execute([
        'equipo_id' => $capacitacion['equipo_id'],
        'id_entidad' => $idEntidad
    ]);

    $equipoValido = $stmtPermisos->fetch(PDO::FETCH_ASSOC);

    if (!$equipoValido) {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'error' => 'No tiene permisos para editar esta capacitación'
        ]);
        exit;
    }

    // ==========================================
    // VALIDAR DATOS DE ENTRADA
    // ==========================================
    if ($idEquipo == 10 || $idEquipo == 11) {
        # code...

        $erroresValidacion = validarDatosEntrada($input);

        if (!empty($erroresValidacion)) {
            //http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'Datos de entrada inválidos',
                'detalles' => $erroresValidacion
            ]);
            exit;
        }
    }

    // ==========================================
    // PROCESAMIENTO TRANSACCIONAL
    // ==========================================

    // Iniciar transacción
    $pdoCourses->beginTransaction();

    try {

        // 1. ACTUALIZAR DATOS PRINCIPALES DE LA CAPACITACIÓN
        actualizarCapacitacionPrincipal($pdoCourses, $capacitacionId, $input);

        // 2. ACTUALIZAR HORARIOS (DELETE + INSERT)
        actualizarHorarios($pdoCourses, $capacitacionId, $input['horarios'] ?? []);

        // 3. ACTUALIZAR TEMAS (DELETE + INSERT) 
        actualizarTemas($pdoCourses, $capacitacionId, $input['temas'] ?? []);

        // 4. REGISTRAR LOG DE CAMBIO (OPCIONAL)
        registrarLogCambio($pdoCourses, $capacitacionId, $usuarioActual['id'], 'Capacitación editada desde módulo de revisión');

        // Confirmar transacción
        $pdoCourses->commit();

        // ==========================================
        // RESPUESTA EXITOSA
        // ==========================================

        // Obtener datos actualizados para respuesta
        $capacitacionActualizada = obtenerCapacitacionActualizada($pdoCourses, $capacitacionId);

        echo json_encode([
            'success' => true,
            'message' => 'Capacitación actualizada exitosamente',
            'capacitacion' => $capacitacionActualizada,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    } catch (Exception $e) {
        // Rollback en caso de error
        $pdoCourses->rollback();
        throw $e;
    }
} catch (Exception $e) {
    // Manejo de errores generales
    error_log('Error en editar_capacitacion.php: ' . $e->getMessage());

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Error interno del servidor',
        'detalles' => $e->getMessage() // TODO: Remover en producción
    ]);
}

// ==========================================
// FUNCIONES AUXILIARES
// ==========================================

/**
 * Valida los datos de entrada del formulario
 * @param array $datos Datos del formulario
 * @return array Array de errores (vacío si no hay errores)
 */
function validarDatosEntrada($datos)
{ 
     $usuarioActual = obtenerUsuarioActual();
    $idEquipo = $usuarioActual['id_equipo'];
    if ($idEquipo == 10 || $idEquipo == 11) {
        # code...

        $errores = [];

        // VALIDACIONES DE CAMPOS OBLIGATORIOS
        $camposObligatorios = [
            'nombre' => 'Nombre de la capacitación',
            'slogan' => 'Slogan',
            'objetivo' => 'Objetivo',
            'que_aprenderas' => 'Qué aprenderás',
            'destinatarios' => 'Destinatarios',
            'requisitos' => 'Requisitos',
            'alcance' => 'Alcance',
            'modalidad' => 'Modalidad',
            'fecha_inicio_inscripcion' => 'Fecha inicio inscripción',
            'fecha_inicio_cursada' => 'Fecha inicio cursada',
            'fecha_fin_cursada' => 'Fecha fin cursada',
            'duracion_clase_minutos' => 'Duración clase (minutos)',
            'total_encuentros' => 'Total encuentros',
            'cupos_maximos' => 'Cupos máximos'
        ];

        foreach ($camposObligatorios as $campo => $nombre) {
            if (empty($datos[$campo])) {
                $errores[] = "El campo '$nombre' es obligatorio";
            }
        }

        // VALIDACIÓN CONDICIONAL: LUGAR
        if (isset($datos['modalidad']) && in_array($datos['modalidad'], ['presencial', 'mixto'])) {
            if (empty($datos['lugar'])) {
                $errores[] = "El campo 'Lugar' es obligatorio para modalidad presencial o mixta";
            }
        }

        // VALIDACIONES DE FORMATO DE FECHAS
        if (!empty($datos['fecha_inicio_inscripcion']) && !validarFecha($datos['fecha_inicio_inscripcion'])) {
            $errores[] = "Formato de fecha inicio inscripción inválido";
        }
        if (!empty($datos['fecha_inicio_cursada']) && !validarFecha($datos['fecha_inicio_cursada'])) {
            $errores[] = "Formato de fecha inicio cursada inválido";
        }
        if (!empty($datos['fecha_fin_cursada']) && !validarFecha($datos['fecha_fin_cursada'])) {
            $errores[] = "Formato de fecha fin cursada inválido";
        }

        // VALIDACIONES DE COHERENCIA DE FECHAS
        if (!empty($datos['fecha_inicio_inscripcion']) && !empty($datos['fecha_inicio_cursada'])) {
            if (strtotime($datos['fecha_inicio_inscripcion']) >= strtotime($datos['fecha_inicio_cursada'])) {
                $errores[] = "La fecha de inicio de inscripción debe ser anterior a la fecha de inicio de cursada";
            }
        }
        if (!empty($datos['fecha_inicio_cursada']) && !empty($datos['fecha_fin_cursada'])) {
            if (strtotime($datos['fecha_inicio_cursada']) > strtotime($datos['fecha_fin_cursada'])) {
                $errores[] = "La fecha de inicio de cursada debe ser anterior o igual a la fecha fin de cursada";
            }
        }

        // VALIDACIONES NUMÉRICAS
        if (isset($datos['duracion_clase_minutos']) && (!is_numeric($datos['duracion_clase_minutos']) || $datos['duracion_clase_minutos'] <= 0)) {
            $errores[] = "La duración de clase debe ser un número positivo";
        }
        if (isset($datos['total_encuentros']) && (!is_numeric($datos['total_encuentros']) || $datos['total_encuentros'] <= 0)) {
            $errores[] = "El total de encuentros debe ser un número positivo";
        }
        if (isset($datos['cupos_maximos']) && (!is_numeric($datos['cupos_maximos']) || $datos['cupos_maximos'] <= 0)) {
            $errores[] = "Los cupos máximos deben ser un número positivo";
        }

        // VALIDACIÓN DE COHERENCIA HORARIOS VS ENCUENTROS
        if (isset($datos['horarios']) && isset($datos['total_encuentros'])) {
            $cantidadHorarios = is_array($datos['horarios']) ? count($datos['horarios']) : 0;
            if ($cantidadHorarios != intval($datos['total_encuentros'])) {
                $errores[] = "La cantidad de horarios configurados ($cantidadHorarios) debe coincidir con el total de encuentros ({$datos['total_encuentros']})";
            }
        }

        // VALIDACIÓN DE ESTRUCTURA DE HORARIOS
        if (isset($datos['horarios']) && is_array($datos['horarios'])) {
            foreach ($datos['horarios'] as $index => $horario) {
                if (empty($horario['dia'])) {
                    $errores[] = "El día es obligatorio en el horario " . ($index + 1);
                }
                if (empty($horario['hora_inicio']) || !validarHora($horario['hora_inicio'])) {
                    $errores[] = "La hora de inicio es obligatoria y debe tener formato válido en el horario " . ($index + 1);
                }
                if (empty($horario['hora_fin']) || !validarHora($horario['hora_fin'])) {
                    $errores[] = "La hora de fin es obligatoria y debe tener formato válido en el horario " . ($index + 1);
                }
            }
        }

        // VALIDACIÓN DE CONTENIDO (al menos un tema)
        if (isset($datos['temas'])) {
            if (!is_array($datos['temas']) || count($datos['temas']) === 0) {
                $errores[] = "Debe agregar al menos un tema";
            } else {
                foreach ($datos['temas'] as $index => $tema) {
                    $descripcionTema = is_array($tema) ? ($tema['descripcion'] ?? $tema['nombre'] ?? '') : $tema;
                    if (empty($descripcionTema)) {
                        $errores[] = "El tema " . ($index + 1) . " no puede estar vacío";
                    }
                }
            }
        }

        return $errores;
    }
}

/**
 * Valida formato de fecha YYYY-MM-DD
 * @param string $fecha Fecha a validar
 * @return bool True si es válida
 */
function validarFecha($fecha)
{
    $d = DateTime::createFromFormat('Y-m-d', $fecha);
    return $d && $d->format('Y-m-d') === $fecha;
}

/**
 * Valida formato de hora HH:MM o HH:MM:SS
 * @param string $hora Hora a validar
 * @return bool True si es válida
 */
function validarHora($hora)
{
    return preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9](:[0-5][0-9])?$/', $hora);
}

/**
 * Actualiza los datos principales de la capacitación
 * @param PDO $pdo Conexión a BD de cursos
 * @param int $id ID de la capacitación
 * @param array $datos Datos del formulario
 */

function actualizarCapacitacionPrincipal($pdo, $id, $datos)
{
    $usuarioActual = obtenerUsuarioActual();
    $estado_revision = "";
    if ($usuarioActual['id_equipo'] == 10 || $usuarioActual['id_equipo'] == 11) {
        $estado_revision = ',estado_id = 3';
    }
    $query = "
        UPDATE capacitaciones SET
            nombre = :nombre,
            slogan = :slogan,
            objetivo = :objetivo,
            que_aprenderas = :que_aprenderas,
            destinatarios = :destinatarios,
            requisitos = :requisitos,
            alcance = :alcance,
            tipo_capacitacion = :tipo_capacitacion,
            tipo_categoria = :tipo_categoria,
            categoria_id = :categoria_id,
            tipo_modalidad = :tipo_modalidad,
            lugar = :lugar,
            fecha_inicio_inscripcion = :fecha_inicio_inscripcion,
            fecha_inicio_cursada = :fecha_inicio_cursada,
            fecha_fin_cursada = :fecha_fin_cursada,
            duracion_clase_minutos = :duracion_clase_minutos,
            total_encuentros = :total_encuentros,
            cupos_maximos = :cupos_maximos
            $estado_revision
        WHERE id = :id
    ";

    $stmt = $pdo->prepare($query);

    // Mapear parámetros desde $datos del frontend
    $stmt->execute([
        'id' => $id,
        'nombre' => $datos['nombre'] ?? '',
        'slogan' => $datos['slogan'] ?? '',
        'objetivo' => $datos['objetivo'] ?? '',
        'que_aprenderas' => $datos['que_aprenderas'] ?? '',
        'destinatarios' => $datos['destinatarios'] ?? '',
        'requisitos' => $datos['requisitos'] ?? '',
        'alcance' => $datos['alcance'] ?? 'interno', // interno/estatal
        'tipo_capacitacion' => $datos['tipo_capacitacion'] ?? 'curso', // curso/taller
        'tipo_categoria' => $datos['tipo_categoria'] ?? 'general',
        'categoria_id' => $datos['categoria_id'] ?? 1,
        'tipo_modalidad' => $datos['modalidad'] ?? 'virtual', // Corregido: usar 'modalidad'
        'lugar' => ($datos['modalidad'] === 'virtual') ? null : ($datos['lugar'] ?? null),
        'fecha_inicio_inscripcion' => $datos['fecha_inicio_inscripcion'] ?? null,
        'fecha_inicio_cursada' => $datos['fecha_inicio_cursada'] ?? null,
        'fecha_fin_cursada' => $datos['fecha_fin_cursada'] ?? null,
        'duracion_clase_minutos' => intval($datos['duracion_clase_minutos'] ?? 60),
        'total_encuentros' => intval($datos['total_encuentros'] ?? 1),
        'cupos_maximos' => intval($datos['cupos_maximos'] ?? 10)
    ]);
}

/**
 * Actualiza horarios de la capacitación (FULL SYNC con DELETE real)
 * @param PDO $pdo Conexión a BD de cursos
 * @param int $capacitacionId ID de la capacitación
 * @param array $horarios Nuevos horarios
 */
function actualizarHorarios($pdo, $capacitacionId, $horarios)
{
    // 1. DELETE REAL: Eliminar todos los horarios existentes de esta capacitación
    $deleteQuery = "DELETE FROM cronogramas WHERE capacitacion_id = :capacitacion_id";
    $stmtDelete = $pdo->prepare($deleteQuery);
    $stmtDelete->execute(['capacitacion_id' => $capacitacionId]);

    // 2. INSERT nuevos horarios solamente
    if (!empty($horarios)) {
        $insertQuery = "
            INSERT INTO cronogramas (capacitacion_id, dia_id, hora_inicio, hora_fin)
            VALUES (:capacitacion_id, :dia_id, :hora_inicio, :hora_fin)
        ";
        $stmtInsert = $pdo->prepare($insertQuery);

        foreach ($horarios as $horario) {
            // Mapear nombre de día a ID
            $diaId = mapearDiaAId($horario['dia'] ?? '');

            if ($diaId) {
                $stmtInsert->execute([
                    'capacitacion_id' => $capacitacionId,
                    'dia_id' => $diaId,
                    'hora_inicio' => $horario['hora_inicio'] ?? '00:00:00',
                    'hora_fin' => $horario['hora_fin'] ?? '00:00:00'
                ]);
            }
        }
    }
}

/**
 * Mapea nombre de día a ID de la tabla dias
 * @param string $nombreDia Nombre del día
 * @return int|null ID del día o null si no se encuentra
 */
function mapearDiaAId($nombreDia)
{
    $mapeo = [
        'lunes' => 1,
        'martes' => 2,
        'miércoles' => 3,
        'miercoles' => 3, // Sin acento
        'jueves' => 4,
        'viernes' => 5,
        'sábado' => 6,
        'sabado' => 6, // Sin acento
        'domingo' => 7
    ];

    return $mapeo[strtolower($nombreDia)] ?? null;
}

/**
 * Actualiza temas de la capacitación (FULL SYNC con DELETE real)
 * @param PDO $pdo Conexión a BD de cursos
 * @param int $capacitacionId ID de la capacitación
 * @param array $temas Nuevos temas aplanados con estructura simple
 */
function actualizarTemas($pdo, $capacitacionId, $temas)
{
    // 1. DELETE REAL: Eliminar todos los temas existentes de esta capacitación
    $deleteQuery = "DELETE FROM temas WHERE capacitacion_id = :capacitacion_id";
    $stmtDelete = $pdo->prepare($deleteQuery);
    $stmtDelete->execute(['capacitacion_id' => $capacitacionId]);

    // 2. INSERT nuevos temas y subtemas solamente
    if (!empty($temas)) {
        $insertTemaQuery = "
            INSERT INTO temas (capacitacion_id, descripcion, tema_padre_id)
            VALUES (:capacitacion_id, :descripcion, :tema_padre_id)
        ";

        $stmtTema = $pdo->prepare($insertTemaQuery);
        $mapaTemaPadre = []; // Para mapear nombres de temas padre a sus IDs

        foreach ($temas as $tema) {
            $descripcion = is_array($tema) ? ($tema['descripcion'] ?? '') : $tema;

            if (empty($descripcion)) continue;

            // Si es un tema principal (no es subtema)
            if (!isset($tema['es_subtema']) || !$tema['es_subtema']) {
                // Insertar tema principal (tema_padre_id = NULL)
                $stmtTema->execute([
                    'capacitacion_id' => $capacitacionId,
                    'descripcion' => $descripcion,
                    'tema_padre_id' => null
                ]);

                // Guardar el ID del tema recién insertado
                $temaId = $pdo->lastInsertId();
                $mapaTemaPadre[$descripcion] = $temaId;
            } else {
                // Es un subtema, buscar el ID del tema padre
                $nombreTemaPadre = $tema['tema_padre_nombre'] ?? '';
                $temaPadreId = $mapaTemaPadre[$nombreTemaPadre] ?? null;

                if ($temaPadreId) {
                    $stmtTema->execute([
                        'capacitacion_id' => $capacitacionId,
                        'descripcion' => $descripcion,
                        'tema_padre_id' => $temaPadreId
                    ]);
                }
            }
        }
    }
}

/**
 * Registra un log de cambio en la capacitación (opcional)
 * @param PDO $pdo Conexión a BD
 * @param int $capacitacionId ID de la capacitación
 * @param int $usuarioId ID del usuario que hizo el cambio
 * @param string $descripcion Descripción del cambio
 */
function registrarLogCambio($pdo, $capacitacionId, $usuarioId, $descripcion)
{
    // Por ahora solo registramos en error_log para debug
    // TODO: Implementar tabla de logs si se requiere en el futuro
    error_log("LOG CAMBIO CAPACITACIÓN - ID: $capacitacionId, Usuario: $usuarioId, Acción: $descripcion, Fecha: " . date('Y-m-d H:i:s'));
}

/**
 * Obtiene los datos actualizados de la capacitación para la respuesta
 * @param PDO $pdo Conexión a BD de cursos
 * @param int $capacitacionId ID de la capacitación
 * @return array Datos actualizados de la capacitación
 */
function obtenerCapacitacionActualizada($pdo, $capacitacionId)
{
    $query = "
        SELECT 
            c.*,
            ec.nombre as estado_nombre,
            cg.nombre as categoria_general_nombre,
            ce.nombre as categoria_especifica_nombre,
            s.nombre as subcategoria_nombre
        FROM capacitaciones c
        LEFT JOIN estados_capacitacion ec ON c.estado_id = ec.id
        LEFT JOIN categorias_generales cg ON (c.tipo_categoria = 'general' AND c.categoria_id = cg.id)
        LEFT JOIN categorias_especificas ce ON (c.tipo_categoria = 'especifica' AND c.categoria_id = ce.id)
        LEFT JOIN subcategorias s ON (c.tipo_categoria = 'subcategoria' AND c.categoria_id = s.id)
        WHERE c.id = :id
    ";

    $stmt = $pdo->prepare($query);
    $stmt->execute(['id' => $capacitacionId]);
    $capacitacion = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$capacitacion) {
        return [];
    }

    // Obtener horarios actualizados
    $horariosQuery = "
        SELECT 
            c.*,
            d.nombre as dia_nombre,
            d.nombre_corto as dia_corto
        FROM cronogramas c
        INNER JOIN dias d ON c.dia_id = d.id
        WHERE c.capacitacion_id = :id
        ORDER BY c.dia_id, c.hora_inicio
    ";

    $stmtHorarios = $pdo->prepare($horariosQuery);
    $stmtHorarios->execute(['id' => $capacitacionId]);
    $capacitacion['horarios'] = $stmtHorarios->fetchAll(PDO::FETCH_ASSOC);

    // Obtener temas y subtemas actualizados
    $temasQuery = "
        SELECT 
            id,
            descripcion,
            tema_padre_id
        FROM temas
        WHERE capacitacion_id = :id
        ORDER BY tema_padre_id IS NULL DESC, id
    ";

    $stmtTemas = $pdo->prepare($temasQuery);
    $stmtTemas->execute(['id' => $capacitacionId]);
    $todasLasTemas = $stmtTemas->fetchAll(PDO::FETCH_ASSOC);

    // Organizar temas jerárquicamente
    $temas = [];
    $subtemas = [];

    // Separar temas principales y subtemas
    foreach ($todasLasTemas as $tema) {
        if ($tema['tema_padre_id'] === null) {
            $temas[$tema['id']] = [
                'id' => $tema['id'],
                'descripcion' => $tema['descripcion'],
                'subtemas' => []
            ];
        } else {
            $subtemas[] = $tema;
        }
    }

    // Asignar subtemas a sus temas padre
    foreach ($subtemas as $subtema) {
        if (isset($temas[$subtema['tema_padre_id']])) {
            $temas[$subtema['tema_padre_id']]['subtemas'][] = [
                'id' => $subtema['id'],
                'descripcion' => $subtema['descripcion']
            ];
        }
    }

    $capacitacion['temas'] = array_values($temas);

    return $capacitacion;
}
