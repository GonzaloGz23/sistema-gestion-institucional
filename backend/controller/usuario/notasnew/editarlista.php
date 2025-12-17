<?php
require_once "../../../config/session_config.php";
require_once "../../../config/database.php";

$data = json_decode(file_get_contents('php://input'), true);
$id = $data['id_lista_tarea'] ?? null;
$tarea = $data['tarea'] ?? null;
$check = $data['list_check'] ?? null;

if ($id) {
    // Preparamos el array de columnas y valores
    $fields = [];
    $values = [];

    if ($tarea !== null) {
        $fields[] = "lista=?";
        $values[] = $tarea;
    }

    if ($check !== null) {
        $fields[] = "chequeado=?";
        $values[] = $check;
    }

    if (!empty($fields)) {
        $values[] = $id; // para el WHERE
        $sql = "UPDATE nota_lista SET " . implode(", ", $fields) . " WHERE id_nota_lista=?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($values);
     
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'No se recibió ningún campo para actualizar']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'id faltante']);
}
?>
