<?php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
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

    // Validar POST
    if (!isset($_POST['id'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Falta el parámetro capacitacion_id'
        ]);
        exit;
    }

    $capacitacion_id = intval($_POST['id']);

    // Consulta a BD de cursos
    $sql = "
        SELECT ic.fecha_inscripcion, 
i.nombre, 
i.apellido, 
i.sexo, 
i.dni,
i.celular,
i.email,
TIMESTAMPDIFF(YEAR,i.fecha_nacimiento,CURDATE()) AS edad,
c.nombre capacitacion
FROM `inscripciones_capacitaciones` ic
LEFT JOIN inscripciones i on i.id = ic.inscripcion_id
LEFT JOIN capacitaciones c on c.id = ic.capacitacion_id
WHERE c.id= :id

    ";

    $stmt = $pdoCourses->prepare($sql);
    $stmt->bindParam(':id', $capacitacion_id, PDO::PARAM_INT);
    $stmt->execute();
    $inscriptos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'inscriptos' => $inscriptos
    ]);
} catch (Exception $e) {

    error_log("Error en listar_inscriptos.php: " . $e->getMessage());

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Error interno del servidor',
        'debug' => $e->getMessage()
    ]);
}
