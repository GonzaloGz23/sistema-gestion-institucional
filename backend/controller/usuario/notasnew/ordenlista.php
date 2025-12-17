 <?php
 require_once "../../../config/session_config.php";
 require_once "../../../config/database.php";

$data = json_decode(file_get_contents('php://input'), true);
 if (!verificarUsuarioAutenticado()) {
    http_response_code(401);
    echo json_encode(['error' => 'Usuario no autenticado']);
    exit;
}


if(!isset($data['tareas']) || !is_array($data['tareas'])){
    echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
    exit;
}

try {
    

    // Preparar consulta para actualizar el orden
    $stmt = $pdo->prepare("UPDATE nota_lista SET orden = :orden WHERE id_nota_lista = :id");
    
    // Ejecutar por cada tarea
    
    foreach ($data['tareas'] as $tarea) {
        if (!isset($tarea['id']) || !isset($tarea['orden'])) continue;

        $stmt->execute([
            ':orden' => $tarea['orden'],
            ':id'    => $tarea['id'],
        ]);
    }

    echo json_encode(['success' => true]);

} catch (PDOException $e) {
    error_log("Error en BD: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error en la base de datos']);
}
?>