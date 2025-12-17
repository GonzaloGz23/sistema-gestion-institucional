<?php
include '../../../../backend/config/database.php';

header('Content-Type: application/json');
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['id_nota'], $input['titulo'])) {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
    exit;
}

$id_nota = intval($input['id_nota']);
$titulo = trim($input['titulo']);

if ($titulo === '') {
    $titulo = 'sin titulo';
}

try {
    $stmt = $pdo->prepare("UPDATE notas SET titulo = ? WHERE id_notas = ?");
    $success = $stmt->execute([$titulo, $id_nota]);

    if ($success) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No se pudo actualizar el título']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
