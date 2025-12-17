<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

try {
    require_once '../../../config/database.php';
    require_once '../../../config/database_courses.php';
    require_once '../../../config/session_config.php';

    if (!verificarUsuarioAutenticado()) {
        echo json_encode([ "success" => false, "message" => "No autenticado" ]);
        exit;
    }

    if (!isset($_POST['id_asignacion'])) {
        echo json_encode([ "success" => false, "message" => "Falta ID del profesor" ]);
        exit;
    }

    $id_asignacion = intval($_POST['id_asignacion']);

    // Aquí se elimina la asignación del profesor a la capacitación
    $query = " UPDATE `asignacion_profesores` SET `esta_activo`='0' WHERE `id`=:id_asignacion";
    $stmt = $pdoCourses->prepare($query);
    $stmt->bindParam(":id_asignacion", $id_asignacion, PDO::PARAM_INT);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        echo json_encode([
            "success" => true,
            "message" => "Profesor eliminado correctamente"
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "message" => "No se encontró el registro"
        ]);
    }

} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => "Error del servidor",
        "error" => $e->getMessage()
    ]);
}
