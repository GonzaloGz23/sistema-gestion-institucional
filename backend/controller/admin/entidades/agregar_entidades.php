<?php
require_once '../../../config/database.php'; // Conexión a la BD

header('Content-Type: application/json');

try {
    // Validar que los datos fueron enviados correctamente
    if (!isset($_POST['nombre']) || empty(trim($_POST['nombre']))) {
        echo json_encode(["success" => false, "message" => "El nombre de la entidad es obligatorio"]);
        exit;
    }

    $nombre = trim($_POST['nombre']);
    $estado = $_POST['estado'] ?? 'habilitado';

    // Insertar en la base de datos
    $stmt = $pdo->prepare("INSERT INTO entidades (nombre, estado) VALUES (:nombre, :estado)");
    $stmt->bindParam(':nombre', $nombre);
    $stmt->bindParam(':estado', $estado);
    $stmt->execute();

    echo json_encode(["success" => true, "message" => "Entidad agregada exitosamente"]);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Error al agregar la entidad", "debug" => $e->getMessage()]);
}
