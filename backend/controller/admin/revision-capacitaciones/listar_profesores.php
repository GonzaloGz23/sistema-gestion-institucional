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

    $capacitacion_id = $_POST["capacitacion_id"];
    // Query para obtener capacitaciones con información del equipo
    // Primero obtenemos los equipos de la entidad del usuario desde BD principal
    $equiposQuery = "
        select p.*,ap.id id_asignacion,ap.esta_activo,ap.fecha_asignacion from asignacion_profesores ap
        inner join profesores p on p.id=ap.profesor_id
        where ap.capacitacion_id=:capacitacionid and ap.esta_activo=1
    ";

    $stmtProfesores = $pdoCourses->prepare($equiposQuery);
    $stmtProfesores->bindParam(':capacitacionid', $capacitacion_id, PDO::PARAM_INT);
    $stmtProfesores->execute();
    $Profesores = $stmtProfesores->fetchAll(PDO::FETCH_ASSOC);

    if (empty($Profesores)) {
        echo json_encode([
            'success' => true,
            'profesores' => [],
            'message' => 'No hay equipos disponibles para esta entidad'
        ]);
        exit;
    }else{
         echo json_encode([
            'success' => true,
            'profesores' => $Profesores
        ]);
    }

} catch (Exception $e) {
    error_log("Error en listar_profesores.php: " . $e->getMessage());

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Error interno del servidor',
        'debug' => $e->getMessage() // Solo para desarrollo, quitar en producción
    ]);
}
