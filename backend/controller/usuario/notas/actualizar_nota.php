<?php
require_once "../../../config/database.php";


header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);

$id     = $data['id']     ?? null;
$campo  = $data['campo']  ?? null;
$valor  = $data['valor']  ?? null;

// Validar que los datos existan
if (!$id || !$campo || !isset($valor)) {
    http_response_code(400);
    echo json_encode(['error' => 'Datos incompletos.']);
    exit;
}

// Validar que el campo sea permitido
$camposPermitidos = ['titulo', 'contenido'];
if (!in_array($campo, $camposPermitidos)) {
    http_response_code(400);
    echo json_encode(['error' => 'Campo no permitido.']);
    exit;
}

try {
    $query = "UPDATE notas SET $campo = :valor WHERE id_notas = :id";
    $stmt = $pdo->prepare($query);
    $stmt->execute([
        ':valor' => $valor,
        ':id'    => $id
    ]);

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error en la base de datos.']);
}