<?php
require_once "../../../config/database.php";

$data = json_decode(file_get_contents("php://input"), true);
$id = $data['id'] ?? null;

if ($id) {
    $query = "DELETE FROM notas WHERE id_notas = :id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);

    if ($stmt->execute()) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "error" => "Error al eliminar la nota"]);
    }
} else {
    echo json_encode(["success" => false, "error" => "ID no válido"]);
}
?>