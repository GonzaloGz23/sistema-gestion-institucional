<?php
header("Content-Type: application/json");

require_once '../../../config/database_courses.php';
require_once '../../../config/database.php';
require_once '../../../config/session_config.php';


$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data["id"])) {
    echo json_encode([
        "success" => false,
        "message" => "ID no enviado"
    ]);
    exit;
}

$id = $data["id"];

try {
    // Cambia el estado en vez de borrarlo
    $sql = "UPDATE `capacitaciones` SET `esta_eliminada`=1  WHERE `id`= :id";
    $stmt = $pdoCourses->prepare($sql);
    $stmt->execute([":id" => $id]);

    echo json_encode([
        "success" => true,
        "message" => "Estado actualizado"
    ]);
} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => "Error: " . $e->getMessage()
    ]);
}
