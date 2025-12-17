<?php
// Incluir la conexión a la base de datos (tu archivo de conexión)
require_once "../../../config/database.php";

if (isset($_POST['nombre'])) {
    $nombre = $_POST['nombre'];
    try {
        $sql = "INSERT INTO etiquetas (descripcion_etiqueta) VALUES (?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nombre]);
        $id = $pdo->lastInsertId();
        echo json_encode(['success' => true, 'id' => $id]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Nombre de etiqueta no proporcionado.']);
}
?>