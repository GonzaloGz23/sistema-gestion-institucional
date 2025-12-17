<?php
require_once "../../../config/database.php";

header('Content-Type: application/json');

$input = json_decode(file_get_contents("php://input"), true);
$id_usuario = $input['id_usuario'] ?? null;
$id_nota = $input['id_nota'] ?? null;

if (!$id_usuario || !$id_nota) {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
    exit;
}

try {
    $stmt = $pdo->prepare("INSERT INTO collaboradores (id_usuario, id_nota) VALUES (?, ?)");
    $stmt->execute([$id_usuario, $id_nota]);
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>