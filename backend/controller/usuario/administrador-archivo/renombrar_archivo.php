<?php
require_once "../../../config/database.php";
date_default_timezone_set('America/Argentina/Buenos_Aires');

// Recibir datos del formulario
$data = json_decode(file_get_contents('php://input'), true);
$id = $data['id'];
$nombre = $data['nombre'];
$tipo = $data['tipo'];


if($tipo == 'archivo') {
// Actualizar la base de datos
$stmt = $pdo->prepare("UPDATE archivo_carpeta SET nombre_archivo = ? WHERE id_archivo = ?");
}

if($tipo == 'carpeta') {
    // Actualizar la base de datos
$stmt = $pdo->prepare("UPDATE carpeta SET nombre_carpeta = ? WHERE id_carpeta = ?");
}

try {
    $stmt->execute([$nombre, $id]);
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => "❌ Error al actualizar: " . $e->getMessage()]);
}
?>