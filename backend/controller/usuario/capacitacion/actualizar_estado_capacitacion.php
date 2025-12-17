<?php
require_once "../../../config/database.php";

$idCapacitacion = $_POST['idCapacitacion'] ?? null;
$nuevoEstado = $_POST['estado'] ?? null;

if (!empty($idCapacitacion) && is_numeric($idCapacitacion) && !empty($nuevoEstado)) {
    try {
        $query = "UPDATE `capacitacion` SET `estado` = :estado WHERE `id_capacitacion` = :id";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':estado', $nuevoEstado, PDO::PARAM_STR);
        $stmt->bindParam(':id', $idCapacitacion, PDO::PARAM_INT);
        if ($stmt->execute()) {
            echo json_encode(["success" => true]);
        } else {
            echo json_encode(["success" => false, "error" => "Error al actualizar el estado."]);
        }
    } catch (PDOException $e) {
        echo json_encode(["success" => false, "error" => "Error de base de datos: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["success" => false, "error" => "Datos incompletos para actualizar el estado."]);
}
?>