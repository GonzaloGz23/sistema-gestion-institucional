<?php
require_once "../../../config/session_config.php";
require_once "../../../config/database.php";

$data = json_decode(file_get_contents('php://input'), true);

$id = $data['id_nota'] ?? null;
$titulo = $data['titulo'] ?? null;
$nota = $data['nota'] ?? null;

if ($id) {
    $actualizado = false;

    if ($titulo !== null) {
        $stmt = $pdo->prepare("UPDATE `notas` SET `titulo` = ? WHERE `id_notas` = ?");
        $stmt->execute([$titulo, $id]);
        $actualizado = true;
    }

    if ($nota !== null) {
        $stmt = $pdo->prepare("UPDATE `notas` SET `contenido` = ? WHERE `id_notas` = ?");
        $stmt->execute([$nota, $id]);
        $actualizado = true;
    }

    if ($actualizado) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'No se recibió ningún campo para actualizar']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'ID faltante']);
}
?>
