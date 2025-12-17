<?php
require_once "../../../config/session_config.php";
require_once "../../../config/database.php";

header('Content-Type: application/json');

// Verificar usuario autenticado
if (!verificarUsuarioAutenticado()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Usuario no autenticado']);
    exit;
}

// Obtener datos del POST
$input = json_decode(file_get_contents('php://input'), true);
$id_nota = $input['id_nota'] ?? null;
$tarea = trim($input['tarea'] ?? '');
$check= $input['check'];

if($check == 1){
    $chequear=true;
}else{
    $chequear=false;
}
if (!$id_nota || $tarea === '') {
    echo json_encode(['success' => false, 'error' => 'Faltan datos']);
    exit;
}


try {
    // Insertar nueva tarea en la lista
    $stmt = $pdo->prepare("INSERT INTO nota_lista (rela_notas, lista, chequeado) VALUES (?, ?, ?)");
    $stmt->execute([$id_nota, $tarea, $check]);

    // Obtener el id de la tarea recién creada
    $id_lista_tarea = $pdo->lastInsertId();

    echo json_encode([
        'success' => true,
        'id_lista_tarea' => $id_lista_tarea,
        'tarea' => $tarea,
        'list_check' => $chequear
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
