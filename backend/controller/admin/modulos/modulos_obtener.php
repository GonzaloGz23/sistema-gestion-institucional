<?php
require_once '../../../config/database.php';
header('Content-Type: application/json');

try {
    if (!isset($_GET['id'])) {
        echo json_encode(["success" => false, "message" => "Falta el parámetro ID"]);
        exit;
    }

    $id = (int) $_GET['id'];

    $stmt = $pdo->prepare("SELECT * FROM modulos WHERE id_modulo = :id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $modulo = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($modulo) {
        echo json_encode(["success" => true, "data" => $modulo]);
    } else {
        echo json_encode(["success" => false, "message" => "Módulo no encontrado"]);
    }
} catch (PDOException $e) {
    echo json_encode([
        "success" => false,
        "message" => "Error al obtener el módulo",
        "debug" => $e->getMessage()
    ]);
}
?>